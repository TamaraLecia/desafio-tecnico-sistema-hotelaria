<?php

namespace App\Http\Controllers;

use App\Models\Reserve;

// Importação do service (serviço)
use App\Services\ReserveService;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ReserveController extends Controller
{
    protected $reserveService;

    // Adição da dependência de serviço no construtor do Controller
    public function __construct(ReserveService $reserveService)
    {
        $this->reserveService = $reserveService;
    }

    /**
     * Listar todas as reservas (método GET)
    */
    public function index()
    {
        // lista todas as reservas com os dados das tabelas relacionadas
        $reserves = Reserve::with(['hotel', 'room', 'guests', 'dailies', 'payments'])->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Lista de reservas recuperada com sucesso',
            'data' => $reserves
        ], Response::HTTP_OK);
    }

    /**
     * Criar uma reserva utilizando as informaçoes do ReserveService (método POST)
     */
    public function store(Request $request)
    {
        try{
            // Validação dos dados recebidos para evitar erros no banco
            $validated = $request->validate([
                // Dados da reserva
                'hotel_id' => 'required|exists:hotels,id',
                'room_id' => 'required|exists:rooms,id',
                'check_in' => 'required|date|date_format:Y-m-d',
                'check_out' => 'required|date|date_format:Y-m-d|after:check_in',
                'total' => 'required|numeric|min:0',
                // Dados do hóspede
                'guest.name' => 'required|string|max:255',
                'guest.last_name' => 'required|string|max:255',
                'guest.phone' => 'required|string|max:50',
                // Dados da diária
                'daily.date' => 'required|date',
                'daily.value' => 'required|numeric',
                // Dados do pagamento
                'payment.method' => 'nullable|string|max:50',
                'payment.value' => 'nullable|numeric'

            ], [
                // Mensagens personalizadas caso algum campo não seja preenchido corretamente
                'hotel_id.required' => 'O campo hotel_id é obrigatório',
                'hotel_id.exists' => 'O hotel informado não existe no sistema',
                'room_id.required' => 'O campo room_id é obrigatório',
                'room_id.exists' => 'O quarto informado não existe no sistema',
                'check_in.required' => 'o campo para a data de check_in é obrigatório',
                'check_in.date_format' => 'a data de check_in deve está no formato AAAA-MM-DD',
                'check_out.required' => 'o campo para a data de check_out é obrigatório',
                'check_out.date_format' => 'a data de check_out deve está no formato AAAA-MM-DD',
                'check_out.after' => 'a data de check_out deve ser informada após à data de check_in',
                'total.required' => 'o valor total da reserva é obrigatório',
                'total.numeric' => 'o valor total deve ser um número válido',
                'total.min' => 'o valor total tem que ser maior que zero',
                'guest.name.required'  => 'o nome do hóspede é obrigatório.',
                'guest.last_name.required'  => 'o sobrenome do hóspede é obrigatório.',
                'guest.phone.required' => 'o número de telefone do hóspede é obrigatório.',
                'daily.date.required'  => 'a data da diária é obrigatório.',
                'daily.value.required' => 'o valor da diária é obrigatório.'

            ]);

            // Executar a regra de negócio definida no ReserveService.php
            $reserve = $this->reserveService->createReserve($request->all());

            // Carrega todos os dados da tabela reserva e das tabelas relacionadas a ele, para gerar 
            // uma resposta JSON completa

            $reserve->load(['hotel', 'room', 'guests', 'dailies', 'payments']);

            // Log para buscar as informações das reservas
            $dataReserve = Reserve::with('hotel', 'room', 'guests', 'dailies', 'payments')->find($reserve->id);

            // Salvar Log
            Log::info('Reserva criada com sucesso', ['reserva' => $dataReserve]);

            // Retorna a mensagem em JSON que a reserva foi criada
            return response()->json([
                'success' => true,
                'message' => 'Reserva com hóspede e diária criada com sucesso',
                'data' => $reserve
            ], Response::HTTP_CREATED);

        }catch(\Illuminate\Validation\ValidationException $erro){
            // Se ocorrer erro de validação
            Log::warning('Falha de validação dos dados enviados',[
                'erros_validacao_detectados' => $erro->errors()
            ]);

            // Resposta com o status 422
            throw $erro;

        }catch(\Exception $erro){
            // Log para se der alguma falha ao criar a reserva
            Log::error('Falha ao adicionar a reserva', ['error' => $erro->getMessage()]);

            // Se uma reserva de um quarto já tiver atigindo o limite de 10 reservas para o mesmo quarto
            if($erro->getCode() === 422) {
                $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
            } else {
                $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            }

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível criar a reserva. Não há disponibildade para este quarto nesta data (limite máximo de 10 ocupações atingido)'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
