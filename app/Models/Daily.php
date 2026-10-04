<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Daily extends Model
{
    // Nome da tabela no banco de dados
    protected $table = 'dailies';

    // Diz ao laravel quais colunas da tabela do banco
    // podem ser preenchidas de uma só vez, quando enviamos as informações xml
    protected $fillable = ['reserve_id', 'date', 'value'];

    // Uma diária pertence a uma reserva
    public function reserve(){

        return $this->belongsTo(Reserve::class, 'reserve_id');
    }
}
