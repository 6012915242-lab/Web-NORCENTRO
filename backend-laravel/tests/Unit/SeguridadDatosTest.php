<?php

namespace Tests\Unit;

use App\Configuracion\Herramientas;
use App\Middlewares\SeguridadMiddleware;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . '/autoload.php';

class SeguridadDatosTest extends TestCase
{
    public function test_limpia_espacios_y_conserva_datos_anidados(): void
    {
        $resultado = SeguridadMiddleware::sanearArregloEntrada([
            ' nombre ' => '  Ana  ',
            'contacto' => ['telefono' => '  123456789  '],
        ]);

        $this->assertSame('Ana', $resultado['nombre']);
        $this->assertSame('123456789', $resultado['contacto']['telefono']);
    }

    public function test_escapa_texto_antes_de_mostrarlo_en_html(): void
    {
        $salida = Herramientas::escaparHtml('<script>alert("hola")</script>');

        $this->assertStringNotContainsString('<script>', $salida);
        $this->assertStringContainsString('&lt;script&gt;', $salida);
        $this->assertStringContainsString('&quot;hola&quot;', $salida);
    }
}
