<?php
class AuditoriaModel
{
    private $db;

    public function __construct()
    {
        require_once __DIR__ . '/../../config/base_datos.php';

        $this->db = Database::getInstance()->getConexion();
        // la sesión ya la abre index.php: un modelo no debería abrirla
    }

    // Anota una acción en la auditoría.
    // Si por algún motivo no se puede anotar, NO corta la operación que el usuario ya hizo:
    // antes hacía die("DIAGNÓSTICO...") y mostraba errores internos en pantalla.
    public function registrar(
        $accion,
        $modulo,
        $descripcion = "",
        $registro_id = null,
        $usuario_id = null
    ) {
        if ($usuario_id === null) {
            $usuario_id = isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : null;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $descripcion = mb_substr((string)$descripcion, 0, 255); // la columna admite 255 caracteres
        $registro_id = $registro_id !== null && $registro_id !== false ? (int)$registro_id : null;

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO auditorias (usuario_id, accion, modulo, descripcion, registro_id, ip) VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("isssis", $usuario_id, $accion, $modulo, $descripcion, $registro_id, $ip);
            $stmt->execute();
            $stmt->close();
            return true;
        } catch (Throwable $e) {
            error_log('[KAYROM Auditoría] ' . $e->getMessage());
            return false;
        }
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
