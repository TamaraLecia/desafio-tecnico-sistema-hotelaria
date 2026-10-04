<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    // Nome da tabela no banco de dados
    protected $table = 'rooms';

    // Diz ao laravel quais colunas da tabela do banco
    // podem ser preenchidas de uma só vez, 
    // quando enviamos as informações xml
    protected $fillable = ['id', 'hotel_id', 'name'];

    // Um quarto pertence a um hotel
    public function hotel(){

        return $this->belongsTo(Hotel::class, 'hotel_id');
    }
}
