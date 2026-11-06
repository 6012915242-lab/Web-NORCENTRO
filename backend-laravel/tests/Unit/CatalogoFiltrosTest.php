<?php

namespace Tests\Unit;

use App\Servicios\CatalogoFiltrosServicio;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . '/autoload.php';

class CatalogoFiltrosTest extends TestCase
{
    private function motos(): array
    {
        return [
            [
                'id' => 1,
                'marca_id' => 1,
                'marca' => 'Bajaj',
                'modelo' => 'Pulsar',
                'cilindrada' => 160,
                'precio' => 9000
            ],
            [
                'id' => 2,
                'marca_id' => 1,
                'marca' => 'Bajaj',
                'modelo' => 'NS400',
                'cilindrada' => 400,
                'precio' => 19000
            ],
            [
                'id' => 3,
                'marca_id' => 2,
                'marca' => 'Yamaha',
                'modelo' => 'MT-15',
                'cilindrada' => 155,
                'precio' => 16000
            ],
        ];
    }

    public function test_filtra_por_marca_cilindrada_y_precio(): void
    {
        $resultado = (new CatalogoFiltrosServicio())->preparar($this->motos(), [
            'marca' => '1', 'cilindrada' => '160', 'precio_max' => '10000',
        ], true);

        $this->assertCount(1, $resultado['catalogo']);
        $this->assertSame('Pulsar', $resultado['catalogo'][0]['modelo']);
        $this->assertSame([160, 400], $resultado['cilindradasCatalogo']);
    }

    public function test_descarta_un_modelo_fuera_de_la_marca(): void
    {
        $resultado = (new CatalogoFiltrosServicio())->preparar($this->motos(), [
            'marca' => '1', 'modelo' => 'MT-15',
        ], true);

        $this->assertSame('', $resultado['modeloSeleccionado']);
        $this->assertCount(2, $resultado['catalogo']);
    }

    public function test_ignora_filtros_con_estructura_invalida(): void
    {
        $resultado = (new CatalogoFiltrosServicio())->preparar($this->motos(), [
            'marca' => ['1'], 'modelo' => ['Pulsar'], 'precio_max' => ['100'],
        ], true);

        $this->assertSame(0, $resultado['marcaSeleccionada']);
        $this->assertSame('', $resultado['modeloSeleccionado']);
        $this->assertCount(3, $resultado['catalogo']);
    }
}
