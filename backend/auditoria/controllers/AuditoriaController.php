<?php
class AuditoriaController {
    private $model;

    public function __construct() {
        $this->model = new AuditoriaModel();
    }

    public function index() {
        $data = [
            'auditorias' => $this->model->getAll(),
            'activePage' => 'auditorias'
        ];

        require_once 'backend/auditoria/views/auditorias.php';
    }
}
