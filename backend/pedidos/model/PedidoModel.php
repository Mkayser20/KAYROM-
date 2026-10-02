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
        $limit = (int)$limit;
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
        $sql = "SELECT p.*, dp.detalle_pedido FROM pedidos p
                LEFT JOIN detalle_pedido dp ON dp.id = p.detalle_pedido_id
                WHERE p.id = $id";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_assoc() : null;
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

        $responsable  = $data['responsable_pedido'] ?? '';
        $numero       = (int)($data['numero_unico'] ?? rand(1000, 9999));
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

            // 3) Los renglones
            $stmt = $this->db->prepare(
                "INSERT INTO pedido_item (pedido_id, repuesto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)"
            );
            foreach ($renglones as $r) {
                $precio = (float)$r['precio'];
                $stmt->bind_param('iiid', $pedido_id, $r['repuesto_id'], $r['cantidad'], $precio);
                $stmt->execute();
            }

            Tx::terminar($this->db, true);
            return $pedido_id;

        } catch (Throwable $e) {
            Tx::terminar($this->db, false);
            $this->error = 'No se pudo crear el pedido: ' . $e->getMessage();
            return false;
        }
    }

    // RECIBIR un pedido: llegó la mercadería.
    // Suma cada renglón al stock (con su movimiento) y marca el pedido como "Entregado".
    // Todo junto: si algo falla, no se cambia nada.
    public function recibir($id) {
        $id = (int)$id;
        $pedido = $this->getById($id);

        if (!$pedido) return ['ok' => false, 'error' => 'El pedido no existe.'];

        $estado = strtolower($pedido['estado_pedido'] ?? '');
        if ($estado === 'entregado') return ['ok' => false, 'error' => 'Ese pedido ya fue recibido.'];
        if ($estado === 'cancelado') return ['ok' => false, 'error' => 'Ese pedido está cancelado.'];

        $items = $this->getItems($id);
        $stock = new StockService();

        Tx::iniciar($this->db);
        try {
            // Por cada renglón, entra esa cantidad al stock
            foreach ($items as $it) {
                $res = $stock->entrada($it['repuesto_id'], $it['cantidad'], 'Pedido', $id,
                                       "Recepción del pedido #$id: {$it['cantidad']} x {$it['nombre']}");
                if (!$res['ok']) throw new Exception($res['error']);
            }

            // Marco el pedido como recibido
            $stmt = $this->db->prepare("UPDATE pedidos SET estado_pedido = 'Entregado', fecha_recepcion = NOW() WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();

            Tx::terminar($this->db, true);
            // "sin_renglones" = es un pedido viejo (de texto libre): se marca recibido pero no hay qué sumar
            return ['ok' => true, 'sin_renglones' => empty($items)];

        } catch (Throwable $e) {
            Tx::terminar($this->db, false);
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    // Eliminar un pedido. Un pedido ya recibido NO se puede eliminar
    // (ya movió stock y tiene que quedar como registro).
    public function delete($id) {
        $id = (int)$id;
        $pedido = $this->getById($id);
        if (!$pedido) return false;
        if (strtolower($pedido['estado_pedido'] ?? '') === 'entregado') return false;

        $ok = $this->db->query("DELETE FROM pedidos WHERE id = $id"); // los renglones se borran solos (CASCADE)
        if ($ok && !empty($pedido['detalle_pedido_id'])) {
            $did = (int)$pedido['detalle_pedido_id'];
            $this->db->query("DELETE FROM detalle_pedido WHERE id = $did");
        }
        return $ok;
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