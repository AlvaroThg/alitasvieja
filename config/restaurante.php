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
    'instagram' => env('RESTAURANTE_INSTAGRAM', 'https://www.instagram.com/alitasdelavieja/'), // URL completa
    'facebook'  => env('RESTAURANTE_FACEBOOK', 'https://www.facebook.com/alitasdelavieja/?locale=es_LA'),  // URL completa

    'horarios' => 'Lunes a domingo · 17:00 a 23:00',

    'sucursales' => [
        [
            'ciudad'    => 'Tarija',
            'direccion' => 'Calle Eulogio Ruiz casi Avenida Belgrano',
            'maps'      => 'https://maps.app.goo.gl/4gM4D3imcxEGFTda6', // link de Google Maps (opcional)
            'telefono'  => '+59160260608',
        ],
        [
            'ciudad'    => 'Cochabamba',
            'direccion' => 'Pando, Cochabamba, Cochabamba, Bolivia', // ← completar dirección
            'maps'      => 'https://maps.app.goo.gl/XJw8DZuZVqYQxuSW7',
            'telefono'  => '+59169684282',
        ],
    ],

    // Equipo que desarrolló el sistema (sección de créditos al final).
    'equipo' => [
        'titulo'   => 'Sistema de gestión desarrollado por',
        'resumen'  => 'Punto de venta a medida para Alitas La Vieja: pedidos en salón y delivery, control de caja por sucursal, inventario y reportes en tiempo real.',
        'personas' => [
            [
                'nombre'   => 'Alvaro Baldiviezo',
                'rol'      => 'Desarrollo full-stack · Arquitectura del sistema',
                'github'   => 'https://github.com/AlvaroThg',   // ej. https://github.com/usuario
                'linkedin' => 'https://www.linkedin.com/in/alvarofabianbaldiviezorodriguez',
            ],
            [
                'nombre'   => 'Marcelo Sanguino',
                'rol'      => 'Desarrollo full-stack · Módulos de POS y menú',
                'github'   => 'https://github.com/Chelo-sanguino',
                // Los acentos van codificados para que el enlace no se rompa al copiarse.
                'linkedin' => 'https://www.linkedin.com/in/sanguino-fern%C3%A1ndez-marcelo-adri%C3%A1n-922849257',
            ],
        ],
        'stack' => ['Laravel 13', 'Livewire 4', 'MySQL', 'Tailwind CSS', 'Docker'],
    ],
];
