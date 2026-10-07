<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\Room;
use App\Models\Reserve;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Garante o caminho correto para a pasta XML
        $caminhoPasta = database_path('XML');
        if (!is_dir($caminhoPasta)) {
            $caminhoPasta = database_path('xml');
        }

        if (!is_dir($caminhoPasta)) {
            Log::error("Pasta de XMLs nao encontrada.");
            $this->command->error("Pasta de XMLs nao encontrada!");
            return;
        }

        // 1. IMPORTAÇÃO DOS HOTÉIS PRIMEIRO
        $arquivoHoteis = $caminhoPasta . '/hotels.xml';
        if (file_exists($arquivoHoteis)) {
            try {
                $xmlHoteis = simplexml_load_file($arquivoHoteis);
                foreach (($xmlHoteis->Hotel ?? $xmlHoteis->hotel ?? []) as $hotelXml) {
                    $idHotel = (int) ($hotelXml['id'] ?? $hotelXml['code'] ?? 1);
                    $nomeHotel = (string) ($hotelXml->Name ?? $hotelXml->name);
                    
                    Hotel::firstOrCreate(
                        ['id' => $idHotel],
                        ['name' => $nomeHotel]
                    );
                }
                $this->command->info("Hoteis importados com sucesso!");
            } catch (\Exception $e) {
                Log::error("Erro ao importar hoteis: " . $e->getMessage());
            }
        }

        // 2. IMPORTAÇÃO DOS QUARTOS
        $arquivoQuartos = $caminhoPasta . '/rooms.xml';
        if (file_exists($arquivoQuartos)) {
            try {
                $xmlQuartos = simplexml_load_file($arquivoQuartos);
                foreach (($xmlQuartos->Room ?? []) as $quartoXml) {
                    $idQuarto = (int) $quartoXml['id'];
                    $hotelId = (int) $quartoXml['hotelCode']; 
                    $nomeQuarto = (string) $quartoXml->Name;  

                    if (!Hotel::where('id', $hotelId)->exists()) {
                        Hotel::create(['id' => $hotelId, 'name' => "Hotel Temporario {$hotelId}"]);
                    }

                    Room::firstOrCreate(
                        ['id' => $idQuarto],
                        [
                            'hotel_id' => $hotelId,
                            'name' => $nomeQuarto
                        ]
                    );
                }
                $this->command->info("Quartos importados com sucesso!");
            } catch (\Exception $e) {
                Log::error("Erro ao importar quartos: " . $e->getMessage());
            }
        }

        // 3. IMPORTAÇÃO DAS RESERVAS, HÓSPEDES, DIÁRIAS E PAGAMENTOS
        $arquivoReservas = $caminhoPasta . '/reserves.xml';
        if (file_exists($arquivoReservas)) {
            try {
                $xmlReservas = simplexml_load_file($arquivoReservas);
                
                foreach (($xmlReservas->Reserve ?? []) as $reservaXml) {
                    $idReserva = (int) $reservaXml['id'];
                    $hotelId = (int) $reservaXml['hotelCode'];
                    $roomId = (int) $reservaXml['roomCode'];
                    $checkIn = (string) $reservaXml->CheckIn;
                    $checkOut = (string) $reservaXml->CheckOut;
                    $total = (float) $reservaXml->Total;

                    // Garante consistencia de chaves estrangeiras
                    if (!Hotel::where('id', $hotelId)->exists()) {
                        Hotel::create(['id' => $hotelId, 'name' => "Hotel Temporario {$hotelId}"]);
                    }
                    if (!Room::where('id', $roomId)->exists()) {
                        Room::create(['id' => $roomId, 'hotel_id' => $hotelId, 'name' => "Quarto Temporario {$roomId}"]);
                    }

                    // Cria a reserva principal
                    $reserva = Reserve::firstOrCreate(
                        ['id' => $idReserva],
                        [
                            'hotel_id' => $hotelId,
                            'room_id' => $roomId,
                            'check_in' => $checkIn,
                            'check_out' => $checkOut,
                            'total' => $total
                        ]
                    );

                    // Salva Hóspedes vinculados (Coleção <Guests>)
                    if (isset($reservaXml->Guests->Guest)) {
                        foreach ($reservaXml->Guests->Guest as $guestXml) {
                            DB::table('guests')->insertOrIgnore([
                                'reserve_id' => $reserva->id,
                                'name' => (string) $guestXml->Name,
                                'last_name' => (string) $guestXml->LastName,
                                'phone' => (string) $guestXml->Phone,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }

                    // Salva Diárias vinculadas (Coleção <Dailies>)
                    if (isset($reservaXml->Dailies->Daily)) {
                        foreach ($reservaXml->Dailies->Daily as $dailyXml) {
                            DB::table('dailies')->insertOrIgnore([
                                'reserve_id' => $reserva->id,
                                'date' => (string) $dailyXml->Date,
                                'value' => (float) $dailyXml->Value,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }

                    // Salva Pagamentos vinculados (Coleção <Payments>)
                    if (isset($reservaXml->Payments->Payment)) {
                        foreach ($reservaXml->Payments->Payment as $paymentXml) {
                            DB::table('payments')->insertOrIgnore([
                                'reserve_id' => $reserva->id,
                                'method' => (string) $paymentXml->Method,
                                'value' => (float) $paymentXml->Value,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
                $this->command->info("Reservas e tabelas filhas importadas com sucesso!");
            } catch (\Exception $e) {
                Log::error("Erro ao importar reservas: " . $e->getMessage());
            }
        }

        $this->command->info("Banco de dados alimentado com a estrutura de todos os seus XMLs!");
    }
}
