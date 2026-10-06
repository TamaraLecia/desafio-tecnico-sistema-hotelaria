<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReserveApiTest extends TestCase
{
    // Ativa a criação automática das tabelas na memória para a realização do teste
    use RefreshDatabase;
    /**
     * Testa se a API de reserva consegue mostrar todas as reservas com sucesso
     */
    public function test_deve_mostrar_todas_reservas_api(): void
    {
        // requisição para a rota de mostrar reservas (GET)
        $response = $this->getJson('/api/reserves');
        
        // Confirma se o status da requisição foi 200 OK
        $response->assertStatus(200);

        // Confirma se o JSON traz a confirmação de sucesso
        $response->assertJsonFragment(['success' => true]);
    }

    /**
     * Testa se a API de reserva consegue bloqueia a criação de uma reserva quando os dados são inválidos
     * ou quando tem alguma falha
     */
    public function test_deve_retornar_erro_se_estiver_faltando_dados_obrigatorios_reserva_api(): void
    {
        // Envio de Post com os dados vazios para a rota de adicionar reserva
        $response = $this->postJson('/api/reserves', []);

        // confirma que o sistema impediu o envio dos dados inválidos
        $response->assertStatus(422);

        // Confirma a mensagem de erro no JSON
        $response->assertJsonStructure(['message', 'errors']);
    }

    /**
     * Testa se a API de reserva consegue criar a reserva com sucesso
     */

    public function test_deve_criar_reserva_api(): void
    {
        // Simula a criação do hotel e do quarto  na memória do teste para 
        // simular o banco de dados

        $hotel = Hotel::create(['name' => 'Hotel de Teste']);
        $room = Room::create(['hotel_id' => $hotel->id, 'name' => 'Quarto de Teste']);

        // Script de teste Json
        $dadosParaCriarReserva = [
            'hotel_id' => $hotel->id,
            'room_id' => $room->id,
            'check_in' => '2026-12-01',
            'check_out' => '2026-12-05',
            'total' => 500.00,
            'cupom' => 'CUPOM20',
            'guest' => [
                'name' => 'Hóspede',
                'last_name' => 'Teste',
                'phone' => '5571999999999'
            ],
            'daily' => [
                'date' => '2026-12-01',
                'value' => 100.00
            ],
            'payment' => [
                'method' => 'Cartão de crédito'
            ]
        ];

        // Envia o Post para criar a reserva
        $response = $this->postJson('/api/reserves', $dadosParaCriarReserva);

        // confirma se o sistema conseguiu criar a reserva
        $response->assertStatus(201);

        // JSON para confimar se os dados da reserva foram criados com sucesso
        $response ->assertJsonFragment(['success' => true]);
    }


}
