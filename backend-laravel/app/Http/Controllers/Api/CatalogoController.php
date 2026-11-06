<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CatalogoServicio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    public function __construct(private CatalogoServicio $catalogo) {}

    public function index(Request $solicitud): JsonResponse
    {
        $datos = $solicitud->validate(['buscar' => ['nullable', 'string', 'max:100']]);

        return response()->json($this->catalogo->listar(trim($datos['buscar'] ?? '')));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['data' => $this->catalogo->obtener($id)]);
    }
}
