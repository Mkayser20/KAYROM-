<?php
require_once __DIR__ . '/../../auditoria/AuditoriaModel.php';

// Controlador de Movimientos: ver el historial y registrar un movimiento manual
// (por ejemplo: llegó mercadería sin pedido, o se rompió/perdió un repuesto).
class MovimientoController {
    private $model;     // para leer el historial
    private $stock;     // el "encargado del stock"
    private $auditoria; // para dejar rastro en la auditoría

    public function __construct() {
        $this->model     = new MovimientoModel();
        $this->stock     = new StockService();
        $this->auditoria = new AuditoriaModel();
    }

    // Mostrar el historial completo
    public function index() {
        $data = [
            'movimientos' => $this->model->getAll(),
            'activePage'  => 'movimientos'
        ];
        require_once 'backend/movimiento/views/movimientos.php';
    }

    // Registrar un movimiento manual (Entrada o Salida de un repuesto)
    public function create() {
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $repuesto_id = (int)($_POST['repuesto_id'] ?? 0);
            $tipo        = $_POST['tipo'] ?? '';
            $cantidad    = (int)($_POST['cantidad'] ?? 0);
            $motivo      = trim($_POST['descripcion'] ?? '');

            // Solo se permiten estos dos tipos desde acá
            if (!in_array($tipo, ['Entrada', 'Salida'])) {
                $error = 'Tipo de movimiento inválido.';
            } else {
                // El StockService cambia el stock Y anota el movimiento
                $res = ($tipo === 'Entrada')
                    ? $this->stock->entrada($repuesto_id, $cantidad, 'Manual', null, $motivo)
                    : $this->stock->salida($repuesto_id, $cantidad, 'Manual', null, $motivo);

                if ($res['ok']) {
                    $this->auditoria->registrar(
                        'CREAR',
                        'Movimientos',
                        "Movimiento manual de $tipo: $cantidad unidad(es) del repuesto ID $repuesto_id" . ($motivo ? " ($motivo)" : ''),
                        $res['movimiento_id']
                    );
                    header('Location: index.php?page=movimientos&msg=created');
                    exit;
                }
                $error = $res['error']; // por ejemplo: "Stock insuficiente..."
            }
        }

        // Mostrar el formulario (vacío, o con el error si algo falló)
        $data = [
            'repuestos'  => (new RepuestoModel())->getAll(),
            'error'      => $error,
            'activePage' => 'movimientos'
        ];
        require_once 'backend/movimiento/views/movimiento_form.php';
    }
}