<?php
require_once __DIR__ . '/../../auditoria/AuditoriaModel.php';

// Controlador de Complementos: pantalla de configuración con los ABM de catálogos
// (por ahora Modelos y Tipos de vehículo; a futuro se pueden sumar más acá)
class ComplementoController {
    private $model;
    private $auditoria;

    public function __construct() {
        $this->model = new ComplementoModel();
        $this->auditoria = new AuditoriaModel();
    }

    // Muestra la pantalla con las dos listas (Modelos y Tipos)
    public function index() {
        $data = [
            'modelos'    => $this->model->getModelos(),
            'tipos'      => $this->model->getTipos(),
            'activePage' => 'complementos'
        ];
        require_once 'backend/complementos/views/complementos.php';
    }

    // ---------- acciones de Modelos ----------

    public function crearModelo() {
        $resultado = $this->model->crearModelo($_POST['nombre'] ?? '');

        if (!isset($resultado['error'])) {
            $nombre = trim($_POST['nombre'] ?? '');
            $this->auditoria->registrar(
                'CREAR',
                'Complementos',
                "Alta de modelo de vehículo: $nombre"
            );
        }

        $this->volver($resultado);
    }

    public function editarModelo() {
        $resultado = $this->model->editarModelo($_POST['id'] ?? 0, $_POST['nombre'] ?? '');

        if (!isset($resultado['error'])) {
            $id = (int)($_POST['id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            $this->auditoria->registrar(
                'EDITAR',
                'Complementos',
                "Modificación de modelo de vehículo ID $id: $nombre",
                $id
            );
        }

        $this->volver($resultado);
    }

    public function eliminarModelo() {
        $id = (int)($_GET['id'] ?? 0);
        $resultado = $this->model->eliminarModelo($id);

        if (!isset($resultado['error'])) {
            $this->auditoria->registrar(
                'ELIMINAR',
                'Complementos',
                "Baja de modelo de vehículo ID $id",
                $id
            );
        }

        $this->volver($resultado);
    }

    // ---------- acciones de Tipos ----------

    public function crearTipo() {
        $resultado = $this->model->crearTipo($_POST['nombre'] ?? '');

        if (!isset($resultado['error'])) {
            $nombre = trim($_POST['nombre'] ?? '');
            $this->auditoria->registrar(
                'CREAR',
                'Complementos',
                "Alta de tipo de vehículo: $nombre"
            );
        }

        $this->volver($resultado);
    }

    public function editarTipo() {
        $resultado = $this->model->editarTipo($_POST['id'] ?? 0, $_POST['nombre'] ?? '');

        if (!isset($resultado['error'])) {
            $id = (int)($_POST['id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            $this->auditoria->registrar(
                'EDITAR',
                'Complementos',
                "Modificación de tipo de vehículo ID $id: $nombre",
                $id
            );
        }

        $this->volver($resultado);
    }

    public function eliminarTipo() {
        $id = (int)($_GET['id'] ?? 0);
        $resultado = $this->model->eliminarTipo($id);

        if (!isset($resultado['error'])) {
            $this->auditoria->registrar(
                'ELIMINAR',
                'Complementos',
                "Baja de tipo de vehículo ID $id",
                $id
            );
        }

        $this->volver($resultado);
    }

    // Redirige de vuelta a Complementos, mostrando error si algo salió mal
    private function volver($resultado) {
        if (isset($resultado['error'])) {
            header('Location: index.php?page=complementos&error=' . urlencode($resultado['error']));
        } else {
            header('Location: index.php?page=complementos&msg=updated');
        }
        exit;
    }
}
