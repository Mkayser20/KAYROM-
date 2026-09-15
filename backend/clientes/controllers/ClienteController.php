<?php
require_once __DIR__ . '/../../auditoria/AuditoriaModel.php';

// Controlador de Clientes: ABM + ficha con los vehículos de cada uno
class ClienteController {
    private $model;
    private $auditoria;

    public function __construct() {
        $this->model = new ClienteModel();
        $this->auditoria = new AuditoriaModel();
    }

    // Listado de clientes
    public function index() {
        $data = [
            'clientes'   => $this->model->getAll(),
            'activePage' => 'clientes'
        ];
        require_once 'backend/clientes/views/clientes.php';
    }

    // Ficha de un cliente: sus datos + todos sus vehículos
    public function verFicha() {
        $id = (int)($_GET['id'] ?? 0);
        $data = [
            'cliente'    => $this->model->getById($id),
            'vehiculos'  => $this->model->getVehiculos($id),
            'activePage' => 'clientes'
        ];
        require_once 'backend/clientes/views/cliente_ficha.php';
    }

    // Crear cliente (desde el modal del listado de Clientes)
    public function create() {
        $resultado = $this->model->create($_POST);

        if (!isset($resultado['error'])) {
            $nuevoId = $resultado['id'] ?? null;
            $nombre = trim($_POST['nombre'] ?? '');
            $apellido = trim($_POST['apellido'] ?? '');
            $dni = trim($_POST['dni'] ?? '');

            $this->auditoria->registrar(
                'CREAR',
                'Clientes',
                "Alta de cliente: $nombre $apellido (DNI: $dni)",
                $nuevoId
            );
        }

        $this->responder($resultado, 'index.php?page=clientes');
    }

    // Editar cliente
    public function edit() {
        $id = (int)($_POST['id'] ?? 0);
        $resultado = $this->model->update($id, $_POST);

        if (!isset($resultado['error'])) {
            $nombre = trim($_POST['nombre'] ?? '');
            $apellido = trim($_POST['apellido'] ?? '');

            $this->auditoria->registrar(
                'EDITAR',
                'Clientes',
                "Modificación de cliente ID $id: $nombre $apellido",
                $id
            );
        }

        $this->responder($resultado, 'index.php?page=clientes');
    }

    // Eliminar cliente
    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        $cliente = $this->model->getById($id);
        $resultado = $this->model->delete($id);

        if (!isset($resultado['error'])) {
            $nombreCompleto = trim((($cliente['nombre'] ?? '') . ' ' . ($cliente['apellido'] ?? '')));
            $descripcion = $nombreCompleto !== '' ? "Baja de cliente: $nombreCompleto" : "Baja de cliente ID $id";

            $this->auditoria->registrar(
                'ELIMINAR',
                'Clientes',
                $descripcion,
                $id
            );
        }

        if (isset($resultado['error'])) {
            header('Location: index.php?page=clientes&error=' . urlencode($resultado['error']));
        } else {
            header('Location: index.php?page=clientes&msg=deleted');
        }
        exit;
    }

    // Responde según si el pedido vino por AJAX (modal) o como formulario normal
    private function responder($resultado, $urlVolver) {
        if (isset($resultado['error'])) {
            if (!empty($_POST['ajax'])) {
                http_response_code(422);
                echo $resultado['error'];
                exit;
            }
            header('Location: ' . $urlVolver . '&error=' . urlencode($resultado['error']));
            exit;
        }
        header('Location: ' . $urlVolver . '&msg=created');
        exit;
    }
}
