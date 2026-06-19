<?php

class Database {
    private static $instance = null;
    public $conexion;

    private function __construct() {
        $this->conexion = new mysqli("localhost", "root", "Migue123456", "soft_kayrom");
        $this->conexion->set_charset("utf8");

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
