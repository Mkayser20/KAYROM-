<?php
require_once __DIR__ . '/../../auditoria/AuditoriaModel.php';

// Controlador del módulo de vehículos - maneja las operaciones CRUD (Create, Read, Update, Delete)
class VehiculoController {
    private $model;     // modelo de vehículos para acceder a la base de datos
    private $auditoria; // modelo para el sistema de auditoría

    public function __construct() {
        $this->model = new VehiculoModel();
        $this->auditoria = new AuditoriaModel();
    }

    // mostrar lista de todos los vehículos
    public function index() {
        $data = [
            'vehiculos'  => $this->model->getAll(),  //obtener todos los vehículos
            'modelos'    => $this->model->getModelos(), //para el select del modal rápido
            'tipos'      => $this->model->getTipos(),   //para el select del modal rápido
            'clientes'   => (new ClienteModel())->getAll(),
            'activePage' => 'vehiculos' //marcar página activa
        ];
        // cargar vista con lista de vehículos
        require_once 'backend/vehiculos/views/vehiculos_listado.php';
    }

    // crear un nuevo vehículo
    public function create() {
        // si es formulario POST, guardar nuevo vehículo
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $clienteModel = new ClienteModel();
            $clienteId = $this->resolverClienteId($_POST, $clienteModel);
            if (isset($clienteId['error'])) {
                $result = $clienteId;
            } else {
                $_POST['cliente_id'] = $clienteId;
                $result = $this->model->create($_POST);
            }

            // si hubo error, mostrar formulario con error
            if (isset($result['error'])) {
                // Si el POST vino del modal rápido, solo devuelvo el mensaje (sin navegar de pantalla)
                if (!empty($_POST['ajax'])) {
                    http_response_code(422);
                    echo $result['error'];
                    exit;
                }
                $data = [
                    'modelos'    => $this->model->getModelos(),  // para llenar seleccionables del formulario
                    'tipos'      => $this->model->getTipos(),    // para llenar seleccionables del formulario
                    'clientes'   => $clienteModel->getAll(),
                    'activePage' => 'vehiculos',
                    'error'      => $result['error'],
                    'formData'   => $_POST // retornar datos ingresados para que no se pierdan
                ];
                require_once 'backend/vehiculos/views/vehiculo_form.php';
                return;
            }

            // AUDITORÍA: Obtener ID e información del vehículo recién creado
            $nuevoId = is_array($result) && isset($result['id'])
                ? $result['id']
                : $this->model->getLastInsertedId();

            $patente = trim($_POST['patente'] ?? $_POST['dominio'] ?? '');
            $patenteTxt = $patente !== '' ? " (Patente: $patente)" : "";

            $this->auditoria->registrar(
                'CREAR',
                'Vehículos',
                "Alta de vehículo$patenteTxt",
                $nuevoId
            );

            $clienteOrigenId = (int)($_POST['origen_cliente_id'] ?? 0);
            if ($clienteOrigenId > 0) {
                header('Location: index.php?page=clientes&action=verFicha&id=' . $clienteOrigenId . '&msg=created');
                exit;
            }

            // éxito: redirigir a lista con mensaje
            header('Location: index.php?page=vehiculos&msg=created');
            exit;
        }

