<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\Reserve;
use App\Models\Guest;
use App\Models\Daily;
use App\Models\Payment;

#[Signature('app:import-xml-command')]
#[Description('Ler os arquivos XML de hotels, rooms e reserves e armazena o dados no banco de dados')]
class ImportXmlCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando a importação dos arquivos XML...');

        // Primeiro importar hotéis
        $hotelsPath = database_path('xml/hotels.xml');
        if(file_exists($hotelsPath)) {
            $xml = simplexml_load_file($hotelsPath);
            foreach ($xml->Hotel as $hotelXml){
                Hotel::updateOrCreate(
                    ['id' => (int) $hotelXml['id']],
                    ['name' => (string) $hotelXml->Name]
                );
            }
            $this->info('Hotéis importados com sucesso');
        } else {
            $this->error('Arquivo hotels.xml não encontrado');
        }

        // Segundo importar quartos
        $roomsPath = database_path('xml/rooms.xml');
        if(file_exists($roomsPath)) {
            $xml = simplexml_load_file($roomsPath);
            foreach ($xml->Room as $roomXml) {
                Room::updateOrCreate(
                    ['id' => (int) $roomXml['id']],
                    [
                        'hotel_id' => (int) $roomXml['hotelCode'],
                        'name' => (string) $roomXml->Name
                    ]
                );
            }
            $this->info('Quartos importados com sucesso');
        } else {
            $this->error('Aquivo rooms.xml não encontrado');
        }

        // Terceiro importar reservas, hóspedes, diárias e pagamentos
        $reservesPath = database_path('xml/reserves.xml');
        if(file_exists($reservesPath)) {
            $xml = simplexml_load_file($reservesPath);
            foreach ($xml->Reserve as $reserveXml) {
                // Cria ou atualiza a reserva principal, usando a sintaxe de propriedade dinâmica do SimpleXMLElement
                $reserve = Reserve::updateOrCreate(
                    ['id' => (int) $reserveXml['id']],
                    [
                        'hotel_id'  => (int) $reserveXml['hotelCode'],
                        'room_id'   => (int) $reserveXml['roomCode'],
                        'check_in'  => (string) $reserveXml->{"CheckIn"},
                        'check_out' => (string) $reserveXml->{"CheckOut"},
                        'total'     => (float) $reserveXml->Total,
                    ]
                );

                // Importar hóspedes vinculados a esta reserva
                if (isset($reserveXml->Guests->Guest)) {
                    foreach ($reserveXml->Guests->Guest as $guestXml){
                        Guest::updateOrCreate(
                            [
                                'reserve_id' => $reserve->id,
                                'phone'      => (string) $guestXml->Phone
                            ],
                            [
                                'name'      => (string) $guestXml->Name,
                                'last_name' => (string) $guestXml->LastName,
                            ]
                        );
                    }
                }

                // Importar diárias vinculadas a esta reserva
                if (isset($reserveXml->Dailies->Daily)) {
                    foreach ($reserveXml->Dailies->Daily as $dailyXml) {
                        Daily::updateOrCreate(
                            [
                                'reserve_id' => $reserve->id,
                                'date'       => (string) $dailyXml->Date
                            ],
                            [
                                'value' => (float) $dailyXml->Value,
                            ]
                        );
                    }
                }

                // Importar pagamentos vinculados a esta reserva, se houver pagamentos
                if(isset($reserveXml->Payments->Payment)) {
                    foreach ($reserveXml->Payments->Payment as $paymentXml) {
                        Payment::updateOrCreate(
                            [
                                'reserve_id' => $reserve->id,
                                'method'     => (string) $paymentXml->Method,
                                'value'      => (float) $paymentXml->Value
                            ]
                        );
                    }
                }
            }
            $this->info('Reservas e dados vinculados importados com sucesso');
        } else {
            $this->error('Arquivo reserves.xml não encontrado');
        }

        $this->info('Processo de importação finalizado com sucesso');
        return Command::SUCCESS;
    }
}
