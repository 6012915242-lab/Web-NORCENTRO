
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
        ]);

        $clientes = Cliente::query()
            ->select([
                'id',
                'dni',
                'nombres',
                'apellidos',
                'telefono',
                'correo',
                'estado',
            ])
            ->where('estado', 'Activo')
            ->when($datos['buscar'] ?? null, function ($consulta, $buscar) {
                $consulta->where(function ($subconsulta) use ($buscar) {
                    $subconsulta
                        ->where('dni', 'like', "%{$buscar}%")
                        ->orWhere('nombres', 'like', "%{$buscar}%")
                        ->orWhere('apellidos', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->paginate(20);

        return response()->json($clientes);
    }

    public function show(int $id): JsonResponse
    {
        $cliente = Cliente::query()
            ->select([
                'id',
                'dni',
                'nombres',
                'apellidos',
                'telefono',
                'correo',
                'estado',
            ])
            ->where('estado', 'Activo')
            ->findOrFail($id);

        return response()->json([
            'data' => $cliente,
        ]);
    }
}
