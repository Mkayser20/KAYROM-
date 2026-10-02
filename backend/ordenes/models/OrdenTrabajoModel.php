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

        // Los repuestos usados llegan como arrays paralelos: repuesto_id[] y cantidad_usada[]
        $repuestoIds   = $_POST['repuesto_id'] ?? [];
        $cantidades    = $_POST['cantidad_usada'] ?? [];
        $items = [];
        foreach ($repuestoIds as $i => $rid) {
            if (empty($rid)) continue;
            $items[] = ['repuesto_id' => $rid, 'cantidad' => $cantidades[$i] ?? 1];
        }

        // DIAGNÓSTICO: cuántos repuestos llegaron desde el formulario
        $recibidos = count($items);

        $resultado = $this->model->create($vehiculo_id, $mecanico_id, $descripcion, $items);

        // NUEVO: si algo falló, no se guardó nada y se le avisa al usuario
        if (empty($resultado['success'])) {
            $mensaje = 'No se pudo crear la orden de trabajo: ' . ($resultado['error'] ?? 'error desconocido');
            header('Location: index.php?page=vehiculos&action=verFicha&id=' . $vehiculo_id . '&ot_aviso=' . urlencode($mensaje));
            exit;
        }

        $this->auditoria->registrar(
            'CREAR',
            'Órdenes de Trabajo',
            "Alta de orden de trabajo #{$resultado['id']} para vehículo ID $vehiculo_id",
            $resultado['id']
        );

        // DIAGNÓSTICO: siempre se muestra un resumen de lo recibido y lo guardado
        $resumen = "Orden #{$resultado['id']} creada. Repuestos recibidos del formulario: $recibidos. Guardados en la orden: " . ($resultado['guardados'] ?? 0) . '.';
        if ($recibidos === 0) {
            $resumen .= ' ATENCIÓN: no llegó ningún repuesto (¿quedó "Elegir repuesto" sin seleccionar?).';
        }
        $resultado['avisos'] = array_merge([$resumen], $resultado['avisos'] ?? []);

        if (!empty($resultado['avisos'])) {
            // Se muestra el resumen y, si hubo faltantes, también los pedidos automáticos generados
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