<?php
class AuditoriaModel
{
    private $db;

    public function __construct()
    {
       require_once __DIR__ . '/../../config/base_datos.php';
        
        $this->db = Database::getInstance()->getConexion();

         if (session_status() === PHP_SESSION_NONE) {
             session_start();
        }
    }

    public function registrar(
        $accion,
        $modulo,
        $descripcion = "",
        $registro_id = null,
        $usuario_id = null
    ) {
        if ($usuario_id === null) {
            $usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? null;

        $sql = "INSERT INTO auditorias (usuario_id, accion, modulo, descripcion, registro_id, ip) VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);

        // 2. Verificar si la consulta se prepara bien
        if (!$stmt) {
            die("DIAGNÓSTICO ERROR PREPARE: " . $this->db->error);
        }

        $stmt->bind_param(
            "isssis",
            $usuario_id,
            $accion,
            $modulo,
            $descripcion,
            $registro_id,
            $ip
        );

        // 3. Verificar si falla la ejecución
        if (!$stmt->execute()) {
            die("DIAGNÓSTICO ERROR EXECUTE: " . $stmt->error);
        }

        $stmt->close();
         return true;
    }

    public function getAll() {
        $sql = "SELECT a.*, u.nombre_usuario
                FROM auditorias a
                LEFT JOIN usuario u ON u.id = a.usuario_id
                ORDER BY a.fecha DESC, a.id DESC";

        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}