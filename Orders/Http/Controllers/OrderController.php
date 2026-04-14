<?php

namespace Modules\Orders\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Orders\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    // Inyectamos el servicio en el controlador
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index()
    {
        return response()->json([
            'message' => 'Módulo de órdenes funcionando perfectamente'
        ]);
    }
}