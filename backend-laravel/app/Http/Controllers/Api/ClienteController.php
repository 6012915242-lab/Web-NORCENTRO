<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ClienteServicio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function __construct(private ClienteServicio $servicio) {}

    public function index(Request $solicitud): JsonResponse
    {
        $datos = $solicitud->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
        ]);

        return response()->json($this->servicio->listar($datos['buscar'] ?? null));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'data' => $this->servicio->buscarActivo($id),
        ]);
    }

    public function store(Request $solicitud): JsonResponse
    {
        $cliente = $this->servicio->guardar($solicitud->all());

        return response()->json(['data' => $cliente], 201);
    }

    public function update(Request $solicitud, int $id): JsonResponse
    {
        $cliente = $this->servicio->buscarActivo($id);
        $cliente = $this->servicio->guardar($solicitud->all(), $cliente);

        return response()->json(['data' => $cliente]);
    }

    public function destroy(int $id): JsonResponse
    {
        $cliente = $this->servicio->buscarActivo($id);
        $this->servicio->desactivar($cliente);

        return response()->json(['mensaje' => 'Cliente desactivado.']);
    }
}
