<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Oferta;
use App\Services\OfertaServicio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfertaController extends Controller
{
    public function __construct(private OfertaServicio $servicio)
    {
    }

    public function index(): JsonResponse
    {
        $ofertas = Oferta::query()->with('moto.marca')->orderByDesc('fecha_fin')->paginate(20);

        return response()->json($ofertas);
    }

    public function store(Request $solicitud): JsonResponse
    {
        return response()->json(['data' => $this->servicio->guardar($solicitud->all())], 201);
    }

    public function update(Request $solicitud, int $id): JsonResponse
    {
        $oferta = Oferta::query()->where('estado', 'Activa')->findOrFail($id);

        return response()->json(['data' => $this->servicio->guardar($solicitud->all(), $oferta)]);
    }

    public function destroy(int $id): JsonResponse
    {
        $oferta = Oferta::query()->where('estado', 'Activa')->findOrFail($id);
        $this->servicio->desactivar($oferta);

        return response()->json(['mensaje' => 'Oferta desactivada.']);
    }
}
