<?php
// SubidaImagen
// ayudante para recibir imágenes que se suben desde un formulario, de forma segura.
// Revisa que el archivo sea una imagen de verdad, la achica si es muy grande y la vuelve a guardar
// con un nombre al azar. Así nunca se usa el nombre que manda el usuario ni se guarda contenido raro.
class SubidaImagen {
    const MAX_BYTES   = 2097152;   // peso máximo permitido: 2 MB
    const LADO_MAX    = 1200;      // la imagen se achica para que su lado más largo no pase de esto (en píxeles)
    const MAX_PIXELES = 16000000;  // para no llenar la memoria con fotos gigantes (16 megapíxeles)
    // tipos permitidos (se mira el contenido real del archivo, no su extensión)
    const TIPOS = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    // ¿El formulario mandó algún archivo? (el campo existe y no vino vacío)
    public static function hayArchivo($archivo) {
        return is_array($archivo) && isset($archivo['error']) && $archivo['error'] !== UPLOAD_ERR_NO_FILE;
    }

    // Revisa y guarda la imagen subida dentro de $carpeta.
    // Devuelve ['ok' => true, 'archivo' => 'nombre.jpg'] o ['ok' => false, 'error' => 'motivo'].
    public static function guardar($archivo, $carpeta) {
        // 1) ¿La subida llegó bien?
        if (!is_array($archivo) || !isset($archivo['error']) || is_array($archivo['error'])) {
            return self::error('El archivo no es válido.');
        }
        switch ($archivo['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return self::error('La imagen pesa demasiado. El máximo es 2 MB.');
            case UPLOAD_ERR_PARTIAL:
                return self::error('La imagen no llegó completa. Probá de nuevo.');
            default:
                return self::error('No se pudo recibir la imagen. Probá de nuevo.');
        }
        $tmp = $archivo['tmp_name'] ?? '';
        if (!is_uploaded_file($tmp)) {
            return self::error('El archivo no es válido.');
        }

        // 2) Peso
        if (filesize($tmp) > self::MAX_BYTES) {
            return self::error('La imagen pesa demasiado. El máximo es 2 MB.');
        }

        // 3) Tipo real: se mira el contenido del archivo, no la extensión ni lo que dice el navegador
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
        if (!isset(self::TIPOS[$mime])) {
            return self::error('Formato no permitido. Usá JPG, PNG o WebP.');
        }
        $ext = self::TIPOS[$mime];

        // 4) Tiene que ser una imagen que se pueda leer, y no gigante
        $info = @getimagesize($tmp);
        if (!$info || $info[0] < 1 || $info[1] < 1) {
            return self::error('No se pudo leer la imagen. Probá con otro archivo.');
        }
        if ($info[0] * $info[1] > self::MAX_PIXELES) {
            return self::error('La imagen es demasiado grande (más de 16 megapíxeles). Probá con una más chica.');
        }

        // 5) La carpeta de destino tiene que existir (si no, se crea)
        if (!is_dir($carpeta) && !@mkdir($carpeta, 0755, true)) {
            return self::error('No se pudo guardar la imagen en el servidor.');
        }
        $nombre  = bin2hex(random_bytes(8)) . '.' . $ext;      // nombre al azar, no el del usuario
        $destino = rtrim($carpeta, '/\\') . DIRECTORY_SEPARATOR . $nombre;

        // 6) Sin GD no se puede reprocesar: se guarda tal cual, ya validada arriba
        if (!self::puedeReprocesar($ext)) {
            return @move_uploaded_file($tmp, $destino)
                ? ['ok' => true, 'archivo' => $nombre]
                : self::error('No se pudo guardar la imagen en el servidor.');
        }

        // 7) Se vuelve a dibujar la imagen: así se descarta cualquier cosa escondida dentro del archivo
        $imagen = @imagecreatefromstring(file_get_contents($tmp));
        if (!$imagen) {
            return self::error('No se pudo leer la imagen. Probá con otro archivo.');
        }

        // Fotos de celular: se respeta la orientación que guardó la cámara
        if ($ext === 'jpg' && function_exists('exif_read_data')) {
            $exif  = @exif_read_data($tmp);
            $giro  = [3 => 180, 6 => -90, 8 => 90][$exif['Orientation'] ?? 1] ?? 0;
            if ($giro) {
                $girada = imagerotate($imagen, $giro, 0);
                if ($girada) { imagedestroy($imagen); $imagen = $girada; }
            }
        }

        // Si es muy grande, se achica (sin deformarla)
        $ancho = imagesx($imagen);
        $alto  = imagesy($imagen);
        $mayor = max($ancho, $alto);
        if ($mayor > self::LADO_MAX) {
            $k = self::LADO_MAX / $mayor;
            $chica = imagescale($imagen, max(1, (int)round($ancho * $k)), max(1, (int)round($alto * $k)), IMG_BICUBIC);
            if ($chica) { imagedestroy($imagen); $imagen = $chica; }
        }

        // PNG y WebP conservan la transparencia
        imagealphablending($imagen, false);
        imagesavealpha($imagen, true);

        if ($ext === 'jpg')       { $guardada = imagejpeg($imagen, $destino, 85); }
        elseif ($ext === 'png')   { $guardada = imagepng($imagen, $destino, 6); }
        else                      { $guardada = imagewebp($imagen, $destino, 85); }
        imagedestroy($imagen);

        if (!$guardada || !is_file($destino)) {
            return self::error('No se pudo guardar la imagen en el servidor.');
        }
        @chmod($destino, 0644);
        return ['ok' => true, 'archivo' => $nombre];
    }

    // Borra una imagen guardada antes. Solo borra archivos con el nombre que genera este ayudante.
    public static function borrar($nombre, $carpeta) {
        if (!self::nombreValido($nombre)) {
            return;
        }
        $ruta = rtrim($carpeta, '/\\') . DIRECTORY_SEPARATOR . $nombre;
        if (is_file($ruta)) {
            @unlink($ruta);
        }
    }

    // Devuelve la dirección web de la imagen, o null si no tiene o si el archivo ya no está en el disco
    public static function url($nombre, $carpeta, $urlBase) {
        if (!self::nombreValido($nombre)) {
            return null;
        }
        return is_file(rtrim($carpeta, '/\\') . DIRECTORY_SEPARATOR . $nombre) ? $urlBase . $nombre : null;
    }

    // El nombre tiene que ser 16 letras/números al azar + extensión permitida (así no se puede apuntar a otro archivo)
    private static function nombreValido($nombre) {
        return is_string($nombre) && preg_match('/^[a-f0-9]{16}\.(jpg|png|webp)$/', $nombre) === 1;
    }

    // ¿Este servidor puede volver a dibujar imágenes de este tipo? (necesita la extensión GD)
    private static function puedeReprocesar($ext) {
        if (!function_exists('imagecreatefromstring')) return false;
        if ($ext === 'webp' && !function_exists('imagewebp')) return false;
        return true;
    }

    private static function error($mensaje) {
        return ['ok' => false, 'error' => $mensaje];
    }
}
