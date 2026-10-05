<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RoomController extends Controller
{
    /**
     * Listar todos os quartos (método GET)
     */
    public function index()
    {
        $rooms = Room::with('hotel')->get();

        return response()->json([
            'sucess' => true,
            'message' => 'Lista de quartos recuperada com sucesso.',
            'data' => $rooms
        ], Response::HTTP_OK);
    }

    /**
     * Criar um quarto (método POST)
     */
    public function store(Request $request)
    {
        // Validação dos dados recebidos
        $validated = $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'name' => 'required|string|max:255'
        ]);

        $room = Room::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Quarto cadastrado com sucesso',
            'data' => $room
        ], Response::HTTP_CREATED);
    }

    /**
     * Mostrar um quarto específico, de acordo com o id (GET/{id})
     */
    public function show(string $id)
    {
        $room = Room::with('hotel')->find($id);

        if(!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Quarto não encontrado'
            ], Response::HTTP_NOT_FOUND);
        }
        return response()->json([
            'success' => true,
            'message' => 'Informações do quarto encontradas com sucesso',
            'data' => $room
        ], Response::HTTP_OK);
    }

    /**
     * Atualizar os dados de um quarto (PUT/PATCH/{id})
     */
    public function update(Request $request, string $id)
    {
        $room = Room::find($id);

        if(!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Quarto não encontrado'
            ], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'hotel_id' => 'sometimes|required|exists|hotels,id',
            'name' => 'sometimes|required|string|max:255'
        ]);

        $room->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Quarto atualizado com sucesso",
            'data' => $room
        ], Response::HTTP_OK);
    }

    /**
     * Excluir um quarto (DELETE/{id})
     */
    public function destroy(string $id)
    {
        $room = Room::find($id);

        if(!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Quarto não encontrado'
            ], Response::HTTP_NOT_FOUND);
        }

        $room->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Quarto excluído com sucesso'
        ], Response::HTTP_OK);
    }
}
