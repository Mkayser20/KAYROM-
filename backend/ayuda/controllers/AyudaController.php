<?php
//Controlador del módulo de Ayuda
// el contenido de la ayuda está acá mismo, en arrays.
//Cada usuario ve solo la ayuda de los módulos a los que tiene acceso (el admin ve todo).
class AyudaController {

    public function index() {
        $esAdmin  = (($_SESSION['rol'] ?? '') === 'admin');
        $permisos = (new PermisoModel())->getByUsuario($_SESSION['usuario_id'] ?? 0);

        //me quedo solo con las secciones que el usuario puede usar
        $secciones = [];
        foreach ($this->secciones() as $clave => $s) {
            $permitido = $s['acceso'] === 'todos'
                      || $esAdmin
                      || ($s['acceso'] === 'permiso' && in_array($clave, $permisos));
            if ($permitido) $secciones[$clave] = $s;
        }

        //si viene ?modulo=xxx se abre directo esa sección (ayuda contextual)
        $moduloAbierto = $_GET['modulo'] ?? '';
        if (!isset($secciones[$moduloAbierto])) $moduloAbierto = '';

        $data = [
            'secciones'     => $secciones,
            'preguntas'     => $this->preguntasFrecuentes($esAdmin),
            'moduloAbierto' => $moduloAbierto,
            'esAdmin'       => $esAdmin,
            'activePage'    => 'ayuda'
        ];
        require_once 'backend/ayuda/views/ayuda.php';
    }

