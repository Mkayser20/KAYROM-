<?php

require_once __DIR__ . '/../config.php';

class Database {
    private static $instance = null;
    public $conexion;

    private function __construct() {
        $this->conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $this->conexion->set_charset(DB_CHARSET);

        if ($this->conexion->connect_error) {
            die("Error de conexión: " . $this->conexion->connect_error);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConexion() {
        return $this->conexion;
    }
}
