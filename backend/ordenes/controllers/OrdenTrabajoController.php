<?php
require_once __DIR__ . '/../../auditoria/AuditoriaModel.php';

// Controlador de Órdenes de Trabajo: se crean, cierran y anulan desde la Ficha Técnica del vehículo
// (antes este archivo y el del modelo estaban cruzados: cada clase estaba en la carpeta de la otra)
class OrdenTrabajoController {
    private $model;
    private $auditoria;

    public function __construct() {
        $this->model = new OrdenTrabajoModel();
        $this->auditoria = new AuditoriaModel();
    }

    // Crear una nueva orden de trabajo (mecánico + repuestos usados) para un vehículo
    public function create() {
        $vehiculo_id = (int)($_POST['vehiculo_id'] ?? 0);
        $mecanico_id = $_POST['mecanico_id'] ?? null;
        $descripcion = $_POST['descripcion'] ?? '';

        // Los repuestos usados llegan como arrays paralelos: repuesto_id[] y cantidad_usada[]
        $repuestoIds   = (array)($_POST['repuesto_id'] ?? []);
        $cantidades    = (array)($_POST['cantidad_usada'] ?? []);
        $items = [];
        foreach ($repuestoIds as $i => $rid) {
            if (empty($rid)) continue;
            $items[] = ['repuesto_id' => $rid, 'cantidad' => $cantidades[$i] ?? 1];
        }

        $resultado = $this->model->create($vehiculo_id, $mecanico_id, $descripcion, $items);

        // si algo falló, no se guardó nada y se le avisa al usuario
        if (empty($resultado['success'])) {
            $this->volverAFicha($vehiculo_id, 'No se pudo crear la orden de trabajo: ' . ($resultado['error'] ?? 'error desconocido'));
        }

        $this->auditoria->registrar(
            'CREAR',
            'Órdenes de Trabajo',
            "Alta de orden de trabajo #{$resultado['id']} para vehículo ID $vehiculo_id",
            $resultado['id']
        );

        // resumen de lo guardado + avisos de faltantes (si hubo)
        $resumen = "Orden #{$resultado['id']} creada con {$resultado['guardados']} repuesto(s).";
        if (count($items) === 0) {
            $resumen .= ' ATENCIÓN: no llegó ningún repuesto (¿quedó "Elegir repuesto" sin seleccionar?).';
        }
        $this->volverAFicha($vehiculo_id, implode(' | ', array_merge([$resumen], $resultado['avisos'] ?? [])));
    }

    // Marcar una orden de trabajo como cerrada (solo si ya no le falta ningún repuesto)
    public function cerrar() {
        $id    = (int)($_GET['id'] ?? 0);
        $orden = $this->model->getById($id);
        $res   = $this->model->cerrar($id);

        if ($res['ok']) {
            $this->auditoria->registrar('EDITAR', 'Órdenes de Trabajo', "Cierre de orden de trabajo #$id", $id);
            $this->volverAFicha($orden['vehiculo_id'], "Orden #$id cerrada.");
        }
        $this->volverAFicha($orden['vehiculo_id'] ?? 0, $res['error']);
    }

    // Anular una orden: devuelve el stock que se había usado y cancela sus pedidos automáticos pendientes
    public function anular() {
        $id    = (int)($_GET['id'] ?? 0);
        $orden = $this->model->getById($id);
        $res   = $this->model->anular($id);

        if ($res['ok']) {
            $this->auditoria->registrar('EDITAR', 'Órdenes de Trabajo', "Anulación de orden de trabajo #$id (stock devuelto)", $id);
            $this->volverAFicha($orden['vehiculo_id'], "Orden #$id anulada: los repuestos usados volvieron al stock.");
        }
        $this->volverAFicha($orden['vehiculo_id'] ?? 0, 'No se pudo anular: ' . $res['error']);
    }

    // Descontar del stock actual lo que la orden tenía pendiente (por ejemplo, repuestos sin proveedor)
    public function descontarPendientes() {
        $id    = (int)($_GET['id'] ?? 0);
        $orden = $this->model->getById($id);
        $res   = $this->model->aplicarPendientes($id);

        if (!$res['ok']) {
            $this->volverAFicha($orden['vehiculo_id'] ?? 0, 'No se pudo: ' . $res['error']);
        }
        if ($res['descontados'] > 0) {
            $this->auditoria->registrar('EDITAR', 'Órdenes de Trabajo', "Se descontaron {$res['descontados']} repuesto(s) pendientes de la orden #$id", $id);
            $this->volverAFicha($orden['vehiculo_id'], "Se descontaron {$res['descontados']} repuesto(s) pendientes de la orden #$id.");
        }
        $this->volverAFicha($orden['vehiculo_id'], 'Todavía no hay stock de los repuestos pendientes.');
    }

    // Vuelve a la ficha del vehículo mostrando un aviso
    private function volverAFicha($vehiculo_id, $mensaje) {
        $vehiculo_id = (int)$vehiculo_id;
        $destino = $vehiculo_id > 0
            ? 'index.php?page=vehiculos&action=verFicha&id=' . $vehiculo_id . '&ot_aviso=' . urlencode($mensaje)
            : 'index.php?page=vehiculos&msg=error';
        header('Location: ' . $destino);
        exit;
    }
}
