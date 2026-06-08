<?php
//Controlador de la página de inicio/dashboard
//Reúne estadísticas de todos los módulos del sistema
class InicioController {
    private $vehiculoModel;  //para obtener datos de vehículos
    private $productoModel;  //para obtener datos de productos
    private $pedidoModel;    //para obtener datos de pedidos
    private $empleadoModel;  //para obtener datos de empleados

    public function __construct() {
        $this->vehiculoModel = new VehiculoModel();
        $this->productoModel = new ProductoModel();
        $this->pedidoModel   = new PedidoModel();
        $this->empleadoModel = new EmpleadoModel();
    }

    //mostrar dashboard con estadísticas generales
    public function index() {
        //reunir datos de todos los módulos para mostrar en el dashboard
        $data = [
            'totalVehiculos'    => $this->vehiculoModel->getTotal(),      //total de vehículos registrados
            'totalStock'        => $this->productoModel->getTotalStock(), //cantidad total de productos en stock
            'stockBajo'         => $this->productoModel->getLowStockCount(6), //cantidad de productos con stock bajo
            'totalPedidos'      => $this->pedidoModel->getTotalPedidos(),      //total de pedidos registrados
            'totalEmpleados'    => $this->empleadoModel->getTotal(),           //total de empleados
            'recentVehiculos'   => $this->vehiculoModel->getRecent(5),         //últimos 5 vehículos agregados
            'lowStockItems'     => $this->productoModel->getLowStock(),        //productos con stock bajo
            'recentPedidos'     => $this->pedidoModel->getRecent(5),           //últimos 5 pedidos
            'estadVehiculos'    => $this->vehiculoModel->getCountByTipo(),     //estadísticas de vehículos por tipo
            'estadPedidos'      => $this->pedidoModel->getCountByEstado(),     //estadísticas de pedidos por estado
            'activePage'        => 'inicio' //marcar página activa en menú
        ];
        //cargar vista del dashboard
        require_once 'views/inicio.php';
    }

    //mostrar página de estadísticas más detalladas
    public function estadisticas() {
        //reunir mismo datos que el index pero con más detalles
        $data = [
            'totalVehiculos'    => $this->vehiculoModel->getTotal(),
            'totalStock'        => $this->productoModel->getTotalStock(),
            'stockBajo'         => $this->productoModel->getLowStockCount(),  //sin límite de 6
            'totalPedidos'      => $this->pedidoModel->getTotalPedidos(),
            'totalEmpleados'    => $this->empleadoModel->getTotal(),
            'recentVehiculos'   => $this->vehiculoModel->getRecent(5),
            'lowStockItems'     => $this->productoModel->getLowStock(),
            'recentPedidos'     => $this->pedidoModel->getRecent(5),
            'estadVehiculos'    => $this->vehiculoModel->getCountByTipo(),
            'estadPedidos'      => $this->pedidoModel->getCountByEstado(),
            'activePage'        => 'estadisticas' //marcar página activa
        ];
        //cargar la misma vista pero con indicador de página diferente
        require_once 'views/inicio.php';
    }
}
