<<?php
require_once __DIR__ . '/../../auditoria/AuditoriaModel.php';

// Controlador del módulo de repuestos - maneja CRUD de repuestos; los cambios de stock pasan por el StockService
class RepuestoController {
    private $model;           // modelo de repuestos
    private $stock;           // el "encargado del stock" (cambia el stock y anota el movimiento)
    private $auditoria;       // modelo para el sistema de auditoría

    public function __construct() {
        $this->model = new RepuestoModel();
        $this->stock = new StockService();
        $this->auditoria = new AuditoriaModel();
    }

    // mostrar lista de todos los repuestos
    public function index() {
        $proveedorModel = new ProveedorModel();
        $data = [
            'repuestos'  => $this->model->getAll(),      //obtener todos los repuestos
            'categorias' => $this->model->getCategorias(), //categorías reales, para el filtro
            'proveedores'=> $proveedorModel->getAll(),    //para el select de proveedor en el alta rápida
            'activePage' => 'repuestos'
        ];
        require_once 'backend/repuestos/views/repuestos.php';
    }

    // crear un nuevo repuesto
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // El stock inicial NO se guarda directo: se crea el repuesto en 0 y el StockService
            // suma el stock inicial, así queda el movimiento anotado de forma real.
            $stockInicial   = max(0, (int)($_POST['stock'] ?? 0));
            $_POST['stock'] = 0;
            $nuevoId = $this->model->create($_POST);

            $nombreRepuesto = trim($_POST['nombre'] ?? '');

            if ($nuevoId && $stockInicial > 0) {
                $this->stock->entrada($nuevoId, $stockInicial, 'Alta', null, 'Alta de repuesto: ' . $nombreRepuesto);
            }

            // AUDITORÍA: Registro de creación
            $this->auditoria->registrar(
                'CREAR',
                'Repuestos',
                "Alta de repuesto: $nombreRepuesto (Stock inicial: $stockInicial)",
                $nuevoId
            );

            // redirigir con mensaje de éxito
            header('Location: index.php?page=repuestos&msg=created');
            exit;
        }
        //mostrar formulario vacío
        $data = [
            'proveedores' => (new ProveedorModel())->getAll(),
            'activePage'  => 'repuestos'
        ];
        require_once 'backend/repuestos/views/repuesto_form.php';
    }

    // editar un repuesto existente
    public function edit() {
        $id = $_GET['id'] ?? 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // El stock tampoco se cambia directo: si el usuario cambió el número,
            // el StockService lo registra como "Ajuste manual" en Movimientos.
            $anterior   = $this->model->getById($id);
            $nuevoStock = max(0, (int)($_POST['stock'] ?? 0));
            $_POST['stock'] = $anterior['stock'] ?? 0; // el update de datos no toca el stock

            $this->model->update($id, $_POST);
            $this->stock->ajustarA($id, $nuevoStock, 'Ajuste manual', null, 'Ajuste manual desde la edición del repuesto');

            // AUDITORÍA: Registro de modificación
            $nombreRepuesto = trim($_POST['nombre'] ?? '');
            $this->auditoria->registrar(
                'EDITAR',
                'Repuestos',
                "Modificación de repuesto ID $id: $nombreRepuesto",
                $id
            );

            // redirigir con mensaje de éxito
            header('Location: index.php?page=repuestos&msg=updated');
            exit;
        }

        // mostrar formulario con datos del repuesto
        $data = [
            'repuesto'    => $this->model->getById($id),
            'proveedores' => (new ProveedorModel())->getAll(),
            'activePage'  => 'repuestos'
        ];
        require_once 'backend/repuestos/views/repuesto_form.php';
    }

    // eliminar un repuesto
    public function delete() {
        $id = (int)($_GET['id'] ?? 0);

        // Obtenemos los datos antes de eliminar (nombre para la auditoría, stock para el movimiento)
        $repuesto = $this->model->getById($id);
        if (!$repuesto) {
            header('Location: index.php?page=repuestos&msg=error');
            exit;
        }
        $nombreRepuesto = $repuesto['nombre'];
        $stockActual    = (int)$repuesto['stock'];

        // Todo junto: si no se puede eliminar, tampoco se anota el movimiento
        $db = Database::getInstance()->getConexion();
        Tx::iniciar($db);
        try {
            // Si tenía unidades en stock, esas unidades "salen" del inventario: queda anotado en Movimientos
            if ($stockActual > 0) {
                $res = $this->stock->salida($id, $stockActual, 'Baja de repuesto', $id,
                    "Baja de repuesto: $nombreRepuesto ($stockActual unidad/es dadas de baja)");
                if (!$res['ok']) throw new Exception($res['error']);
            }
            if (!$this->model->delete($id)) throw new Exception('No se pudo eliminar el repuesto.');
            Tx::terminar($db, true);
        } catch (Throwable $e) {
            // Típico: el repuesto ya se usó en una orden de trabajo o está en un pedido
            Tx::terminar($db, false);
            header('Location: index.php?page=repuestos&msg=repuesto_en_uso');
            exit;
        }

        // AUDITORÍA: Registro de eliminación
        $this->auditoria->registrar('ELIMINAR', 'Repuestos', "Baja de repuesto: $nombreRepuesto", $id);

        header('Location: index.php?page=repuestos&msg=deleted');
        exit;
    }
}