<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

class RoomController extends Controller
{
    
    // /Listar todos os quartos (método GET)
    #[OA\Get(
        path: "/rooms",
        summary: "Listar todos os quartos (READ)",
        description: "Retorna uma lista completa de todos os quartos cadastrados no sistema hoteleiro, incluindo os dados dos hotéis vinculados.",
        tags: ["Quartos"]
    )]
    #[OA\Response(
        response: 200,
        description: "Lista de quartos recuperada com sucesso."
    )]
    #[OA\Response(
        response: 500,
        description: "Erro interno no servidor."
    )]

    public function index()
    {
        $rooms = Room::with('hotel')->get();

        return response()->json([
            'success' => true,
            'message' => 'Lista de quartos recuperada com sucesso.',
            'data' => $rooms
        ], Response::HTTP_OK);
    }

    // Criar um quarto (método POST)
     

    #[OA\Post(
        path: "/rooms",
        summary: "Criar um novo quarto (CREATE)",
        description: "Cadastra um novo quarto associado a um hotel existente no sistema.",
        tags: ["Quartos"]
    )]
    #[OA\RequestBody(
        required: true,
        description: "Dados necessários para cadastrar o quarto",
        content: new OA\JsonContent(
            required: ["hotel_id", "name"],
            properties: [
                new OA\Property(property: "hotel_id", type: "integer", example: 1, description: "ID do hotel existente no banco"),
                new OA\Property(property: "name", type: "string", example: "Suíte Master Vista Mar", description: "Nome ou descrição do quarto")
            ]
        )
    )]
    #[OA\Response(response: 201, description: "Quarto cadastrado com sucesso.")]
    #[OA\Response(response: 422, description: "Dados inválidos ou hotel não encontrado.")]
    #[OA\Response(response: 500, description: "Erro interno no servidor ao tentar salvar.")]

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

    //Mostrar um quarto específico, de acordo com o id (GET/{id})
     #[OA\Get(
        path: "/rooms/{id}",
        summary: "Mostrar um quarto específico (READ)",
        description: "Busca e retorna as informações detalhadas de um quarto com base no ID fornecido na URL.",
        tags: ["Quartos"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "ID numérico do quarto a ser buscado", schema: new OA\Schema(type: "string"))
        ]
    )]
    #[OA\Response(response: 200, description: "Informações do quarto encontradas com sucesso.")]
    #[OA\Response(response: 404, description: "Quarto não encontrado no sistema.")]

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

    //Atualizar os dados de um quarto (PUT/PATCH/{id})

    #[OA\Put(
        path: "/rooms/{id}",
        summary: "Atualizar os dados de um quarto (UPDATE)",
        description: "Atualiza as informações parciais ou totais de um quarto com base no ID enviado na URL.",
        tags: ["Quartos"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "ID numérico do quarto a ser editado", schema: new OA\Schema(type: "string"))
        ]
    )]
    #[OA\RequestBody(
        required: true,
        description: "Dados permitidos para atualização",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "hotel_id", type: "integer", example: 1, description: "ID do novo hotel (opcional)"),
                new OA\Property(property: "name", type: "string", example: "Suíte Master Casal Luxo", description: "Novo nome do quarto (opcional)")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Quarto atualizado com sucesso.")]
    #[OA\Response(response: 404, description: "Quarto não encontrado ou dados fornecidos inválidos.")]

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

    //Excluir um quarto (DELETE/{id})

     #[OA\Delete(
        path: "/rooms/{id}",
        summary: "Excluir um quarto (DELETE)",
        description: "Remove permanentemente um quarto do banco de dados do sistema hoteleiro.",
        tags: ["Quartos"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "ID numérico do quarto a ser excluído", schema: new OA\Schema(type: "string"))
        ]
    )]
    #[OA\Response(response: 200, description: "Quarto excluído com sucesso.")]
    #[OA\Response(response: 404, description: "Quarto não encontrado para exclusão.")]

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
