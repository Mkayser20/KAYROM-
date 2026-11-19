<?php
// Modelo de Pedidos a proveedores.
// Cambio importante: un pedido ahora tiene RENGLONES (tabla pedido_item):
// cada renglón dice qué repuesto y cuántos. Así, al recibir el pedido,
// el sistema sabe exactamente qué sumar al stock.
class PedidoModel {
    private $db;           // conexión a la base de datos
    public  $error = '';   // último mensaje de error (para mostrarlo en pantalla)

    public function __construct() {
        $this->db = Database::getInstance()->getConexion();
    }

    // Todos los pedidos, del más nuevo al más viejo (con proveedor y patente del vehículo si tiene)
    public function getAll() {
        $sql = "SELECT p.*, dp.detalle_pedido, pr.nombre_proveedor, v.patente
                FROM pedidos p
                LEFT JOIN detalle_pedido dp ON dp.id = p.detalle_pedido_id
                LEFT JOIN proveedor pr      ON pr.id = p.proveedor_id
                LEFT JOIN vehiculo v        ON v.id  = p.vehiculo_id
                ORDER BY p.fecha_pedidos DESC, p.id DESC";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Los últimos X pedidos (por defecto 5)
    public function getRecent($limit = 5) {
        $limit = max(1, (int)$limit);
        $sql = "SELECT p.*, dp.detalle_pedido
                FROM pedidos p
                LEFT JOIN detalle_pedido dp ON dp.id = p.detalle_pedido_id
                ORDER BY p.fecha_pedidos DESC LIMIT $limit";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Un pedido puntual
    public function getById($id) {
        $id = (int)$id;
        $stmt = $this->db->prepare(
            "SELECT p.*, dp.detalle_pedido FROM pedidos p
             LEFT JOIN detalle_pedido dp ON dp.id = p.detalle_pedido_id
             WHERE p.id = ?"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Los renglones de un pedido (qué repuestos y cuántos)
    public function getItems($pedido_id) {
        $stmt = $this->db->prepare(
            "SELECT pi.*, r.nombre
             FROM pedido_item pi
             JOIN repuestos r ON r.id = pi.repuesto_id
             WHERE pi.pedido_id = ?"
        );
        $stmt->bind_param('i', $pedido_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // REMITO: junta todo lo que hace falta para armar el remito de un pedido.
    // Trae el pedido con los datos del proveedor (y su domicilio, que está en persona),
    // la patente del vehículo si tiene, y los renglones con SKU y categoría del repuesto.
    // Devuelve null si el pedido no existe.
    public function getRemito($id) {
        $id = (int)$id;

        // 1) Cabecera: el pedido + proveedor + vehículo
        $stmt = $this->db->prepare(
            "SELECT p.*, dp.detalle_pedido,
                    pr.nombre_proveedor, pr.cuit, pr.telefono, pr.email,
                    pe.domicilio AS domicilio_proveedor,
                    v.patente
             FROM pedidos p
             LEFT JOIN detalle_pedido dp ON dp.id = p.detalle_pedido_id
             LEFT JOIN proveedor pr      ON pr.id = p.proveedor_id
             LEFT JOIN persona pe        ON pe.id = pr.persona_id
             LEFT JOIN vehiculo v        ON v.id  = p.vehiculo_id
             WHERE p.id = ?"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $pedido = $stmt->get_result()->fetch_assoc();
        if (!$pedido) return null;

        // 2) Renglones: qué repuestos, cuántos y a qué precio
        $stmt = $this->db->prepare(
            "SELECT pi.cantidad, pi.precio_unitario, r.nombre, r.sku, r.categoria
             FROM pedido_item pi
             JOIN repuestos r ON r.id = pi.repuesto_id
             WHERE pi.pedido_id = ?
             ORDER BY pi.id"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // 3) Calculo el subtotal de cada renglón y el total del remito
        $total = 0;
        foreach ($items as &$it) {
            $it['subtotal'] = $it['cantidad'] * $it['precio_unitario'];
            $total += $it['subtotal'];
        }
        unset($it);

        return ['pedido' => $pedido, 'items' => $items, 'total' => $total];
    }

    // Crea un pedido con sus renglones.
    // $items = lista de ['repuesto_id' => 3, 'cantidad' => 2, 'precio' => 1500 (opcional)]
    // Devuelve el ID del pedido nuevo, o false (y deja el motivo en $this->error).
    public function create($data, $items = []) {
        $this->error = '';

        // Junto los renglones repetidos y tiro los vacíos
        $renglones = [];
        foreach ($items as $it) {
            $rid = (int)($it['repuesto_id'] ?? 0);
            $cant = (int)($it['cantidad'] ?? 0);
            if ($rid <= 0 || $cant <= 0) continue;
            if (!isset($renglones[$rid])) {
                $renglones[$rid] = ['repuesto_id' => $rid, 'cantidad' => 0, 'precio' => $it['precio'] ?? null];
            }
            $renglones[$rid]['cantidad'] += $cant;
        }
        if (empty($renglones)) {
            $this->error = 'El pedido necesita al menos un repuesto con cantidad.';
            return false;
        }

        $responsable  = mb_substr(trim((string)($data['responsable_pedido'] ?? '')), 0, 45); // la columna admite 45
        // El número del pedido ya no es al azar (rand se podía repetir): se usa el mismo ID del pedido.
        $numero       = 0;
        $proveedor_id = !empty($data['proveedor_id'])      ? (int)$data['proveedor_id']      : null;
        $vehiculo_id  = !empty($data['vehiculo_id'])       ? (int)$data['vehiculo_id']       : null;
        $orden_id     = !empty($data['orden_trabajo_id'])  ? (int)$data['orden_trabajo_id']  : null;

        Tx::iniciar($this->db);
        try {
            // Completo nombre y precio de cada repuesto, y armo el texto "3x Filtro, 2x Aceite"
            $textos = [];
            $cantidadTotal = 0;
            $stmtRep = $this->db->prepare("SELECT nombre, precio FROM repuestos WHERE id = ?");
            foreach ($renglones as $rid => &$r) {
                $stmtRep->bind_param('i', $rid);
                $stmtRep->execute();
                $rep = $stmtRep->get_result()->fetch_assoc();
                if (!$rep) throw new Exception('Uno de los repuestos del pedido no existe.');
                if ($r['precio'] === null) $r['precio'] = $rep['precio'];
                $r['nombre'] = $rep['nombre'];
                $textos[] = $r['cantidad'] . 'x ' . $rep['nombre'];
                $cantidadTotal += $r['cantidad'];
            }
            unset($r);

            $detalle = !empty($data['detalle_pedido']) ? $data['detalle_pedido'] : implode(', ', $textos);
            $detalle = mb_substr((string)$detalle, 0, 500); // la columna admite 500

            // 1) El detalle (texto)
            $stmt = $this->db->prepare("INSERT INTO detalle_pedido (detalle_pedido) VALUES (?)");
            $stmt->bind_param('s', $detalle);
            $stmt->execute();
            $detalle_id = $this->db->insert_id;

            // 2) El pedido (siempre nace "Pendiente")
            $stmt = $this->db->prepare(
                "INSERT INTO pedidos (fecha_pedidos, estado_pedido, responsable_pedido, numero_unico, cantidad,
                                      detalle_pedido_id, proveedor_id, vehiculo_id, orden_trabajo_id)
                 VALUES (NOW(), 'Pendiente', ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('siiiiii', $responsable, $numero, $cantidadTotal, $detalle_id, $proveedor_id, $vehiculo_id, $orden_id);
            $stmt->execute();
            $pedido_id = $this->db->insert_id;

            // el número del pedido = su ID (así nunca se repite)
            $stmt = $this->db->prepare("UPDATE pedidos SET numero_unico = id WHERE id = ?");
            $stmt->bind_param('i', $pedido_id);
            $stmt->execute();

            // 3) Los renglones
            $stmt = $this->db->prepare(
                "INSERT INTO pedido_item (pedido_id, repuesto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)"
            );
            foreach ($renglones as $r) {
                $precio = (float)$r['precio'];
                $stmt->bind_param('iiid', $pedido_id, $r['repuesto_id'], $r['cantidad'], $precio);
                $stmt->execute();
            }

            if (!Tx::terminar($this->db, true)) throw new Exception('falló un paso interno');
            return $pedido_id;

        } catch (Throwable $e) {
            Tx::terminar($this->db, false);
            $this->error = 'No se pudo crear el pedido: ' . $e->getMessage();
            return false;
        }
    }

    // RECIBIR un pedido: llegó la mercadería.
    // Suma cada renglón al stock (con su movimiento) y marca el pedido como "Entregado".
    // Si el pedido es de una Orden de Trabajo, lo que llegó se descuenta enseguida para esa orden
    // (antes quedaba sumado al stock aunque ya estuviera puesto en el auto: el stock quedaba inflado).
    // Todo junto: si algo falla, no se cambia nada.
    public function recibir($id) {
        $id = (int)$id;

        Tx::iniciar($this->db);
        try {
            // 1) PRIMERO marco el pedido como recibido, pero solo si todavía no lo estaba.
            // Antes se revisaba el estado y después se sumaba el stock: con un doble clic
            // (o dos pestañas) las dos veces veían "Pendiente" y el stock se sumaba dos veces.
            // Con este UPDATE condicional, la base deja "ganar" a uno solo.
            $stmt = $this->db->prepare(
                "UPDATE pedidos SET estado_pedido = 'Entregado', fecha_recepcion = NOW()
                 WHERE id = ? AND (estado_pedido IS NULL OR estado_pedido NOT IN ('Entregado', 'Cancelado'))"
            );
            $stmt->bind_param('i', $id);
            $stmt->execute();
            if ($stmt->affected_rows === 0) {
                Tx::terminar($this->db, false);
                return ['ok' => false, 'error' => 'El pedido no existe, ya fue recibido o está cancelado.'];
            }

            $pedido = $this->getById($id);
            $items  = $this->getItems($id);
            $stock  = new StockService();

            // 2) Por cada renglón, entra esa cantidad al stock
            $recibidos = [];
            foreach ($items as $it) {
                $res = $stock->entrada($it['repuesto_id'], $it['cantidad'], 'Pedido', $id,
                                       "Recepción del pedido #$id: {$it['cantidad']} x {$it['nombre']}");
                if (!$res['ok']) throw new Exception($res['error']);
                $recibidos[$it['repuesto_id']] = ($recibidos[$it['repuesto_id']] ?? 0) + (int)$it['cantidad'];
            }

            // 3) Si el pedido era para una Orden de Trabajo, se descuenta lo que esa orden esperaba
            if (!empty($pedido['orden_trabajo_id'])) {
                (new OrdenTrabajoModel())->aplicarRecepcion($pedido['orden_trabajo_id'], $recibidos, $id);
            }

            if (!Tx::terminar($this->db, true)) throw new Exception('falló un paso interno');
            // "sin_renglones" = es un pedido viejo (de texto libre): se marca recibido pero no hay qué sumar
            return ['ok' => true, 'sin_renglones' => empty($items)];

        } catch (Throwable $e) {
            Tx::terminar($this->db, false);
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    // Cambia el estado de un pedido a "Pendiente" o "Cancelado" (lo usa el desplegable del listado).
    // "Recibido" NO pasa por acá: tiene que usar recibir(), que es el que suma al stock.
    public function cambiarEstado($id, $estado) {
        $id = (int)$id;
        if (!in_array($estado, ['Pendiente', 'Cancelado'], true)) return false;

        // un pedido ya recibido movió stock: no se vuelve atrás (la condición va en el mismo UPDATE)
        $stmt = $this->db->prepare(
            "UPDATE pedidos SET estado_pedido = ? WHERE id = ? AND (estado_pedido IS NULL OR estado_pedido <> 'Entregado')"
        );
        $stmt->bind_param('si', $estado, $id);
        $stmt->execute();
        return $stmt->affected_rows > 0 || ($this->getById($id)['estado_pedido'] ?? null) === $estado;
    }

    // Eliminar un pedido. Un pedido ya recibido NO se puede eliminar
    // (ya movió stock y tiene que quedar como registro).
    public function delete($id) {
        $id = (int)$id;
        $pedido = $this->getById($id);
        if (!$pedido) return false;
        if (strtolower($pedido['estado_pedido'] ?? '') === 'entregado') return false;

        Tx::iniciar($this->db);
        try {
            $stmt = $this->db->prepare("DELETE FROM pedidos WHERE id = ?"); // los renglones se borran solos (CASCADE)
            $stmt->bind_param('i', $id);
            $stmt->execute();
            if (!empty($pedido['detalle_pedido_id'])) {
                $did = (int)$pedido['detalle_pedido_id'];
                $stmt = $this->db->prepare("DELETE FROM detalle_pedido WHERE id = ?");
                $stmt->bind_param('i', $did);
                $stmt->execute();
            }
            return Tx::terminar($this->db, true);
        } catch (Throwable $e) {
            Tx::terminar($this->db, false);
            return false;
        }
    }

    // Total de pedidos registrados (para el dashboard)
    public function getTotalPedidos() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM pedidos");
        return $result ? $result->fetch_assoc()['total'] : 0;
    }

    // Cantidad de pedidos por estado (para el gráfico del dashboard)
    public function getCountByEstado() {
        $result = $this->db->query(
            "SELECT estado_pedido as label, COUNT(*) as total FROM pedidos GROUP BY estado_pedido"
        );
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}