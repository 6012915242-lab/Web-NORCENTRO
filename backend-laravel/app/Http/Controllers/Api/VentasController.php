<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VentasServicio;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VentasController extends Controller
{
    public function __construct(private VentasServicio $servicio) {}

    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->servicio->listar()]);
    }

    public function store(Request $solicitud): JsonResponse
    {
        try {
            $resultado = $this->servicio->registrar($solicitud->all(), (int) $solicitud->user()->id);
        } catch (DomainException $error) {
            return response()->json(['mensaje' => $error->getMessage()], 422);
        }

        return response()->json(['data' => $resultado], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['data' => $this->servicio->obtener($id)]);
    }

    public function creditos(): JsonResponse
    {
        return response()->json(['data' => $this->servicio->listarCreditos()]);
    }

    public function cuotas(int $id): JsonResponse
    {
        return response()->json(['data' => $this->servicio->cuotas($id)]);
    }

    public function pagar(Request $solicitud): JsonResponse
    {
        try {
            $this->servicio->registrarPago($solicitud->all());
        } catch (DomainException $error) {
            return response()->json(['mensaje' => $error->getMessage()], 422);
        }

        return response()->json(['mensaje' => 'Pago registrado.']);
    }
}
