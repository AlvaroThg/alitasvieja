<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * La raíz es la landing pública del restaurante (200), no el POS:
     * un cliente que entra al dominio debe ver el menú, no un login.
     */
    public function test_root_shows_public_landing(): void
    {
        $response = $this->withoutVite()->get('/');

        $response->assertStatus(200);
        $response->assertSee(config('restaurante.nombre'), false);
    }

    /**
     * La página de login es accesible públicamente (200).
     */
    public function test_login_page_is_accessible(): void
    {
        // withoutVite() evita errores por ausencia del manifest en CI
        $response = $this->withoutVite()->get('/login');

        $response->assertStatus(200);
    }
}
