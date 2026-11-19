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

    // El alta de empleados se hace en "Registrar usuario", porque un empleado necesita
    // usuario y contraseña para entrar. Antes este alta guardaba solo en la tabla empleado
    // (sin usuario) y el empleado nuevo nunca aparecía en la lista.
    public function create() {
        if (!empty($_GET['modal'])) {
            // si se pidió desde el modal, saco al usuario del iframe y lo llevo a la pantalla completa
            echo "<script>parent.location.href = 'index.php?page=registrar_usuario';</script>";
            exit;
        }
        header('Location: index.php?page=registrar_usuario');
        exit;
    }

    public function edit() {
        $id = (int)($_GET['id'] ?? 0); // siempre número
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            //  Validación de unicidad (excluyendo al propio usuario que se edita)
            $usuarioModel = new UsuarioModel();

            $actual    = $this->model->getById($id);
            $personaId = $actual['persona_id'] ?? 0;

            $email    = trim($_POST['email']            ?? '');
            $usuario  = trim($_POST['nombre_usuario']   ?? '');
            $dni      = trim($_POST['dni']              ?? '');
            $telefono = trim($_POST['telefono_persona'] ?? '');
            $rolNuevo = UsuarioModel::normalizarRol($_POST['rol'] ?? 'empleado');
            $esUnoMismo = $id === (int)($_SESSION['usuario_id'] ?? 0);

            if (!$actual) {
                $error = 'El empleado no existe.';
            }
            // el admin no puede sacarse el rol ni desactivarse a sí mismo (se quedaría afuera del sistema)
            elseif ($esUnoMismo && ($rolNuevo !== 'admin' || (int)($_POST['activo'] ?? 1) !== 1)) {
                $error = 'No podés quitarte el rol de administrador ni desactivar tu propio usuario.';
            }
            elseif ($usuarioModel->existeEmail($email, $id)) {
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

                // Guardar los permisos seleccionados en los checkboxes (el admin no necesita, tiene todo)
                if ($rolNuevo !== 'admin') {
                    $permisoModel = new PermisoModel();
                    $permisoModel->setForUsuario($id, $_POST['modulos'] ?? []);
                }

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
                $error = $this->model->error ?: 'Error al actualizar.';
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

    // Activar / desactivar un empleado desde el check de la tabla (llega por AJAX)
    public function cambiarActivo() {
        $id     = (int)($_POST['id'] ?? 0);
        $activo = !empty($_POST['activo']) ? 1 : 0; // "1" = activo, "0" = inactivo

        // El admin no puede desactivarse a sí mismo (se quedaría sin poder entrar)
        if ($id === (int)($_SESSION['usuario_id'] ?? 0) && !$activo) {
            http_response_code(422);
            echo 'No podés desactivar tu propio usuario.';
            exit;
        }

        $empleado = $this->model->getById($id);
        if (!$empleado || !$this->model->cambiarActivo($id, $activo)) {
            http_response_code(422);
            echo 'No se pudo cambiar el estado del empleado.';
            exit;
        }

        // AUDITORÍA: Activación / desactivación
        $nombreCompleto = trim(($empleado['nombre'] ?? '') . ' ' . ($empleado['apellido'] ?? ''));
        $this->auditoria->registrar(
            'EDITAR',
            'Empleados',
            "Empleado $nombreCompleto " . ($activo ? 'activado' : 'desactivado'),
            $id
        );

        echo 'ok';
        exit;
    }

    // Dar de baja un empleado (se desactiva, no se borra: ver EmpleadoModel::delete)
    public function delete() {
        $id = (int)($_POST['id'] ?? 0);

        if ($id === (int)($_SESSION['usuario_id'] ?? 0)) {
            header('Location: index.php?page=empleados&msg=error');
            exit;
        }

        $empleado = $this->model->getById($id);
        if (!$empleado || !$this->model->delete($id)) {
            header('Location: index.php?page=empleados&msg=error');
            exit;
        }

        $nombreCompleto = trim(($empleado['nombre'] ?? '') . ' ' . ($empleado['apellido'] ?? ''));
        $this->auditoria->registrar(
            'ELIMINAR',
            'Empleados',
            "Baja (desactivación) de empleado: $nombreCompleto",
            $id
        );

        header('Location: index.php?page=empleados&msg=deleted');
        exit;
    }
}
