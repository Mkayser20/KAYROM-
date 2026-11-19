<?php
require_once __DIR__ . '/../../auditoria/AuditoriaModel.php';
require_once __DIR__ . '/../../../compartidoCREO/models/SubidaImagen.php';

// Controlador del módulo de repuestos - maneja CRUD de repuestos; los cambios de stock pasan por el StockService
class RepuestoController {
    // dónde se guardan las fotos de los repuestos (carpeta en el disco y dirección web)
    const CARPETA_IMAGENES = __DIR__ . '/../../../public/uploads/repuestos';
    const URL_IMAGENES     = 'public/uploads/repuestos/';

    private $model;            // modelo de repuestos
    private $stock;            // el "encargado del stock" (cambia el stock y anota el movimiento)
    private $auditoria;        // modelo para el sistema de auditoría

    public function __construct() {
        $this->model = new RepuestoModel();
        $this->stock = new StockService();
        $this->auditoria = new AuditoriaModel();
    }

    // mostrar lista de todos los repuestos
    public function index() {
        $proveedorModel = new ProveedorModel();
        $data = [
            'repuestos'  => $this->conImagen($this->model->getAll()), //obtener todos los repuestos (con la dirección de su imagen)
            'categorias' => $this->model->getCategorias(), //categorías reales, para el filtro
            'proveedores'=> $proveedorModel->getAll(),    //para el select de proveedor en el alta rápida
            'activePage' => 'repuestos'
        ];
        require_once 'backend/repuestos/views/repuestos.php';
    }

    // crear un nuevo repuesto
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->prepararCategoria();
            $error = $this->model->validarDatos($_POST);
            if ($error !== null) {
                if (!empty($_POST['ajax'])) {
                    http_response_code(422);
                    echo $error;
                    exit;
                }
                $data = [
                    'proveedores' => (new ProveedorModel())->getAll(),
                    'activePage' => 'repuestos',
                    'error' => $error,
                    'formData' => $_POST,
                ];
                require_once 'backend/repuestos/views/repuesto_form.php';
                return;
            }

            // Primero reviso la imagen: si no sirve, no se crea nada
            $img = $this->recibirImagen();
            if (!$img['ok']) $this->errorImagen($img['error']);

            // El stock inicial NO se guarda directo: se crea el repuesto en 0 y el StockService
            // suma el stock inicial, así queda el movimiento anotado de forma real.
            $stockInicial   = max(0, (int)($_POST['stock'] ?? 0));
            $_POST['stock'] = 0;
            $nombreRepuesto = trim($_POST['nombre'] ?? '');

            // Alta + imagen + stock inicial, todo junto: si algo falla no queda un repuesto a medias
            $db = Database::getInstance()->getConexion();
            Tx::iniciar($db);
            try {
                $nuevoId = $this->model->create($_POST);
                if (!$nuevoId) throw new Exception('No se pudo crear el repuesto.');

                if ($img['archivo']) {
                    $this->model->setImagen($nuevoId, $img['archivo']);
                }
                if ($stockInicial > 0) {
                    $res = $this->stock->entrada($nuevoId, $stockInicial, 'Alta', null, 'Alta de repuesto: ' . $nombreRepuesto);
                    if (!$res['ok']) throw new Exception($res['error']);
                }
                if (!Tx::terminar($db, true)) throw new Exception('No se pudo crear el repuesto.');
            } catch (Throwable $e) {
                Tx::terminar($db, false);
                // si no se pudo crear, se descarta la imagen que ya se había guardado
                SubidaImagen::borrar($img['archivo'], self::CARPETA_IMAGENES);
                error_log('[KAYROM Repuesto] ' . $e->getMessage());
                $this->responderError('No se pudo crear el repuesto. Revisá los datos e intentá de nuevo.');
            }

            // AUDITORÍA: Registro de creación
            $this->auditoria->registrar(
                'CREAR',
                'Repuestos',
                "Alta de repuesto: $nombreRepuesto (Stock inicial: $stockInicial)",
                $nuevoId
            );

