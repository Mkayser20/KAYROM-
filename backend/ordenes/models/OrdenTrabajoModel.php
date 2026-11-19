<?php
// Modelo de las Órdenes de Trabajo: qué mecánico trabajó en qué vehículo y qué repuestos usó
//
// Cómo funciona el stock con las órdenes (columnas nuevas de orden_trabajo_repuesto):
//   - cantidad           = lo que pidió el mecánico (lo que lleva el auto)
//   - cantidad_pendiente = lo que faltaba en stock y todavía NO se descontó (está esperando que llegue)
//   - precio_unitario    = el precio del repuesto EN EL MOMENTO de la orden (para que el total no cambie después)
// Cuando llega el pedido de lo que faltaba, PedidoModel::recibir() suma al stock y enseguida
// lo descuenta para esta orden (aplicarRecepcion). Así el stock no queda inflado.
class OrdenTrabajoModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConexion();
    }

    // Trae todas las órdenes de trabajo de un vehículo, con el nombre del mecánico
    public function getByVehiculo($vehiculo_id) {
        $vehiculo_id = (int)$vehiculo_id;
        $stmt = $this->db->prepare(
            "SELECT ot.*, p.nombre AS mecanico_nombre, p.apellido AS mecanico_apellido
             FROM orden_trabajo ot
             LEFT JOIN usuario u ON u.id = ot.mecanico_id
             LEFT JOIN persona p ON p.id = u.persona_id
             WHERE ot.vehiculo_id = ?
             ORDER BY ot.fecha DESC"
        );
        $stmt->bind_param('i', $vehiculo_id);
        $stmt->execute();
        $ordenes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // A cada orden le sumo la lista de repuestos que usó, su total y cuánto falta que llegue
        foreach ($ordenes as &$orden) {
            $orden['items'] = $this->getItems($orden['id']);
            $orden['total_repuestos'] = $orden['estado'] === 'Anulada' ? 0 : array_sum(array_map(
                static fn($item) => (float)$item['precio'] * (int)$item['cantidad'],
                $orden['items']
            ));
            $orden['pendientes'] = array_sum(array_column($orden['items'], 'cantidad_pendiente'));
        }
        return $ordenes;
    }

    public function getById($id) {
        $id = (int)$id;
        $stmt = $this->db->prepare("SELECT * FROM orden_trabajo WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Repuestos usados en una orden de trabajo puntual.
    // El precio es el guardado en la orden (precio de ese momento), no el precio actual del repuesto.
    public function getItems($orden_trabajo_id) {
        $orden_trabajo_id = (int)$orden_trabajo_id;
        $stmt = $this->db->prepare(
            "SELECT otr.id, otr.cantidad, otr.cantidad_pendiente, otr.precio_unitario AS precio,
                    r.nombre, r.id AS repuesto_id
             FROM orden_trabajo_repuesto otr
             JOIN repuestos r ON r.id = otr.repuesto_id
             WHERE otr.orden_trabajo_id = ?"
        );
        $stmt->bind_param('i', $orden_trabajo_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Crea una orden de trabajo con sus repuestos usados.
    // - Lo que hay en stock se descuenta (pasando por el StockService, que anota el movimiento).
    // - Lo que falta queda como "pendiente" en la orden y se junta en UN pedido automático
    //   por proveedor, ya vinculado a esta orden y a este vehículo.
    // - Todo ocurre junto: si algo falla, no se guarda nada a medias.
    public function create($vehiculo_id, $mecanico_id, $descripcion, $items) {
        $vehiculo_id = (int)$vehiculo_id;
        $mecanico_id = !empty($mecanico_id) ? (int)$mecanico_id : null;
        $descripcion = mb_substr(trim((string)$descripcion), 0, 255);

        $stock       = new StockService();
        $pedidoModel = new PedidoModel();
        $avisos      = [];  // mensajes para mostrarle al mecánico
        $guardados   = 0;   // cuántos repuestos se guardaron de verdad en la orden
        $faltantes   = [];  // lo que falta, agrupado por proveedor
        $hayPendientes = false;

        Tx::iniciar($this->db);
        try {
            // 0) Validaciones: el vehículo tiene que existir y el mecánico tiene que ser un usuario activo
            $stmt = $this->db->prepare("SELECT id FROM vehiculo WHERE id = ?");
            $stmt->bind_param('i', $vehiculo_id);
            $stmt->execute();
            if (!$stmt->get_result()->fetch_assoc()) throw new Exception('El vehículo no existe.');

            if ($mecanico_id !== null) {
                $stmt = $this->db->prepare("SELECT id FROM usuario WHERE id = ? AND activo = 1");
                $stmt->bind_param('i', $mecanico_id);
                $stmt->execute();
                if (!$stmt->get_result()->fetch_assoc()) throw new Exception('El mecánico elegido no existe o está inactivo.');
            }

            // 1) La orden
            $stmt = $this->db->prepare(
                "INSERT INTO orden_trabajo (vehiculo_id, mecanico_id, descripcion, estado)
                 VALUES (?, ?, ?, 'Abierta')"
            );
            $stmt->bind_param('iis', $vehiculo_id, $mecanico_id, $descripcion);
            $stmt->execute();
            $orden_id = $this->db->insert_id;

            // Si el mismo repuesto vino en dos filas, se suman (así queda un solo renglón)
            $porRepuesto = [];
            foreach ($items as $item) {
                $rid  = (int)($item['repuesto_id'] ?? 0);
                $cant = (int)($item['cantidad'] ?? 0);
                if ($rid <= 0 || $cant <= 0) continue;
                $porRepuesto[$rid] = ($porRepuesto[$rid] ?? 0) + $cant;
            }

            // 2) Cada repuesto pedido por el mecánico
            // FOR UPDATE: bloquea el repuesto mientras dura la orden, así el stock que leo no cambia en el medio
            $stmtRep = $this->db->prepare("SELECT nombre, stock, precio, proveedor_id FROM repuestos WHERE id = ? FOR UPDATE");
            $stmtOtr = $this->db->prepare(
                "INSERT INTO orden_trabajo_repuesto (orden_trabajo_id, repuesto_id, cantidad, cantidad_pendiente, precio_unitario)
                 VALUES (?, ?, ?, ?, ?)"
            );

            foreach ($porRepuesto as $repuesto_id => $cantidad) {
                $stmtRep->bind_param('i', $repuesto_id);
                $stmtRep->execute();
                $fila = $stmtRep->get_result()->fetch_assoc();
                if (!$fila) throw new Exception("No se encontró el repuesto ID $repuesto_id.");

                $usar   = min($cantidad, max(0, (int)$fila['stock'])); // lo que realmente hay
                $faltan = $cantidad - $usar;                           // lo que no alcanza
                $precio = (float)$fila['precio'];                      // precio de HOY, queda guardado

                // Anoto en la orden lo que pidió el mecánico, y cuánto quedó pendiente
                $stmtOtr->bind_param('iiiid', $orden_id, $repuesto_id, $cantidad, $faltan, $precio);
                $stmtOtr->execute();
                $guardados++;

                // Descuento solo lo que había (el StockService anota el movimiento)
                if ($usar > 0) {
                    $res = $stock->salida($repuesto_id, $usar, 'Orden de Trabajo', $orden_id,
                                          "Uso en Orden de Trabajo #$orden_id: $usar x {$fila['nombre']}");
                    if (!$res['ok']) throw new Exception($res['error']);
                }

                // Lo que falta se guarda para pedirlo al proveedor habitual
                if ($faltan > 0) {
                    $hayPendientes = true;
                    if (!empty($fila['proveedor_id'])) {
                        $faltantes[$fila['proveedor_id']][] = [
                            'repuesto_id' => $repuesto_id,
                            'cantidad'    => $faltan,
                            'precio'      => $fila['precio'],
                        ];
                        $avisos[] = "Faltaban $faltan de \"{$fila['nombre']}\" — se generó un pedido automático al proveedor. Cuando llegue, se descuenta solo para esta orden.";
                    } else {
                        $avisos[] = "Faltaban $faltan de \"{$fila['nombre']}\" y no tiene proveedor habitual: quedaron pendientes. Cuando haya stock, usá \"Descontar pendientes\" en la orden.";
                    }
                }
            }

            // 3) Un pedido automático por proveedor, vinculado a la orden y al vehículo
            foreach ($faltantes as $proveedor_id => $renglones) {
                $pedidoId = $pedidoModel->create([
                    'responsable_pedido' => 'Sistema (OT #' . $orden_id . ')',
                    'proveedor_id'       => $proveedor_id,
                    'vehiculo_id'        => $vehiculo_id,
                    'orden_trabajo_id'   => $orden_id,
                ], $renglones);
                if (!$pedidoId) throw new Exception($pedidoModel->error);
            }

            // 4) Si quedó algo pendiente, el vehículo pasa a "Esperando Repuestos"
            if ($hayPendientes) {
                $this->cambiarEstadoVehiculo($vehiculo_id, 'Esperando Repuestos');
            }

            if (!Tx::terminar($this->db, true)) throw new Exception('No se pudo guardar la orden.');
            return ['success' => true, 'id' => $orden_id, 'avisos' => $avisos, 'guardados' => $guardados];

        } catch (Throwable $e) {
            Tx::terminar($this->db, false);
            error_log('[KAYROM OrdenTrabajo] ' . $e->getMessage()); // queda en xampp/apache/logs/error.log
            return ['success' => false, 'error' => $e->getMessage(), 'avisos' => []];
        }
    }

    // Llegó mercadería de un pedido vinculado a esta orden: descuenta lo que estaba pendiente.
    // La llama PedidoModel::recibir() DENTRO de su transacción (el stock ya se sumó).
    // $recibidos = [repuesto_id => cantidad que llegó]
    public function aplicarRecepcion($orden_id, $recibidos, $pedido_id) {
        $orden_id = (int)$orden_id;
        $orden = $this->getById($orden_id);
        // si la orden ya no existe o fue anulada, lo que llegó queda en stock y listo
        if (!$orden || $orden['estado'] === 'Anulada') return;

        $stock = new StockService();
        foreach ($recibidos as $repuesto_id => $cantidadLlegada) {
            $this->descontarPendiente($orden_id, (int)$repuesto_id, (int)$cantidadLlegada, $stock,
                "Llegó del pedido #$pedido_id para la Orden de Trabajo #$orden_id");
        }
        $this->actualizarVehiculoSiCompleta($orden_id);
    }

    // Botón "Descontar pendientes": intenta descontar del stock actual lo que le falta a la orden
    // (sirve para los repuestos sin proveedor, o si el pedido automático se canceló y se compró por otro lado)
    public function aplicarPendientes($orden_id) {
        $orden_id = (int)$orden_id;
        $orden = $this->getById($orden_id);
        if (!$orden || $orden['estado'] !== 'Abierta') {
            return ['ok' => false, 'error' => 'Solo se pueden descontar pendientes de una orden abierta.'];
        }

        $stock = new StockService();
        $descontados = 0;
        Tx::iniciar($this->db);
        try {
            foreach ($this->getItems($orden_id) as $it) {
                if ((int)$it['cantidad_pendiente'] <= 0) continue;
                // traigo el stock actual (con bloqueo) para saber cuánto puedo descontar
                $stmt = $this->db->prepare("SELECT stock FROM repuestos WHERE id = ? FOR UPDATE");
                $stmt->bind_param('i', $it['repuesto_id']);
                $stmt->execute();
                $hay = (int)($stmt->get_result()->fetch_assoc()['stock'] ?? 0);

                $descontados += $this->descontarPendiente($orden_id, (int)$it['repuesto_id'], $hay, $stock,
                    "Entrega de pendientes para la Orden de Trabajo #$orden_id");
            }
            $this->actualizarVehiculoSiCompleta($orden_id);
            if (!Tx::terminar($this->db, true)) throw new Exception('No se pudo guardar.');
            return ['ok' => true, 'descontados' => $descontados];
        } catch (Throwable $e) {
            Tx::terminar($this->db, false);
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    // Descuenta hasta $disponible unidades de lo pendiente de un repuesto en la orden.
    // Devuelve cuántas descontó. Si algo falla tira una excepción (y la transacción de afuera deshace todo).
    private function descontarPendiente($orden_id, $repuesto_id, $disponible, $stock, $descripcion) {
        $stmt = $this->db->prepare(
            "SELECT id, cantidad_pendiente FROM orden_trabajo_repuesto
             WHERE orden_trabajo_id = ? AND repuesto_id = ? AND cantidad_pendiente > 0 FOR UPDATE"
        );
        $stmt->bind_param('ii', $orden_id, $repuesto_id);
        $stmt->execute();
        $renglon = $stmt->get_result()->fetch_assoc();
        if (!$renglon || $disponible <= 0) return 0;

        $aplicar = min((int)$renglon['cantidad_pendiente'], $disponible);

        $res = $stock->salida($repuesto_id, $aplicar, 'Orden de Trabajo', $orden_id, $descripcion);
        if (!$res['ok']) throw new Exception($res['error']);

        $stmt = $this->db->prepare("UPDATE orden_trabajo_repuesto SET cantidad_pendiente = cantidad_pendiente - ? WHERE id = ?");
        $stmt->bind_param('ii', $aplicar, $renglon['id']);
        $stmt->execute();
        return $aplicar;
    }

    // Cerrar una orden: solo si está abierta y ya no le falta ningún repuesto
    public function cerrar($id) {
        $orden = $this->getById($id);
        if (!$orden) return ['ok' => false, 'error' => 'La orden no existe.'];
        if ($orden['estado'] !== 'Abierta') return ['ok' => false, 'error' => 'La orden ya está ' . mb_strtolower($orden['estado']) . '.'];

        $pendientes = array_sum(array_column($this->getItems($id), 'cantidad_pendiente'));
        if ($pendientes > 0) {
            return ['ok' => false, 'error' => "No se puede cerrar: todavía faltan $pendientes repuesto(s) por llegar. Cuando lleguen, usá \"Descontar pendientes\" o anulá la orden."];
        }

        $this->cambiarEstado($id, 'Cerrada');
        return ['ok' => true];
    }

    // Anular una orden (por ejemplo, se cargó mal o el cliente no hizo el trabajo):
    // devuelve al stock lo que se había descontado y cancela sus pedidos automáticos que no llegaron.
    public function anular($id) {
        $id = (int)$id;
        $orden = $this->getById($id);
        if (!$orden) return ['ok' => false, 'error' => 'La orden no existe.'];
        if ($orden['estado'] !== 'Abierta') return ['ok' => false, 'error' => 'Solo se puede anular una orden abierta.'];

        $stock = new StockService();
        Tx::iniciar($this->db);
        try {
            foreach ($this->getItems($id) as $it) {
                $descontado = (int)$it['cantidad'] - (int)$it['cantidad_pendiente']; // lo que sí salió del stock
                if ($descontado > 0) {
                    $res = $stock->entrada($it['repuesto_id'], $descontado, 'Orden de Trabajo', $id,
                                           "Devolución por anulación de la Orden de Trabajo #$id: $descontado x {$it['nombre']}");
                    if (!$res['ok']) throw new Exception($res['error']);
                }
            }
            $stmt = $this->db->prepare("UPDATE orden_trabajo_repuesto SET cantidad_pendiente = 0 WHERE orden_trabajo_id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();

            // sus pedidos automáticos que todavía no llegaron ya no hacen falta
            $stmt = $this->db->prepare("UPDATE pedidos SET estado_pedido = 'Cancelado' WHERE orden_trabajo_id = ? AND estado_pedido = 'Pendiente'");
            $stmt->bind_param('i', $id);
            $stmt->execute();

            $this->cambiarEstado($id, 'Anulada');
            if (!Tx::terminar($this->db, true)) throw new Exception('No se pudo anular la orden.');
            return ['ok' => true];
        } catch (Throwable $e) {
            Tx::terminar($this->db, false);
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    // Cambiar el estado de una orden (solo estos tres valores)
    public function cambiarEstado($id, $estado) {
        if (!in_array($estado, ['Abierta', 'Cerrada', 'Anulada'], true)) return false;
        $id = (int)$id;
        $stmt = $this->db->prepare("UPDATE orden_trabajo SET estado = ? WHERE id = ?");
        $stmt->bind_param('si', $estado, $id);
        return $stmt->execute();
    }

    // Si la orden ya no tiene pendientes y el vehículo estaba "Esperando Repuestos", pasa a "En Reparación"
    private function actualizarVehiculoSiCompleta($orden_id) {
        $orden = $this->getById($orden_id);
        if (!$orden) return;
        $pendientes = array_sum(array_column($this->getItems($orden_id), 'cantidad_pendiente'));
        if ($pendientes > 0) return;

        $stmt = $this->db->prepare(
            "UPDATE vehiculo SET estado_taller = 'En Reparación' WHERE id = ? AND estado_taller = 'Esperando Repuestos'"
        );
        $stmt->bind_param('i', $orden['vehiculo_id']);
        $stmt->execute();
    }

    private function cambiarEstadoVehiculo($vehiculo_id, $estado) {
        $stmt = $this->db->prepare("UPDATE vehiculo SET estado_taller = ? WHERE id = ?");
        $stmt->bind_param('si', $estado, $vehiculo_id);
        $stmt->execute();
    }
}
