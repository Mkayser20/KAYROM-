<?php
//Controlador de la página de inicio/dashboard
//Reúne estadísticas de todos los módulos del sistema
class InicioController {
    private $vehiculoModel;  //para obtener datos de vehículos
    private $repuestoModel;  //para obtener datos de stock (repuestos es el módulo real, no "productos")
    private $pedidoModel;    //para obtener datos de pedidos
    private $empleadoModel;  //para obtener datos de empleados

    public function __construct() {
        $this->vehiculoModel = new VehiculoModel();
        $this->repuestoModel = new RepuestoModel();
        $this->pedidoModel   = new PedidoModel();
        $this->empleadoModel = new EmpleadoModel();
    }

    //mostrar dashboard con estadísticas generales
    public function index() {
        //reunir datos de todos los módulos para mostrar en el dashboard
        $data = [
            'totalVehiculos'    => $this->vehiculoModel->getTotal(),      //total de vehículos registrados
            'totalStock'        => $this->repuestoModel->getTotalStock(), //cantidad total de repuestos en stock
            'stockBajo'         => $this->repuestoModel->getLowStockCount(), //cantidad de repuestos con stock bajo
            'totalPedidos'      => $this->pedidoModel->getTotalPedidos(),      //total de pedidos registrados
            'totalEmpleados'    => $this->empleadoModel->getTotal(),           //total de empleados
            'recentVehiculos'   => $this->vehiculoModel->getRecent(5),         //últimos 5 vehículos agregados
            'lowStockItems'     => $this->repuestoModel->getLowStock(),        //repuestos con stock bajo
            'recentPedidos'     => $this->pedidoModel->getRecent(5),           //últimos 5 pedidos
            'estadVehiculos'    => $this->vehiculoModel->getCountByTipo(),     //estadísticas de vehículos por tipo
            'estadPedidos'      => $this->pedidoModel->getCountByEstado(),     //estadísticas de pedidos por estado
            'activePage'        => 'inicio' //marcar página activa en menú
        ];
        //cargar vista del dashboard
        require_once 'backend/inicio/views/inicio.php';
    }
}