    //Contenido de la ayuda de cada módulo.
    //acceso: 'todos' = cualquier usuario logueado, 'permiso' = según los checkboxes del admin, 'admin' = solo admin
    private function secciones() {
        return [
            'inicio' => [
                'titulo'  => 'Inicio (Dashboard)',
                'icono'   => 'grafico',
                'acceso'  => 'todos',
                'resumen' => 'Vista general del taller: totales, alertas de stock bajo y últimos movimientos.',
                'pasos'   => [
                    'Al iniciar sesión llegás a esta pantalla. Arriba ves las tarjetas con los totales de vehículos, stock, pedidos y empleados.',
                    'La sección de alertas te muestra los repuestos con stock por debajo del mínimo.',
                    'Los gráficos muestran los vehículos por tipo y por estado en el taller, los pedidos por estado y los repuestos por categoría.',
                    'Con "Exportar PDF" o "Descargar XLS" podés bajar la información para imprimirla o trabajarla en Excel.',
                    'Arriba a la derecha ves tu nombre y tu rol. Tocalo para abrir el menú de usuario, donde está la opción "Cerrar sesión".',
                ],
                'tips'    => ['Revisá las alertas de stock bajo todos los días para pedir a tiempo.'],
            ],
            'vehiculos' => [
                'titulo'  => 'Vehículos y Órdenes de Trabajo',
                'icono'   => 'vehiculo',
                'acceso'  => 'permiso',
                'url'     => 'index.php?page=vehiculos',
                'resumen' => 'Registro de los vehículos que ingresan al taller y de los trabajos que se les hacen.',
                'pasos'   => [
                    'Para cargar un vehículo, tocá "+ Nuevo Vehículo" y completá patente, tipo, modelo, año, kilometraje y el cliente dueño.',
                    'El estado en taller indica en qué etapa está: En Diagnóstico → En Reparación → Esperando Repuestos → Listo para Entrega → Entregado.',
                    'Desde el listado podés cambiar el estado directamente, sin entrar a editar.',
                    'Para ver todo el historial de un vehículo, entrá a su ficha. Ahí ves los datos, el cliente y sus órdenes de trabajo.',
                    'Para registrar un trabajo, en la ficha tocá "+ Nueva Orden": escribí la descripción, elegí el mecánico y agregá los repuestos usados.',
                ],
                'tips'    => [
                    'Al guardar una orden, los repuestos usados se descuentan solos del stock.',
                    'Si un repuesto no alcanza, el sistema descuenta lo que hay y genera automáticamente un pedido al proveedor habitual por lo que falta.',
                    'Si el repuesto no tiene proveedor habitual, no se puede generar el pedido automático: asignale uno en Repuestos.',
                ],
            ],
            'clientes' => [
                'titulo'  => 'Clientes',
                'icono'   => 'lista',
                'acceso'  => 'permiso',
                'url'     => 'index.php?page=clientes',
                'resumen' => 'Datos de los dueños de los vehículos.',
                'pasos'   => [
                    'Tocá "+ Nuevo Cliente" y cargá nombre, apellido, DNI y teléfono.',
                    'Con los íconos de la columna Acciones podés ver la ficha, editar o eliminar un cliente.',
                    'En la ficha del cliente ves todos los vehículos que tiene registrados.',
                ],
                'tips'    => ['Cargá primero el cliente y después el vehículo, así lo podés asociar al crearlo.'],
            ],
            'repuestos' => [
                'titulo'  => 'Repuestos (Stock)',
                'icono'   => 'llave',
                'acceso'  => 'permiso',
                'url'     => 'index.php?page=repuestos',
                'resumen' => 'Inventario de repuestos con su foto, stock actual, stock mínimo, precio y proveedor.',
                'pasos'   => [
                    'Tocá "+ Nuevo Repuesto" y completá nombre, categoría, SKU (opcional), precio, stock actual, stock mínimo y proveedor habitual.',
                    'Para ponerle una foto, tocá "Elegir imagen" en el cuadro de la izquierda del formulario o arrastrá el archivo hasta ahí. Sirve JPG, PNG o WebP de hasta 2 MB; si es muy grande, el sistema la achica solo.',
                    'Para cambiar la foto, editá el repuesto y tocá "Cambiar imagen". Para sacarla, usá el tacho rojo que aparece al lado y guardá.',
                    'En la tabla, la columna Imagen muestra una miniatura de cada repuesto. Los que no tienen foto muestran "Sin imagen".',
                    'Hacé doble clic sobre una miniatura (en el celular, un toque) para verla en grande. Con las flechas pasás al repuesto anterior o siguiente, y podés acercar con la rueda del mouse o con los botones + y −. Se cierra con la X o con la tecla Esc.',
                    'Con el botón "Lista / Tarjetas", arriba a la derecha de los filtros, cambiás entre la tabla y las tarjetas con la foto grande. El sistema recuerda tu elección.',
                    'Usá el buscador y los filtros de categoría y estado (En Stock / Stock Bajo / Sin Stock) para encontrar repuestos rápido. Funcionan igual en la tabla y en las tarjetas.',
                    'Con el ícono del carrito agregás el repuesto a tu pedido a proveedores (lo ves en "Realizar pedido").',
                ],
                'tips'    => [
                    'Cuando el stock actual llega al stock mínimo, el repuesto aparece como "Stock Bajo" y en las alertas del inicio.',
                    'La foto es opcional. Si eliminás un repuesto, también se borra su foto.',
                    'Si al subir una foto aparece un aviso rojo, revisá el formato (JPG, PNG o WebP) y el peso (hasta 2 MB).',
                    'No se puede eliminar un repuesto que ya se usó en órdenes de trabajo o que está en pedidos.',
                    'Para corregir el stock a mano no lo edites: usá un Movimiento manual, así queda registrado.',
                ],
            ],
            'carrito' => [
                'titulo'  => 'Realizar pedido',
                'icono'   => 'enviar',
                'acceso'  => 'todos',
                'url'     => 'index.php?page=carrito',
                'resumen' => 'Lista de repuestos que querés pedir a los proveedores, antes de generar los pedidos.',
                'pasos'   => [
                    'Agregá repuestos desde el módulo Repuestos con el ícono del carrito.',
                    'En esta pantalla ajustá las cantidades con los botones + y −, o quitá los que no necesites.',
                    'Al confirmar, el sistema arma un pedido separado por cada proveedor y vacía la lista.',
                ],
                'tips'    => ['Si algo falla al generar los pedidos, no se crea ninguno y tu lista queda como estaba.'],
            ],
            'pedidos' => [
                'titulo'  => 'Pedidos a proveedores',
                'icono'   => 'caja',
                'acceso'  => 'permiso',
                'url'     => 'index.php?page=pedidos',
                'resumen' => 'Seguimiento de los pedidos de repuestos hechos a los proveedores.',
                'pasos'   => [
                    'Los pedidos se crean desde "Realizar pedido", con "+ Nuevo Pedido", o solos cuando una orden de trabajo necesita repuestos que faltan.',
                    'Todo pedido nuevo empieza en estado Pendiente.',
                    'Cuando llega la mercadería, tocá "Confirmar entrega" (o pasá el estado a Recibido): el stock de cada repuesto se suma automáticamente.',
                    'Si el pedido no se va a concretar, cambiá el estado a Cancelado.',
                ],
                'tips'    => [
                    'Un pedido recibido no se puede eliminar ni volver a recibir, para no duplicar stock.',
                    'Los pedidos antiguos sin renglones se pueden marcar como recibidos, pero no modifican el stock.',
                ],
            ],
            'movimientos' => [
                'titulo'  => 'Movimientos de stock',
                'icono'   => 'movimientos',
                'acceso'  => 'permiso',
                'url'     => 'index.php?page=movimientos',
                'resumen' => 'Historial de todas las entradas y salidas de stock, con quién y por qué.',
                'pasos'   => [
                    'El listado muestra cada movimiento con fecha, repuesto, tipo, cantidad, origen y usuario.',
                    'Las órdenes de trabajo y los pedidos recibidos generan movimientos solos.',
                    'Para un ajuste a mano tocá "+ Movimiento manual", elegí el repuesto, el tipo (Entrada suma stock / Salida resta stock), la cantidad y escribí el motivo.',
                ],
                'tips'    => ['Escribí siempre un motivo claro (ej: "se rompió en el taller"); sirve para controles posteriores.'],
            ],
            'proveedores' => [
                'titulo'  => 'Proveedores',
                'icono'   => 'etiqueta',
                'acceso'  => 'permiso',
                'url'     => 'index.php?page=proveedores',
                'resumen' => 'Datos de contacto de los proveedores de repuestos.',
                'pasos'   => [
                    'Tocá "+ Nuevo Proveedor" y cargá nombre, CUIT, teléfono y email.',
                    'Después asignalo como proveedor habitual en cada repuesto para que funcionen los pedidos automáticos.',
                ],
                'tips'    => [],
            ],
            'empleados' => [
                'titulo'  => 'Empleados y permisos',
                'icono'   => 'ajustes',
                'acceso'  => 'admin',
                'url'     => 'index.php?page=empleados',
                'resumen' => 'Alta de empleados y control de a qué módulos puede entrar cada uno.',
                'pasos'   => [
                    'Tocá "+ Nuevo Empleado" y completá sus datos, puesto, especialidad y usuario.',
                    'Al editar un empleado, marcá los módulos que puede usar (Vehículos, Clientes, Repuestos, Pedidos, Movimientos, Proveedores).',
                    'El empleado solo verá en el menú los módulos marcados. Inicio, Realizar pedido y Ayuda los ven todos.',
                ],
                'tips'    => ['Los administradores tienen acceso a todo, no hace falta marcarles permisos.'],
            ],
            'registrar_usuario' => [
                'titulo'  => 'Registrar usuario',
                'icono'   => 'check',
                'acceso'  => 'admin',
                'url'     => 'index.php?page=registrar_usuario&action=registrar_usuario',
                'resumen' => 'Creación de cuentas de acceso al sistema.',
                'pasos'   => [
                    'Completá los datos personales (nombre, apellido, DNI, teléfono, domicilio) y los de acceso (usuario, email y contraseña dos veces).',
                    'Elegí el rol: Administrador, Empleado u Otro, y si la cuenta queda Activa o Inactiva.',
                    'El email es importante: se usa para recuperar la contraseña.',
                ],
                'tips'    => [],
            ],
            'complementos' => [
                'titulo'  => 'Complementos',
                'icono'   => 'ajustes',
                'acceso'  => 'admin',
                'url'     => 'index.php?page=complementos',
                'resumen' => 'Listas que se usan en los formularios: tipos y modelos de vehículo.',
                'pasos'   => [
                    'Con "+ Nuevo Tipo" agregás tipos (ej: Automóvil, Moto, Camioneta).',
                    'Con "+ Nuevo Modelo" agregás modelos (ej: Toyota Hilux).',
                    'La columna "En uso" indica si algún vehículo lo está usando.',
                ],
                'tips'    => ['Los que están en uso no conviene borrarlos, porque los vehículos quedarían sin ese dato.'],
            ],
            'auditorias' => [
                'titulo'  => 'Auditoría',
                'icono'   => 'lista',
                'acceso'  => 'admin',
                'url'     => 'index.php?page=auditorias',
                'resumen' => 'Registro de quién creó, editó o eliminó cada dato del sistema.',
                'pasos'   => [
                    'Arriba ves el total de eventos, creaciones, ediciones y eliminaciones.',
                    'Esas tarjetas son filtros rápidos: tocá una y la tabla muestra solo ese tipo de acción. Para volver a ver todo, tocá la misma tarjeta otra vez o la de "Eventos".',
                    'Junto al título "Historial de movimientos" aparece qué filtro está activo y cuántos registros muestra.',
                    'La tabla muestra fecha, usuario, acción, módulo, descripción, registro afectado e IP.',
                ],
                'tips'    => ['Usala para controlar cambios inesperados en el stock o en los datos.'],
            ],
        ];
    }

