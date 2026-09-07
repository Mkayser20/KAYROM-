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
            'activePage' => 'vehiculos' //marcar página activa
        ];
        // cargar vista con lista de vehículos
        require_once 'backend/vehiculos/views/vehiculos_listado.php';
    }

    // crear un nuevo vehículo
    public function create() {
        // si es formulario POST, guardar nuevo vehículo
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->model->create($_POST);

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

            // éxito: redirigir a lista con mensaje
            header('Location: index.php?page=vehiculos&msg=created');
            exit;
        }

        // mostrar formulario vacío para crear
        $data = [
            'modelos'    => $this->model->getModelos(),
            'tipos'      => $this->model->getTipos(),
            'activePage' => 'vehiculos'
        ];
        require_once 'backend/vehiculos/views/vehiculo_form.php';
    }

    // editar un vehículo existente
    public function edit() {
        $id = (int)($_GET['id'] ?? 0); // obtener ID del vehículo a editar

        // si es formulario POST, actualizar vehículo
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->model->update($id, $_POST);

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

    //eliminar un vehículo
    public function delete() {
        $id = (int)($_GET['id'] ?? 0); // obtener ID del vehículo a eliminar

        // Cargar datos previos para la auditoría
        $vehiculo = $this->model->getById($id);
        $patente  = $vehiculo['patente'] ?? $vehiculo['dominio'] ?? "ID $id";

        if ($this->model->delete($id)) {
            // AUDITORÍA: Registro de baja
            $this->auditoria->registrar(
                'ELIMINAR',
                'Vehículos',
                "Baja de vehículo: $patente",
                $id
            );
        }

        // redirigir a lista con mensaje de éxito
        header('Location: index.php?page=vehiculos&msg=deleted');
        exit;
    }
}