            // redirigir con mensaje de éxito
            header('Location: index.php?page=repuestos&msg=created');
            exit;
        }
        //mostrar formulario vacío
        $data = [
            'proveedores' => (new ProveedorModel())->getAll(),
            'activePage'  => 'repuestos'
        ];
        require_once 'backend/repuestos/views/repuesto_form.php';
    }

    // editar un repuesto existente
    public function edit() {
        $id = (int)($_GET['id'] ?? 0); // siempre número

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->prepararCategoria();
            $error = $this->model->validarDatos($_POST);
            if ($error !== null) {
                if (!empty($_POST['ajax'])) {
                    http_response_code(422);
                    echo $error;
                    exit;
                }
                $repuesto = $this->model->getById($id);
                $data = [
                    'repuesto' => $repuesto ? $this->conImagen([$repuesto])[0] : null,
                    'proveedores' => (new ProveedorModel())->getAll(),
                    'activePage' => 'repuestos',
                    'error' => $error,
                    'formData' => $_POST,
                ];
                require_once 'backend/repuestos/views/repuesto_form.php';
                return;
            }

            // El stock tampoco se cambia directo: si el usuario cambió el número,
            // el StockService lo registra como "Ajuste manual" en Movimientos.
            $anterior   = $this->model->getById($id);
            if (!$anterior) {
                $this->responderError('El repuesto no existe.');
            }

            // Primero reviso la imagen nueva (si mandó una): si no sirve, no se cambia nada
            $img = $this->recibirImagen();
            if (!$img['ok']) $this->errorImagen($img['error'], $anterior ? $this->conImagen([$anterior])[0] : null);

            $nuevoStock = max(0, (int)($_POST['stock'] ?? 0));
            $_POST['stock'] = $anterior['stock'] ?? 0; // el update de datos no toca el stock

            // Datos + ajuste de stock juntos: antes eran dos pasos separados y uno podía fallar sin el otro
            $db = Database::getInstance()->getConexion();
            Tx::iniciar($db);
            try {
                if (!$this->model->update($id, $_POST)) throw new Exception('No se pudo actualizar el repuesto.');
                $res = $this->stock->ajustarA($id, $nuevoStock, 'Ajuste manual', null, 'Ajuste manual desde la edición del repuesto');
                if (!$res['ok']) throw new Exception($res['error']);
                if (!Tx::terminar($db, true)) throw new Exception('No se pudo actualizar el repuesto.');
            } catch (Throwable $e) {
                Tx::terminar($db, false);
                SubidaImagen::borrar($img['archivo'], self::CARPETA_IMAGENES);
                error_log('[KAYROM Repuesto] ' . $e->getMessage());
                $this->responderError('No se pudo guardar el repuesto. Revisá los datos e intentá de nuevo.');
            }

            // Imagen: una nueva reemplaza a la anterior; "quitar" la borra
            $cambioImagen = false;
            if ($img['archivo']) {
                $this->model->setImagen($id, $img['archivo']);
                SubidaImagen::borrar($anterior['imagen'] ?? null, self::CARPETA_IMAGENES);
                $cambioImagen = true;
            } elseif (($_POST['quitar_imagen'] ?? '') === '1' && !empty($anterior['imagen']) && $this->model->tieneColumnaImagen()) {
                $this->model->setImagen($id, null);
                SubidaImagen::borrar($anterior['imagen'], self::CARPETA_IMAGENES);
                $cambioImagen = true;
            }

            // AUDITORÍA: Registro de modificación
            $nombreRepuesto = trim($_POST['nombre'] ?? '');
            $this->auditoria->registrar(
                'EDITAR',
                'Repuestos',
                "Modificación de repuesto ID $id: $nombreRepuesto" . ($cambioImagen ? ' (imagen modificada)' : ''),
                $id
            );

            // redirigir con mensaje de éxito
            header('Location: index.php?page=repuestos&msg=updated');
            exit;
        }

        // mostrar formulario con datos del repuesto
        $repuesto = $this->model->getById($id);
        $data = [
            'repuesto'    => $repuesto ? $this->conImagen([$repuesto])[0] : null,
            'proveedores' => (new ProveedorModel())->getAll(),
            'activePage'  => 'repuestos'
        ];
        require_once 'backend/repuestos/views/repuesto_form.php';
    }

    // eliminar un repuesto
    public function delete() {
        $id = (int)($_POST['id'] ?? 0);

        // Obtenemos los datos antes de eliminar (nombre para la auditoría, stock para el movimiento)
        $repuesto = $this->model->getById($id);
        if (!$repuesto) {
            header('Location: index.php?page=repuestos&msg=error');
            exit;
        }
        $nombreRepuesto = $repuesto['nombre'];
        $stockActual    = (int)$repuesto['stock'];

        $referencias = $this->model->getReferencias($id);
        if ((int)$referencias['ordenes'] > 0
            || (int)$referencias['pedidos'] > 0
            || (int)$referencias['carritos'] > 0) {
            header('Location: index.php?page=repuestos&msg=repuesto_en_uso');
            exit;
        }

        // Todo junto: si no se puede eliminar, tampoco se anota el movimiento
        $db = Database::getInstance()->getConexion();
        Tx::iniciar($db);
        try {
            // Si tenía unidades en stock, esas unidades "salen" del inventario: queda anotado en Movimientos
            if ($stockActual > 0) {
                $res = $this->stock->salida($id, $stockActual, 'Baja de repuesto', $id,
                    "Baja de repuesto: $nombreRepuesto ($stockActual unidad/es dadas de baja)");
                if (!$res['ok']) throw new Exception($res['error']);
            }
            if (!$this->model->delete($id)) throw new Exception('No se pudo eliminar el repuesto.');
            Tx::terminar($db, true);
        } catch (Throwable $e) {
            Tx::terminar($db, false);
            error_log("No se pudo eliminar el repuesto #$id: " . $e->getMessage());
            header('Location: index.php?page=repuestos&msg=error');
            exit;
        }

        // Ya no existe el repuesto: se borra también el archivo de su imagen
        SubidaImagen::borrar($repuesto['imagen'] ?? null, self::CARPETA_IMAGENES);

        // AUDITORÍA: Registro de eliminación
        $this->auditoria->registrar('ELIMINAR', 'Repuestos', "Baja de repuesto: $nombreRepuesto", $id);

        header('Location: index.php?page=repuestos&msg=deleted');
        exit;
    }

    private function prepararCategoria() {
        $categorias = ['Frenos', 'Filtros', 'Motor', 'Suspensión', 'Eléctrico', 'Lubricantes', 'Refrigeración', 'Carrocería', 'Transmisión'];
        $categoria = $_POST['categoria'] ?? '';

        if ($categoria === '__otro__') {
            $categoria = $_POST['categoria_otro'] ?? '';
        } elseif (!is_string($categoria) || !in_array($categoria, $categorias, true)) {
            $categoria = '';
        }

        $_POST['categoria'] = is_string($categoria) ? trim($categoria) : '';
    }

    // marcar / desmarcar un repuesto como favorito (la estrella de las acciones)
    // Desde la pantalla se llama con JavaScript (fetch) y responde un JSON, así no se recarga la página.
    public function favorito() {
        $id = (int)($_GET['id'] ?? 0);
        $esAjax = !empty($_GET['ajax']);

        // sin la columna en la tabla no hay dónde guardar la marca
        if (!$this->model->tieneColumnaFavorito()) {
            $this->responderFavorito($esAjax, false, null, 'Falta ejecutar agregar_favorito_repuestos.sql en la base de datos.');
        }

        $nuevoEstado = $this->model->toggleFavorito($id);
        if ($nuevoEstado === null) {
            $this->responderFavorito($esAjax, false, null, 'El repuesto no existe.');
        }

        // AUDITORÍA: queda anotado quién marcó o desmarcó el favorito
        $repuesto = $this->model->getById($id);
        $this->auditoria->registrar(
            'EDITAR',
            'Repuestos',
            ($nuevoEstado ? 'Marcó como favorito' : 'Quitó de favoritos') . ' el repuesto: ' . $repuesto['nombre'],
            $id
        );

        $this->responderFavorito($esAjax, true, $nuevoEstado);
    }

    // Respuesta de la acción favorito: JSON si vino de JavaScript, o vuelta al listado si fue un clic normal
    private function responderFavorito($esAjax, $ok, $favorito, $error = '') {
        if ($esAjax) {
            header('Content-Type: application/json; charset=utf-8');
            if (!$ok) http_response_code(422);
            echo json_encode(['ok' => $ok, 'favorito' => $favorito, 'error' => $error], JSON_UNESCAPED_UNICODE);
            exit;
        }
        header('Location: index.php?page=repuestos' . ($ok ? '' : '&msg=error'));
        exit;
    }

    // Revisa la imagen que mandó el formulario (si mandó alguna) y la guarda.
    // Devuelve ['ok' => true, 'archivo' => 'nombre.jpg' (o null si no mandó)] o ['ok' => false, 'error' => 'motivo']
    private function recibirImagen() {
        if (!SubidaImagen::hayArchivo($_FILES['imagen'] ?? null)) {
            return ['ok' => true, 'archivo' => null];
        }
        // sin la columna "imagen" en la tabla no hay dónde anotar el archivo
        if (!$this->model->tieneColumnaImagen()) {
            return ['ok' => false, 'error' => 'Falta ejecutar migracion_imagen_repuestos.sql en la base de datos.'];
        }
        return SubidaImagen::guardar($_FILES['imagen'], self::CARPETA_IMAGENES);
    }

    // Si la imagen no sirvió: desde el modal devuelvo solo el motivo (así se muestra sin salir de la pantalla);
    // desde el formulario de página completa, vuelvo a mostrar el formulario con el error.
    private function errorImagen($mensaje, $repuesto = null) {
        if (!empty($_POST['ajax'])) {
            http_response_code(422);
            echo $mensaje;
            exit;
        }
        $data = [
            'repuesto'    => $repuesto,
            'proveedores' => (new ProveedorModel())->getAll(),
            'error'       => $mensaje,
            'activePage'  => 'repuestos'
        ];
        require_once 'backend/repuestos/views/repuesto_form.php';
        exit;
    }

    // Error al guardar: desde el modal devuelvo el motivo en texto; si no, vuelvo al listado con aviso
    private function responderError($mensaje) {
        if (!empty($_POST['ajax'])) {
            http_response_code(422);
            echo $mensaje;
            exit;
        }
        header('Location: index.php?page=repuestos&msg=error');
        exit;
    }

    // Agrega a cada repuesto la dirección web de su imagen (null si no tiene o si el archivo ya no está)
    private function conImagen($repuestos) {
        foreach ($repuestos as $i => $r) {
            $repuestos[$i]['imagen_url'] = SubidaImagen::url($r['imagen'] ?? null, self::CARPETA_IMAGENES, self::URL_IMAGENES);
        }
        return $repuestos;
    }
}