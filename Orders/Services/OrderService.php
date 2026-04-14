<?php

namespace Modules\Orders\Services;

class OrderService
{
    public function calculateTotal($items)
    {
        // Logica para el negocio posteriormente buen Alvarito:)
        $total = 0;
        foreach ($items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }
}