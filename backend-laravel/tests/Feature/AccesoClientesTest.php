<?php

namespace Tests\Feature;

use Tests\TestCase;

class AccesoClientesTest extends TestCase
{
    public function test_una_persona_sin_sesion_no_puede_consultar_clientes(): void
    {
        $this->getJson('/api/clientes')->assertUnauthorized();
        $this->getJson('/api/clientes/1')->assertUnauthorized();
        $this->get('/api/clientes')->assertUnauthorized();
    }
}
