<?php

/**
 * Datos de la web pública del restaurante (alitasdelavieja.com).
 *
 * Todo lo editable de la landing vive acá: contacto, sucursales, redes y los
 * créditos del equipo. El menú NO se escribe aquí — se lee de la base de datos
 * para que siempre refleje los precios reales del sistema.
 */
return [

    // Dominio público. Si la visita llega por este host se muestra la landing
    // del restaurante; por cualquier otro (ej. pos.*) se muestra el acceso al sistema.
    'dominio_publico' => env('PUBLIC_DOMAIN', 'alitasdelavieja.com'),

    'nombre' => 'Alitas La Vieja',
    'lema'   => 'Solo con las manos',
    'descripcion' => 'Alitas crocantes, salsas de la casa y papas recién hechas. Para comer aquí, llevar o pedir a domicilio en Cochabamba y Tarija.',

    // Logo: dejar el archivo en public/img/ (ej. public/img/logo.png).
    // Si no existe, la landing muestra el logotipo tipográfico.
    'logo' => 'img/logo.png',

    'whatsapp' => env('RESTAURANTE_WHATSAPP', ''), // solo dígitos, ej. 59171234567
    'instagram' => env('RESTAURANTE_INSTAGRAM', ''), // URL completa
    'facebook'  => env('RESTAURANTE_FACEBOOK', ''),  // URL completa

    'horarios' => 'Lunes a domingo · 17:00 a 23:00',

    'sucursales' => [
        [
            'ciudad'    => 'Tarija',
            'direccion' => 'Calle Eulogio Ruiz casi Avenida Belgrano',
            'maps'      => '', // link de Google Maps (opcional)
            'telefono'  => '',
        ],
        [
            'ciudad'    => 'Cochabamba',
            'direccion' => '', // ← completar dirección
            'maps'      => '',
            'telefono'  => '',
        ],
    ],

    // Equipo que desarrolló el sistema (sección de créditos al final).
    'equipo' => [
        'titulo'   => 'Sistema de gestión desarrollado por',
        'resumen'  => 'Punto de venta a medida para Alitas La Vieja: pedidos en salón y delivery, control de caja por sucursal, inventario y reportes en tiempo real.',
        'personas' => [
            [
                'nombre'   => 'Álvaro Baldiviezo',
                'rol'      => 'Desarrollo full-stack · Arquitectura del sistema',
                'github'   => '',   // ej. https://github.com/usuario
                'linkedin' => '',
            ],
            [
                'nombre'   => 'Marcelo Sanguino',
                'rol'      => 'Desarrollo full-stack · Módulos de POS y menú',
                'github'   => '',
                'linkedin' => '',
            ],
        ],
        'stack' => ['Laravel 13', 'Livewire 4', 'MySQL', 'Tailwind CSS', 'Docker'],
    ],
];
