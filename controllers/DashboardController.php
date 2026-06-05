<?php
// controllers/DashboardController.php

class DashboardController {
    private $vehiculoModel;
    private $productoModel;
    private $pedidoModel;
    private $empleadoModel;

    public function __construct() {
        $this->vehiculoModel = new VehiculoModel();
        $this->productoModel = new ProductoModel();
        $this->pedidoModel   = new PedidoModel();
        $this->empleadoModel = new EmpleadoModel();
    }

    public function index() {
        $data = [
            'totalVehiculos'    => $this->vehiculoModel->getTotal(),
            'totalStock'        => $this->productoModel->getTotalStock(),
            'stockBajo'         => $this->productoModel->getLowStockCount(),
            'totalPedidos'      => $this->pedidoModel->getTotalPedidos(),
            'totalEmpleados'    => $this->empleadoModel->getTotal(),
            'recentVehiculos'   => $this->vehiculoModel->getRecent(5),
            'lowStockItems'     => $this->productoModel->getLowStock(),
            'recentPedidos'     => $this->pedidoModel->getRecent(5),
            'estadVehiculos'    => $this->vehiculoModel->getCountByTipo(),
            'estadPedidos'      => $this->pedidoModel->getCountByEstado(),
            'activePage'        => 'dashboard'
        ];
        require_once 'views/dashboard.php';
    }

    public function estadisticas() {
        $data = [
            'totalVehiculos'    => $this->vehiculoModel->getTotal(),
            'totalStock'        => $this->productoModel->getTotalStock(),
            'stockBajo'         => $this->productoModel->getLowStockCount(),
            'totalPedidos'      => $this->pedidoModel->getTotalPedidos(),
            'totalEmpleados'    => $this->empleadoModel->getTotal(),
            'recentVehiculos'   => $this->vehiculoModel->getRecent(5),
            'lowStockItems'     => $this->productoModel->getLowStock(),
            'recentPedidos'     => $this->pedidoModel->getRecent(5),
            'estadVehiculos'    => $this->vehiculoModel->getCountByTipo(),
            'estadPedidos'      => $this->pedidoModel->getCountByEstado(),
            'activePage'        => 'estadisticas'
        ];
        require_once 'views/dashboard.php';
    }
}
