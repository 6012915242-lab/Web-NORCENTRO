<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SolicitudesCreditoServicio;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class SolicitudesCreditoController extends Controller
{
    public function __construct(private SolicitudesCreditoServicio $servicio)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->servicio->listar()]);
    }

    public function evaluar(Request $solicitud, int $id): JsonResponse
    {
        $datos = $solicitud->validate([
            'decision' => ['required', 'string', Rule::in(['aprobar', 'rechazar'])],
        ]);

        try {
            $mensaje = $this->servicio->evaluar($id, $datos['decision']);
        } catch (DomainException|InvalidArgumentException $error) {
            return response()->json(['mensaje' => $error->getMessage()], 422);
        }

        return response()->json(['mensaje' => $mensaje]);
    }
}
