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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
