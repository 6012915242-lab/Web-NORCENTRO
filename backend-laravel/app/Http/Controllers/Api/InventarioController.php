<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InventarioServicio;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    public function __construct(private InventarioServicio $inventario)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->inventario->listar()]);
    }

    public function store(Request $solicitud): JsonResponse
    {
        try {
            $unidad = $this->inventario->registrar($solicitud->all());
        } catch (DomainException $error) {
            return response()->json(['mensaje' => $error->getMessage()], 422);
        }

        return response()->json(['data' => $unidad], 201);
    }
}
