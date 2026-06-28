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
        require_once 'backend/inicio/views/inicio.php';
    }
}
