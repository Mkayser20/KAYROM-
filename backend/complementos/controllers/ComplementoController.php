<?php
// Controlador de Complementos: pantalla de configuración con los ABM de catálogos
// (por ahora Modelos y Tipos de vehículo; a futuro se pueden sumar más acá)
class ComplementoController {
    private $model;

    public function __construct() {
        $this->model = new ComplementoModel();
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
        $this->volver($resultado);
    }

    public function editarModelo() {
        $resultado = $this->model->editarModelo($_POST['id'] ?? 0, $_POST['nombre'] ?? '');
        $this->volver($resultado);
    }

    public function eliminarModelo() {
        $resultado = $this->model->eliminarModelo($_GET['id'] ?? 0);
        $this->volver($resultado);
    }

    // ---------- acciones de Tipos ----------

    public function crearTipo() {
        $resultado = $this->model->crearTipo($_POST['nombre'] ?? '');
        $this->volver($resultado);
    }

    public function editarTipo() {
        $resultado = $this->model->editarTipo($_POST['id'] ?? 0, $_POST['nombre'] ?? '');
        $this->volver($resultado);
    }

    public function eliminarTipo() {
        $resultado = $this->model->eliminarTipo($_GET['id'] ?? 0);
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
