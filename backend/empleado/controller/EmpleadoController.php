<?php
require_once __DIR__ . '/../../auditoria/AuditoriaModel.php';

class EmpleadoController {
    private $model;
    private $auditoria;

    public function __construct() {
        $this->model = new EmpleadoModel();
        $this->auditoria = new AuditoriaModel();
    }

    public function index() {
        $data = [
            'empleados' => $this->model->getAll(),
            'activePage' => 'empleados'
        ];
        require_once 'backend/empleado/views/empleados.php';
    }

    public function create() {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->model->create($_POST)) {
                $nuevoId  = $this->model->getLastInsertedId();
                $nombre   = trim($_POST['nombre'] ?? '');
                $apellido = trim($_POST['apellido'] ?? '');

                // AUDITORÍA: Crear
                $this->auditoria->registrar(
                    'CREAR',
                    'Empleados',
                    "Alta de empleado: $nombre $apellido",
                    $nuevoId
                );

                if (!empty($_POST['modal'])) {
                    require_once 'backend/empleado/views/guardado_modal.php';
                    exit;
                }
                header('Location: index.php?page=empleados&msg=created');
                exit;
            } else {
                $error = 'Error al guardar el empleado.';
            }
        }
        $data = ['error' => $error, 'modal' => isset($_GET['modal']), 'activePage' => 'empleados'];
        require_once 'backend/empleado/views/empleado_form.php';
    }

    public function edit() {
        $id = $_GET['id'] ?? 0;
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // --- Validación de unicidad (excluyendo al propio usuario que se edita) ---
            require_once 'compartidoCREO/models/UsuarioModel.php';
            $usuarioModel = new UsuarioModel();

            $actual    = $this->model->getById($id);
            $personaId = $actual['persona_id'] ?? 0;

            $email    = trim($_POST['email']            ?? '');
            $usuario  = trim($_POST['nombre_usuario']   ?? '');
            $dni      = trim($_POST['dni']              ?? '');
            $telefono = trim($_POST['telefono_persona'] ?? '');

            if ($usuarioModel->existeEmail($email, $id)) {
                $error = "El correo '$email' ya está en uso por otro usuario.";
            }
            elseif ($usuarioModel->existeUsuario($usuario, $id)) {
                $error = "El usuario '$usuario' ya está en uso por otro usuario.";
            }
            elseif ($dni !== '' && $usuarioModel->existeDni($dni, $personaId)) {
                $error = "El DNI '$dni' ya está registrado en otra persona.";
            }
            elseif ($telefono !== '' && $usuarioModel->existeTelefono($telefono, $personaId)) {
                $error = "El teléfono '$telefono' ya está registrado en otra persona.";
            }
            elseif ($this->model->update($id, $_POST)) {

                // Guardar los permisos seleccionados en los checkboxes
                $permisoModel = new PermisoModel();
                $modulos = $_POST['modulos'] ?? [];
                $permisoModel->setForUsuario($id, $modulos);

                $nombre   = trim($_POST['nombre'] ?? '');
                $apellido = trim($_POST['apellido'] ?? '');

                // AUDITORÍA: Editar
                $this->auditoria->registrar(
                    'EDITAR',
                    'Empleados',
                    "Modificación de empleado ID $id: $nombre $apellido",
                    $id
                );

                if (!empty($_POST['modal'])) {
                    require_once 'backend/empleado/views/guardado_modal.php';
                    exit;
                }
                header('Location: index.php?page=empleados&msg=updated');
                exit;
            } else {
                $error = 'Error al actualizar.';
            }
        }
        $data = [
            'empleado' => $this->model->getById($id),
            'error' => $error,
            'modal' => isset($_GET['modal']),
            'activePage' => 'empleados'
        ];
        require_once 'backend/empleado/views/empleado_form.php';
    }

    public function delete() {
        $id = $_GET['id'] ?? 0;

        $empleado = $this->model->getById($id);
        if ($this->model->delete($id)) {
            $nombreCompleto = isset($empleado['nombre']) ? "{$empleado['nombre']} {$empleado['apellido']}" : "ID $id";

            // AUDITORÍA: Eliminar
            $this->auditoria->registrar(
                'ELIMINAR',
                'Empleados',
                "Baja de empleado: $nombreCompleto",
                $id
            );
        }

        header('Location: index.php?page=empleados&msg=deleted');
        exit;
    }
}
