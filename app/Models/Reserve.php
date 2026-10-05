<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserve extends Model
{
    // Nome da tabela no banco de dados
    protected $table = 'reserves';

    // Diz ao laravel quais colunas da tabela do banco
    // podem ser preenchidas de uma só vez, 
    // quando enviamos as informações xml
    protected $fillable = ['id', 'hotel_id', 'room_id', 'check_in', 'check_out', 'total'];

    // Uma reserva pertence a um hotel
    public function hotel(){

        return $this->belongsTo(Hotel::class, 'hotel_id');
    }

    // Uma reserva pertence a um quarto
    public function room(){

        return $this->belongsTo(Room::class, 'room_id');
    }

    // Uma reserva tem muitos hóspedes
    public function guests(){

        return $this->hasMany(Guest::class, 'reserve_id');
    }

    // Uma reserva tem muitas diárias
    public function dailies(){

        return $this->hasMany(Daily::class, 'reserve_id');
    }

    // Uma reserva tem muitos pagamentos
    public function payments(){

        return $this->hasMany(Payment::class, 'reserve_id');
    }
}
