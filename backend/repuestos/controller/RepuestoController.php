<?php
require_once __DIR__ . '/../../auditoria/AuditoriaModel.php';
require_once __DIR__ . '/../../../compartidoCREO/models/SubidaImagen.php';

// Controlador del módulo de repuestos - maneja CRUD de repuestos; los cambios de stock pasan por el StockService
class RepuestoController {
    // dónde se guardan las fotos de los repuestos (carpeta en el disco y dirección web)
    const CARPETA_IMAGENES = __DIR__ . '/../../../public/uploads/repuestos';
    const URL_IMAGENES     = 'public/uploads/repuestos/';

    private $model;           // modelo de repuestos
    private $stock;           // el "encargado del stock" (cambia el stock y anota el movimiento)
    private $auditoria;       // modelo para el sistema de auditoría

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
            // Primero reviso la imagen: si no sirve, no se crea nada
            $img = $this->recibirImagen();
            if (!$img['ok']) $this->errorImagen($img['error']);

            // El stock inicial NO se guarda directo: se crea el repuesto en 0 y el StockService
            // suma el stock inicial, así queda el movimiento anotado de forma real.
            $stockInicial   = max(0, (int)($_POST['stock'] ?? 0));
            $_POST['stock'] = 0;
            $nuevoId = $this->model->create($_POST);

            $nombreRepuesto = trim($_POST['nombre'] ?? '');

            // Si subió una imagen, se guarda su nombre en el repuesto (si no se pudo crear, se descarta el archivo)
            if ($nuevoId && $img['archivo']) {
                $this->model->setImagen($nuevoId, $img['archivo']);
            } elseif ($img['archivo']) {
                SubidaImagen::borrar($img['archivo'], self::CARPETA_IMAGENES);
            }

            if ($nuevoId && $stockInicial > 0) {
                $this->stock->entrada($nuevoId, $stockInicial, 'Alta', null, 'Alta de repuesto: ' . $nombreRepuesto);
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
        $id = $_GET['id'] ?? 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // El stock tampoco se cambia directo: si el usuario cambió el número,
            // el StockService lo registra como "Ajuste manual" en Movimientos.
            $anterior   = $this->model->getById($id);

            // Primero reviso la imagen nueva (si mandó una): si no sirve, no se cambia nada
            $img = $this->recibirImagen();
            if (!$img['ok']) $this->errorImagen($img['error'], $anterior ? $this->conImagen([$anterior])[0] : null);

            $nuevoStock = max(0, (int)($_POST['stock'] ?? 0));
            $_POST['stock'] = $anterior['stock'] ?? 0; // el update de datos no toca el stock

            $this->model->update($id, $_POST);
            $this->stock->ajustarA($id, $nuevoStock, 'Ajuste manual', null, 'Ajuste manual desde la edición del repuesto');

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
        $id = (int)($_GET['id'] ?? 0);

        // Obtenemos los datos antes de eliminar (nombre para la auditoría, stock para el movimiento)
        $repuesto = $this->model->getById($id);
        if (!$repuesto) {
            header('Location: index.php?page=repuestos&msg=error');
            exit;
        }
        $nombreRepuesto = $repuesto['nombre'];
        $stockActual    = (int)$repuesto['stock'];

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
            // Típico: el repuesto ya se usó en una orden de trabajo o está en un pedido
            Tx::terminar($db, false);
            header('Location: index.php?page=repuestos&msg=repuesto_en_uso');
            exit;
        }

        // Ya no existe el repuesto: se borra también el archivo de su imagen
        SubidaImagen::borrar($repuesto['imagen'] ?? null, self::CARPETA_IMAGENES);

        // AUDITORÍA: Registro de eliminación
        $this->auditoria->registrar('ELIMINAR', 'Repuestos', "Baja de repuesto: $nombreRepuesto", $id);

        header('Location: index.php?page=repuestos&msg=deleted');
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

    // Agrega a cada repuesto la dirección web de su imagen (null si no tiene o si el archivo ya no está)
    private function conImagen($repuestos) {
        foreach ($repuestos as $i => $r) {
            $repuestos[$i]['imagen_url'] = SubidaImagen::url($r['imagen'] ?? null, self::CARPETA_IMAGENES, self::URL_IMAGENES);
        }
        return $repuestos;
    }
}