    private function preguntasFrecuentes($esAdmin) {
        $preguntas = [
            ['¿Olvidé mi contraseña, qué hago?',
             'En la pantalla de inicio de sesión tocá "Olvidé mi contraseña", escribí tu email y te llegará un enlace para crear una nueva.'],
            ['¿Por qué no veo algún módulo en el menú?',
             'Cada empleado ve solo los módulos que el administrador le habilitó. Si necesitás otro, pedíselo al administrador.'],
            ['¿Cómo se actualiza el stock?',
             'Solo: baja al guardar una orden de trabajo y sube al recibir un pedido. Para correcciones usá un Movimiento manual.'],
            ['¿Qué pasa si no hay stock de un repuesto que necesito en una orden?',
             'La orden se guarda igual, se descuenta lo que hay y se genera un pedido automático al proveedor habitual por lo que falta.'],
            ['¿Por qué no puedo eliminar un repuesto o un pedido?',
             'Un repuesto usado en órdenes o pedidos, o un pedido ya recibido, no se pueden borrar para no perder el historial ni descuadrar el stock.'],
            ['¿Cómo cierro sesión?',
             'Arriba a la derecha tocá tu nombre para abrir el menú de usuario y elegí "Cerrar sesión". El sistema te pide confirmar antes de salir.'],
            ['¿Puedo ponerle una foto a un repuesto?',
             'Sí. Al crear o editar un repuesto tocá "Elegir imagen" (o arrastrá la foto al cuadro). Sirve JPG, PNG o WebP de hasta 2 MB. Después la ves como miniatura en la tabla y, con doble clic, en grande.'],
            ['¿Qué significan los avisos que aparecen arriba?',
             'Confirman lo que acabás de hacer: verde significa que salió bien, rojo que hubo un error (el mensaje explica el motivo), amarillo es una advertencia y azul es información. Desaparecen solos a los pocos segundos. Antes de acciones importantes, como eliminar o recibir un pedido, el sistema te pide confirmar en una ventana.'],
            ['Apareció "No se pudo completar la operación", ¿se guardó algo?',
             'No. Cuando aparece ese mensaje el sistema deshace todo; podés intentar de nuevo sin riesgo de datos a medias.'],
        ];
        if ($esAdmin) {
            $preguntas[] = ['¿Cómo le doy acceso a un empleado a un módulo?',
                            'En Empleados, editá al empleado y marcá los módulos que puede usar. Los cambios se ven la próxima vez que cargue una página.'];
        }
        return $preguntas;
    }
}
