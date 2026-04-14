<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * La raíz del sitio redirige a /login (302).
     */
    public function test_root_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
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