        // mostrar formulario vacío para crear
        $data = [
            'modelos'    => $this->model->getModelos(),
            'tipos'      => $this->model->getTipos(),
            'clientes'   => (new ClienteModel())->getAll(),
            'formData'   => [
                'cliente_id' => (int)($_GET['cliente_id'] ?? 0),
                'origen_cliente_id' => (int)($_GET['cliente_id'] ?? 0),
            ],
            'activePage' => 'vehiculos'
        ];
        require_once 'backend/vehiculos/views/vehiculo_form.php';
    }

    // editar un vehículo existente
    public function edit() {
        $id = (int)($_GET['id'] ?? 0); // obtener ID del vehículo a editar

        // si es formulario POST, actualizar vehículo
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $clienteModel = new ClienteModel();
            $clienteId = $this->resolverClienteId($_POST, $clienteModel);
            if (isset($clienteId['error'])) {
                $result = $clienteId;
            } else {
                $_POST['cliente_id'] = $clienteId;
                $result = $this->model->update($id, $_POST);
            }

            // si hubo error, mostrar formulario con error
            if (isset($result['error'])) {
                if (!empty($_POST['ajax'])) {
                    http_response_code(422);
                    echo $result['error'];
                    exit;
                }
                $data = [
                    'vehiculo'   => $this->model->getById($id),  // cargar datos actuales
                    'modelos'    => $this->model->getModelos(),
                    'tipos'      => $this->model->getTipos(),
                    'clientes'   => $clienteModel->getAll(),
                    'activePage' => 'vehiculos',
                    'error'      => $result['error'],
                    'formData'   => $_POST
                ];
                require_once 'backend/vehiculos/views/vehiculo_form.php';
                return;
            }

            // AUDITORÍA: Registro de modificación
            $patente = trim($_POST['patente'] ?? $_POST['dominio'] ?? '');
            $patenteTxt = $patente !== '' ? " ($patente)" : "";

            $this->auditoria->registrar(
                'EDITAR',
                'Vehículos',
                "Modificación de vehículo ID $id$patenteTxt",
                $id
            );

            // éxito: redirigir a lista con mensaje
            header('Location: index.php?page=vehiculos&msg=updated');
            exit;
        }

        // mostrar formulario con datos del vehículo
        $data = [
            'vehiculo'   => $this->model->getById($id),
            'modelos'    => $this->model->getModelos(),
            'tipos'      => $this->model->getTipos(),
            'clientes'   => (new ClienteModel())->getAll(),
            'activePage' => 'vehiculos'
        ];
        require_once 'backend/vehiculos/views/vehiculo_form.php';
    }

    //mostrar ficha técnica de un solo vehículo (solo lectura + sus órdenes de trabajo)
    public function verFicha() {
        $id = (int)($_GET['id'] ?? 0);
        $ordenModel = new OrdenTrabajoModel();
        $data = [
            'vehiculo'   => $this->model->getById($id),
            'ordenes'    => $ordenModel->getByVehiculo($id),
            'mecanicos'  => (new EmpleadoModel())->getAll(),
            'repuestos'  => (new RepuestoModel())->getAll(),
            'activePage' => 'vehiculos'
        ];
        require_once 'backend/vehiculos/views/vehiculo_ficha.php';
    }

    private function resolverClienteId($datos, ClienteModel $clienteModel) {
        $seleccion = trim((string)($datos['cliente_id'] ?? ''));
        if ($seleccion !== '' && $seleccion !== 'nuevo') {
            if (!ctype_digit($seleccion) || (int)$seleccion <= 0 || !$clienteModel->getById((int)$seleccion)) {
                return ['error' => 'Seleccioná un cliente válido'];
            }
            return (int)$seleccion;
        }

        if ($seleccion === 'nuevo') {
            $dni = trim((string)($datos['cliente_dni'] ?? ''));
            if ($dni === '') {
                return ['error' => 'Ingresá el DNI para crear o buscar el cliente'];
            }

            $existente = $clienteModel->getByDni($dni);
            if ($existente) {
                return (int)$existente['id'];
            }
            if (trim((string)($datos['cliente_nombre'] ?? '')) === ''
                || trim((string)($datos['cliente_apellido'] ?? '')) === '') {
                return ['error' => 'Ingresá nombre y apellido para registrar un cliente nuevo'];
            }

            return $clienteModel->crearOEncontrar(
                $datos['cliente_nombre'] ?? '',
                $datos['cliente_apellido'] ?? '',
                $dni,
                $datos['cliente_telefono'] ?? ''
            );
        }

        if (!empty($datos['cliente_dni'])) {
            return $clienteModel->crearOEncontrar(
                $datos['cliente_nombre'] ?? '',
                $datos['cliente_apellido'] ?? '',
                $datos['cliente_dni'],
                $datos['cliente_telefono'] ?? ''
            );
        }

        return null;
    }

    // cambiar solo el estado de un vehículo (llega por AJAX desde el desplegable del listado)
    public function cambiarEstado() {
        $id     = (int)($_POST['id'] ?? 0);
        $estado = $_POST['estado'] ?? '';

        // si algo falla, devuelvo el error en texto plano (igual que en los modales)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->model->cambiarEstado($id, $estado)) {
            http_response_code(422);
            echo 'No se pudo cambiar el estado del vehículo.';
            exit;
        }

        // AUDITORÍA: Registro del cambio de estado
        $vehiculo = $this->model->getById($id);
        $patente  = $vehiculo['patente'] ?? "ID $id";
        $this->auditoria->registrar(
            'EDITAR',
            'Vehículos',
            "Cambio de estado del vehículo $patente a: $estado",
            $id
        );

        echo 'ok';
        exit;
    }

    //eliminar un vehículo
    public function delete() {
        $id = (int)($_POST['id'] ?? 0); // obtener ID del vehículo a eliminar

        // Cargar datos previos para la auditoría
        $vehiculo = $this->model->getById($id);
        $patente  = $vehiculo['patente'] ?? $vehiculo['dominio'] ?? "ID $id";

        $resultado = $this->model->delete($id);
        if (isset($resultado['error'])) {
            header('Location: index.php?page=vehiculos&msg=' . urlencode($resultado['error']));
            exit;
        }

        if ($resultado['success']) {
            // AUDITORÍA: Registro de baja
            $this->auditoria->registrar(
                'ELIMINAR',
                'Vehículos',
                "Baja de vehículo: $patente",
                $id
            );
        }

        header('Location: index.php?page=vehiculos&msg=deleted');
        exit;
    }
}
