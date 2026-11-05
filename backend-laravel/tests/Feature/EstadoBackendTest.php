<?php

namespace Tests\Feature;

use Tests\TestCase;

class EstadoBackendTest extends TestCase
{
    public function test_la_pagina_del_backend_identifica_norcentro(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Backend de NORCENTRO');
    }
}
