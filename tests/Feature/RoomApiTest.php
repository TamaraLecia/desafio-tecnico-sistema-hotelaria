<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Hotel;
use App\Models\Room;

class RoomApiTest extends TestCase
{
    // Ativa a criação automática das tabelas na memória para a realização do teste
    use RefreshDatabase;

    /**
     * Testa se o sistema consegue mostrar todos os quartos
     */
    public function test_deve_mostrar_todos_os_quartos_api(): void
    {
        // requisição para a rota de mostrar quartos (GET)
        $response = $this->getJson('/api/rooms');

        // Confirma se o status da requisição foi 200 OK
        $response->assertStatus(200);

         // Confirma se o JSON traz a confirmação de sucesso
        $response->assertJsonFragment(['success' => true]);
    }

     /**
     * Testa se a API de quarto consegue bloqueia a criação de uma quarto quando os dados são inválidos
     * ou quando tem alguma falha
     */
     public function test_deve_retornar_erro_se_estiver_faltando_dados_obrigatorios_quarto_api(): void
     {

        // Envio de Post com os dados vazios para a rota de adicionar quarto
        $response = $this->postJson('/api/rooms', []);

         // confirma que o sistema impediu o envio dos dados inválidos
        $response->assertStatus(422);

        // Confirma a mensagem de erro no JSON
        $response->assertJsonStructure(['message', 'errors']);
     }


    /**
     * Testa se a API de quarto consegue criar o quarto com sucesso
     */

    public function test_deve_criar_quarto_com_sucesso_api(): void
    {
        // Simula a criação do hotelna memória do teste para simular o banco de dados
        $hotel = Hotel::create(['name' => 'Hotel de Teste']);

        // Script de teste Json
        $envioDadosQuarto = [
            'hotel_id' => $hotel->id,
            'name' => 'Suíte Master'
        ];

        // Envia o Post para criar o quarto
        $response = $this->postJson('/api/rooms', $envioDadosQuarto);

        // confirma se o sistema conseguiu criar o quarto
        $response->assertStatus(201);

        // JSON para confimar se os dados do quarto foram criados com sucesso
        $response->assertJsonFragment(['name' => 'Suíte Master']);
    }

    /**
     * Testa se a API de quarto mostrar dados de um quarto especifíco
     */
    public function test_deve_buscar_um_quarto_especifico_com_sucesso_api(): void
    {
        // Simula a criação do hotelna memória do teste para simular o banco de dados
        $hotel = Hotel::create(['name' => 'Hotel de Teste']);
        $room  = Room::create(['hotel_id' => $hotel->id, 'name' => "Suíte comum"]);

        // Envia o GET para o id do quarto criado
        $response = $this->getJson("/api/rooms/{$room->id}");

        // Confirma se o status da requisição foi 200 OK
        $response->assertStatus(200);

        // Confirma se o JSON traz a confirmação de sucesso
        $response->assertJsonFragment(['name' => 'Suíte comum']);
    }

    /**
    * Testa se a API de quarto consegue atualizar os dados de um quarto especifíco
    */
    public function test_deve_atualizar_os_dados_de_um_quarto_com_sucesso_api(): void
    {
        // Simula a criação do hotelna memória do teste para simular o banco de dados
        $hotel = Hotel::create(['name' => 'Hotel de Teste']);
        $room = Room::create(['hotel_id' => $hotel->id, 'name' => 'Suíte comum']);

        // Script de teste Json
        $dadosQuartoAtualizado = [
            'name' => 'Suíte presidencial'
        ];

        // Requisição PUT para atualizar o nome do quarto
        $response = $this->putJson("/api/rooms/{$room->id}", $dadosQuartoAtualizado);

        // Confirma se o status da requisição foi 200 OK
        $response->assertStatus(200);

        // Confirma se o JSON traz a confirmação de sucesso
        $response->assertJsonFragment(['name' => 'Suíte presidencial']);
    }

    /**
    * Testa se a API de quarto consegue excluir os dados de um quarto
    */
    public function test_deve_excluir_um_quarto_com_sucesso_api(): void
    {
        // Simula a criação do hotelna memória do teste para simular o banco de dados
        $hotel = Hotel::create(['name' => 'Hotel de Teste']);
        $room = Room::create(['hotel_id' => $hotel->id, 'name' => "Suite média"]);

        // Dispara o Delete na rota do quarto específico
        $response = $this->deleteJson("/api/rooms/{$room->id}");

        // Confirma se o status da requisição foi 200 OK
        $response->assertStatus(200);

        // Confirma se o quarto realmente foi apagado do banco de dados
        $this->assertDatabaseMissing('rooms', ['id' => $room->id]);
    }

    
}
