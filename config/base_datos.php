<?php

require_once __DIR__ . '/../config.php';

class Database {
    private static $instance = null;
    public $conexion;

    private function __construct() {
        // Que mysqli tire excepciones cuando algo falla (así los try/catch de las transacciones funcionan)
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            // el charset se configura recién cuando ya sabemos que la conexión anduvo
            $this->conexion->set_charset(DB_CHARSET);
        } catch (mysqli_sql_exception $e) {
            // el detalle va al log; al usuario no se le muestran datos del servidor
            error_log('[KAYROM BD] ' . $e->getMessage());
            http_response_code(500);
            die('No se pudo conectar a la base de datos. Revisá que MySQL esté encendido y los datos del archivo .env.');
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
