<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OfertaServicio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfertaController extends Controller
{
    public function __construct(private OfertaServicio $servicio) {}

    public function index(): JsonResponse
    {
        return response()->json($this->servicio->listar());
    }

    public function store(Request $solicitud): JsonResponse
    {
        return response()->json(['data' => $this->servicio->guardar($solicitud->all())], 201);
    }

    public function update(Request $solicitud, int $id): JsonResponse
    {
        $oferta = $this->servicio->buscarActiva($id);

        return response()->json(['data' => $this->servicio->guardar($solicitud->all(), $oferta)]);
    }

    public function destroy(int $id): JsonResponse
    {
        $oferta = $this->servicio->buscarActiva($id);
        $this->servicio->desactivar($oferta);

        return response()->json(['mensaje' => 'Oferta desactivada.']);
    }
}
