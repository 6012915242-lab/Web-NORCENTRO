<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Servicios\ConsultaDniServicio;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class ConsultaDniController extends Controller
{
    public function consultar(Request $solicitud, ConsultaDniServicio $servicio): JsonResponse
    {
        $datos = $solicitud->validate([
            'dni' => ['required', 'string', 'regex:/^[0-9]{8}$/'],
        ]);

        try {
            $persona = $servicio->consultar($datos['dni']);
        } catch (DomainException $error) {
            return response()->json(['ok' => false, 'mensaje' => $error->getMessage()], 422);
        } catch (RuntimeException $error) {
            return response()->json(['ok' => false, 'mensaje' => $error->getMessage()], 503);
        }

        return response()->json(['ok' => true, 'datos' => $persona]);
    }
}
