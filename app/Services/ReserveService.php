<?php

namespace App\Services;

use App\Models\Reserve;
use Illuminate\Support\Facades\Log;

class ReserveService
{
    /**
     * Gerencia todas as regras e cria uma reserva completa
     */
    public function createReserve(array $dadosReserva): Reserve
    {
        // Verifica a disponibilidade de quartos para a reserva
        // considerar no máximo 10 quartos disponíveis
        $reservasExistentes = Reserve::where('room_id', $dadosReserva['room_id'])->where('check_in', $dadosReserva['check_in'])->count();

        if($reservasExistentes >= 10) {
            Log::warning("Tentativa de reserva negada no Service: Disponibilidade de reservas atigindas para o quarto de ID = {$dadosReserva['room_id']}");
            throw new \Exception('Não há disponibildade para este quarto nesta data (limite máximo de 10 ocupações atingido)', 422);
        }

        // Considerando de descontos (cupom) e acrescimos como juros e taxas de serviços
        $valorCalculado = (float) $dadosReserva['total'];

        // Regra de descontos (cupom de 20% de desconto)
        if(isset($dadosReserva['cupom']) && $dadosReserva['cupom'] === 'CUPOM20'){
            $desconto = $valorCalculado * 0.20;
            $valorCalculado = $valorCalculado  - $desconto;
        }

        // Regra da taxa de serviço (A taxa de serviço tem R$ 10 de acréscimo)
        $valorCalculado = $valorCalculado + 10;

        //Regra de juro por método de pagamento (Dinheiro, Cartão de crétido, pix)
        // Dinheiro acréscimo de R$ 0.0
        if(isset($dadosReserva['payment']['method']) && $dadosReserva['payment']['method'] === 'Dinheiro' || $dadosReserva['payment']['method'] === 'dinheiro') {
            $valorCalculado = $valorCalculado + 0.0;
        }

         // Cartão de crédito acréscimo de R$ 10
        if(isset($dadosReserva['payment']['method']) && $dadosReserva['payment']['method'] === 'Cartão de crédito' || $dadosReserva['payment']['method'] ==='cartao de credito') {
            $valorCalculado = $valorCalculado + 10;
        }
        // pix acréscimo de R$ 0.0
        if(isset($dadosReserva['payment']['method']) && $dadosReserva['payment']['method'] === 'Pix' || $dadosReserva['payment']['method'] === 'pix') {
            $valorCalculado = $valorCalculado + 0.0;
        }

        try {
            // Armazenamento dos dados nas tabelas relacionadas
            // Adição da nova reserva
            $reserve = Reserve::create([
                'hotel_id' => $dadosReserva['hotel_id'],
                'room_id' => $dadosReserva['room_id'],
                'check_in' => $dadosReserva['check_in'],
                'check_out' => $dadosReserva['check_out'],
                'total' => $valorCalculado
            ]);

            // Adiciona o hóspede a reserva
            $reserve->guests()->create([
                'name' => $dadosReserva['guest']['name'],
                'last_name' => $dadosReserva['guest']['last_name'],
                'phone' => $dadosReserva['guest']['phone']
            ]);

            // Adiciona a diária do hóspede a reserva
            $reserve->dailies()->create([
                'date' => $dadosReserva['daily']['date'],
                'value' => $dadosReserva['daily']['value']
            ]);

            // Adiciona o pagamento da reserva se houver
            if(!empty($dadosReserva['payment']['method'])){
                $reserve->payments()->create([
                    'method' => $dadosReserva['payment']['method'],
                    'value' => !empty($dadosReserva['payment']['value']) ? $dadosReserva['payment']['value'] : $valorCalculado
                ]);
            }

            // retorna a reserva
            return $reserve;
        } catch(\Exception $erro) {
            // Log do erro ocorrido no armazenamento dos dados da reserva
            Log::error("Erro ao salvar os dados no ReserveService: " . $erro->getMessage());

            // Envia a exceção com o HTTP 500 para o Controle emitir o JSON
            throw new \Exception("Erro interno ao salvar os dados da reserva no banco", 500);

        }
    }
}