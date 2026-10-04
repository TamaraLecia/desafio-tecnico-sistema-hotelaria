<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    // Nome da tabela no banco de dados
    protected $table = 'hotels';

    // Diz ao laravel quais colunas da tabela do banco
    // podem ser preenchidas de uma só vez, 
    // quando enviamos as informações xml
    protected $fillable = ['id', 'name'];

    // Um hotel possui muitos quartos
    public function rooms() {

        return $this->hasMany(Room::class, 'hotel_id');
    }
}
