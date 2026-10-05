<?php

namespace App\Http\Controllers;

use App\Models\Reserve;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReserveController extends Controller
{

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
     * Criar uma reserva (método POST)
     */
    public function store(Request $request)
    {
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

        // Adição da nova reserva
        $reserve = Reserve::create([
            'hotel_id' => $request->hotel_id,
            'room_id' => $request->room_id,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'total' => $request->total
        ]);

        // Adiciona o hóspede a reserva
        $reserve->guests()->create([
            'name' => $request->input('guest.name'),
            'last_name' => $request->input('guest.last_name'),
            'phone' => $request->input('guest.phone')
        ]);

        // Adiciona a diária do hóspede a reserva
        $reserve->dailies()->create([
            'date' => $request->input('daily.date'),
            'value' => $request->input('daily.value')
        ]);

        // Adiciona o pagamento da reserva se houver
        if($request->filled('payment.method') && $request->filled('payment.value')) {
            $reserve->payments()->create([
                'method' => $request->input('payment.method'),
                'value' => $request->input('payment.value')
            ]);
        }

        // Carrega todos os dados da tabela reserva e das tabelas relacionadas a ele, para gerar 
        // uma resposta JSON completa

        $reserve->load(['hotel', 'room', 'guests', 'dailies', 'payments']);

        // Retorna a mensagem em JSON que a reserva foi criada
        return response()->json([
            'success' => true,
            'message' => 'Reserva com hóspede e diária criada com sucesso',
            'data' => $reserve
        ], Response::HTTP_CREATED);
    }
}
