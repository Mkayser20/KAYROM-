<?php
require_once __DIR__ . '/../../auditoria/AuditoriaModel.php';

// Controlador de Órdenes de Trabajo: se crean y cierran desde la Ficha Técnica del vehículo
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

        // Los repuestos usados llegan como arrays paralelos: repuesto_id[] y cantidad[]
        $repuestoIds   = $_POST['repuesto_id'] ?? [];
        $cantidades    = $_POST['cantidad_usada'] ?? [];
        $items = [];
        foreach ($repuestoIds as $i => $rid) {
            if (empty($rid)) continue;
            $items[] = ['repuesto_id' => $rid, 'cantidad' => $cantidades[$i] ?? 1];
        }

        $resultado = $this->model->create($vehiculo_id, $mecanico_id, $descripcion, $items);

        if (!empty($resultado['id'])) {
            $this->auditoria->registrar(
                'CREAR',
                'Órdenes de Trabajo',
                "Alta de orden de trabajo #{$resultado['id']} para vehículo ID $vehiculo_id",
                $resultado['id']
            );
        }

        if (!empty($resultado['avisos'])) {
            // Hubo faltantes: se generaron pedidos automáticos, se lo aviso al mecánico
            $mensaje = implode(' | ', $resultado['avisos']);
            header('Location: index.php?page=vehiculos&action=verFicha&id=' . $vehiculo_id . '&ot_aviso=' . urlencode($mensaje));
            exit;
        }

        header('Location: index.php?page=vehiculos&action=verFicha&id=' . $vehiculo_id . '&msg=updated');
        exit;
    }

    // Marcar una orden de trabajo como cerrada
    public function cerrar() {
        $id = (int)($_GET['id'] ?? 0);
        $vehiculo_id = (int)($_GET['vehiculo_id'] ?? 0);

        if ($this->model->cambiarEstado($id, 'Cerrada')) {
            $this->auditoria->registrar(
                'EDITAR',
                'Órdenes de Trabajo',
                "Cierre de orden de trabajo #$id",
                $id
            );
        }

        header('Location: index.php?page=vehiculos&action=verFicha&id=' . $vehiculo_id . '&msg=updated');
        exit;
    }
}
