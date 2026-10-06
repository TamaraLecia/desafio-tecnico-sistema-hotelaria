<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class RoomController extends Controller
{
    /**
     * Listar todos os quartos (método GET)
     */
    public function index()
    {
        $rooms = Room::with('hotel')->get();

        return response()->json([
            'success' => true,
            'message' => 'Lista de quartos recuperada com sucesso.',
            'data' => $rooms
        ], Response::HTTP_OK);
    }

    /**
     * Criar um quarto (método POST)
     */
    public function store(Request $request)
    {
        try{
            // Validação dos dados recebidos
            $validated = $request->validate([
                'hotel_id' => 'required|exists:hotels,id',
                'name' => 'required|string|max:255'
            ]);

            $room = Room::create($validated);

            // Log para buscar as informações dos quartos
            $dataRoom = Room::with('hotel')->find($room->id);

            // Salvar Log
            Log::info('Quarto criado com sucesso', ['quarto' => $dataRoom]);

            return response()->json([
                'success' => true,
                'message' => 'Quarto cadastrado com sucesso',
                'data' => $room
            ], Response::HTTP_CREATED);

        }catch(\Illuminate\Validation\ValidationException $erro){
            // Se ocorrer erro de validação
            Log::warning('Falha de validação nos dados enviados',[
                'erros_validacao_detectados' => $erro->errors()
            ]);

            // Resposta com o status 422
            throw $erro;

        }catch(\Exception $erro){
            // Log para se der alguma falha ao criar o quarto
            Log::error('Falha ao adicionar o quarto', ['error' => $erro->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível adicionar o quarto devido a um erro interno'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Mostrar um quarto específico, de acordo com o id (GET/{id})
     */
    public function show(string $id)
    {
        try{
            // Log para buscar as informações do quartos
            $room = Room::with('hotel')->findOrFail($id);

            // Salvar Log
            Log::info('Informações do quarto especifíco encontradas com sucesso', ['quarto' => $room]);

            return response()->json([
                'success' => true,
                'message' => 'Informações do quarto encontradas com sucesso',
                'data' => $room
            ], Response::HTTP_OK);

        }catch(\Exception $erro){

            // Log para se der alguma falha ao procurar o quarto
            Log::error('Falha ao procurar informações do quarto especifíco', ['error' => $erro->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Quarto não encontrado'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Atualizar os dados de um quarto (PUT/PATCH/{id})
     */
    public function update(Request $request, string $id)
    {
        try{

            $room = Room::findOrFail($id);

            $validated = $request->validate([
                'hotel_id' => 'sometimes|required|exists:hotels,id',
                'name' => 'sometimes|required|string|max:255'
            ]);

            $room->update($validated);

            // Log para atualizar as informações do quarto
            $dataRoom = Room::with('hotel')->find($room->id);

            // Salvar Log
            Log::info('Informações do quarto atualizada com sucesso', ['quarto' => $dataRoom]);

            return response()->json([
                'success' => true,
                'message' => "Quarto atualizado com sucesso",
                'data' => $room
            ], Response::HTTP_OK);

        }catch(Illuminate\Validation\ValidationException $erro){
            // Se ocorrer erro de validação na atualização
            Log::warning('Falha de validação nos dados enviados',[
                'erros_validacao_detectados' => $erro->errors()
            ]);

            // Resposta com o status 422
            throw $erro;
        }
        catch(\Exception $erro){

            // Log para se der alguma falha ao altualizar o quarto
            Log::error('Falha ao atualizar as informações do quarto', ['error' => $erro->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Quarto não encontrado'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Excluir um quarto (DELETE/{id})
     */
    public function destroy(string $id)
    {
        try{
            $room = Room::findOrFail($id);

            $room->delete();

            // Log para deletar o quarto
            $dataRoom = Room::with('hotel')->find($room->id);

            // Salvar Log
            Log::info('Quarto excluído com sucesso', ['quarto' => $dataRoom]);
            
            return response()->json([
                'success' => true,
                'message' => 'Quarto excluído com sucesso'
            ], Response::HTTP_OK);

        }catch(\Exception $erro){
            // Log para se der alguma falha ao excluir o quarto
            Log::error('Falha ao excluir o quarto, quarto não existe.', ['error' => $erro->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'quarto não existe, impossivél realizar a exclusão do quarto'
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
