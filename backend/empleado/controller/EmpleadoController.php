<?php

class EmpleadoController {
    private $model;

    public function __construct() {
        $this->model = new EmpleadoModel();
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
                header('Location: index.php?page=empleados&msg=created');
                exit;
            } else {
                $error = 'Error al guardar el empleado.';
            }
        }
        $data = ['error' => $error, 'activePage' => 'empleados'];
        require_once 'backend/empleado/views/empleado_form.php';
    }

    // excluir al usuario al editar

    public function edit() {
        $id = $_GET['id'] ?? 0;
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

             // --- Validación de unicidad (excluyendo al propio usuario que se edita) ---
            require_once 'models/UsuarioModel.php';
            $usuarioModel = new UsuarioModel();

            $actual    = $this->model->getById($id);
            $personaId = $actual['persona_id'] ?? 0;

            $email   = trim($_POST['email']          ?? '');
            $usuario = trim($_POST['nombre_usuario'] ?? '');
            $dni     = trim($_POST['dni']            ?? '');
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

                header('Location: index.php?page=empleados&msg=updated');
                exit;
            } else {
                $error = 'Error al actualizar.';
            }
        }
        $data = [
            'empleado' => $this->model->getById($id),
            'error' => $error,
            'activePage' => 'empleados'
        ];
        require_once 'backend/empleado/views/empleado_form.php';
    }

    public function delete() {
        $id = $_GET['id'] ?? 0;
        $this->model->delete($id);
        header('Location: index.php?page=empleados&msg=deleted');
        exit;
    }
}
