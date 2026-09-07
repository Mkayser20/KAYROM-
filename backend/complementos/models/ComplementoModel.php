<?php
// Modelo de Complementos: acá se administran los catálogos que alimentan los selects
// de Vehículos (Modelos y Tipos), para no tener que escribirlos a mano cada vez.
class ComplementoModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConexion();
    }

    // ---------- MODELOS DE VEHÍCULO ----------

    // Trae todos los modelos, y de paso cuántos vehículos tiene cargados cada uno
    public function getModelos() {
        $sql = "SELECT m.*, COUNT(v.id) AS cantidad_vehiculos
                FROM modelo_vehiculo m
                LEFT JOIN vehiculo v ON v.modelo_vehiculo_id = m.id
                GROUP BY m.id
                ORDER BY m.modelo_vehiculo ASC";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Crea un modelo nuevo (ej: "Toyota Hilux"). El año de catálogo ya no se usa,
    // pero la columna sigue siendo parte de la tabla, así que mando 0 de relleno.
    public function crearModelo($nombre) {
        $nombre = trim($this->db->real_escape_string($nombre));
        if ($nombre === '') {
            return ['error' => 'El nombre del modelo es requerido'];
        }
        $existe = $this->db->query("SELECT id FROM modelo_vehiculo WHERE modelo_vehiculo = '$nombre'")->fetch_assoc();
        if ($existe) {
            return ['error' => 'Ya existe un modelo con ese nombre'];
        }
        $ok = $this->db->query("INSERT INTO modelo_vehiculo (modelo_vehiculo, anio_vehiculo) VALUES ('$nombre', 0)");
        return $ok ? ['success' => true] : ['error' => 'No se pudo crear el modelo'];
    }

    // Edita el nombre de un modelo existente
    public function editarModelo($id, $nombre) {
        $id = (int)$id;
        $nombre = trim($this->db->real_escape_string($nombre));
        if ($nombre === '') {
            return ['error' => 'El nombre del modelo es requerido'];
        }
        $ok = $this->db->query("UPDATE modelo_vehiculo SET modelo_vehiculo='$nombre' WHERE id=$id");
        return $ok ? ['success' => true] : ['error' => 'No se pudo actualizar el modelo'];
    }

    // Elimina un modelo, siempre que ningún vehículo lo esté usando
    public function eliminarModelo($id) {
        $id = (int)$id;
        $enUso = $this->db->query("SELECT COUNT(*) AS c FROM vehiculo WHERE modelo_vehiculo_id=$id")->fetch_assoc();
        if ($enUso['c'] > 0) {
            return ['error' => 'No se puede eliminar: hay ' . $enUso['c'] . ' vehículo(s) usando este modelo'];
        }
        $ok = $this->db->query("DELETE FROM modelo_vehiculo WHERE id=$id");
        return $ok ? ['success' => true] : ['error' => 'No se pudo eliminar el modelo'];
    }

    // ---------- TIPOS DE VEHÍCULO ----------

    // Trae todos los tipos, y cuántos vehículos hay de cada tipo
    public function getTipos() {
        $sql = "SELECT t.*, COUNT(v.id) AS cantidad_vehiculos
                FROM tipo_vehiculo t
                LEFT JOIN vehiculo v ON v.tipo_vehiculo_id = t.id
                GROUP BY t.id
                ORDER BY t.tipo_vehiculo ASC";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Crea un tipo nuevo (ej: "Automóvil", "Moto", "Camioneta")
    public function crearTipo($nombre) {
        $nombre = trim($this->db->real_escape_string($nombre));
        if ($nombre === '') {
            return ['error' => 'El nombre del tipo es requerido'];
        }
        $existe = $this->db->query("SELECT id FROM tipo_vehiculo WHERE tipo_vehiculo = '$nombre'")->fetch_assoc();
        if ($existe) {
            return ['error' => 'Ya existe un tipo con ese nombre'];
        }
        $ok = $this->db->query("INSERT INTO tipo_vehiculo (tipo_vehiculo) VALUES ('$nombre')");
        return $ok ? ['success' => true] : ['error' => 'No se pudo crear el tipo'];
    }

    // Edita el nombre de un tipo existente
    public function editarTipo($id, $nombre) {
        $id = (int)$id;
        $nombre = trim($this->db->real_escape_string($nombre));
        if ($nombre === '') {
            return ['error' => 'El nombre del tipo es requerido'];
        }
        $ok = $this->db->query("UPDATE tipo_vehiculo SET tipo_vehiculo='$nombre' WHERE id=$id");
        return $ok ? ['success' => true] : ['error' => 'No se pudo actualizar el tipo'];
    }

    // Elimina un tipo, siempre que ningún vehículo lo esté usando
    public function eliminarTipo($id) {
        $id = (int)$id;
        $enUso = $this->db->query("SELECT COUNT(*) AS c FROM vehiculo WHERE tipo_vehiculo_id=$id")->fetch_assoc();
        if ($enUso['c'] > 0) {
            return ['error' => 'No se puede eliminar: hay ' . $enUso['c'] . ' vehículo(s) usando este tipo'];
        }
        $ok = $this->db->query("DELETE FROM tipo_vehiculo WHERE id=$id");
        return $ok ? ['success' => true] : ['error' => 'No se pudo eliminar el tipo'];
    }
}
