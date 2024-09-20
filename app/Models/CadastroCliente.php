<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CadastroCliente extends Model
{

    protected $table = 'cadastro_cliente';
    protected $fillable = [
        'nome',
        'usuario',
        'whatsapp',
        'ativo',
        'fiado',
    ];
    protected $hidden = [
        'password',
        'created_at',
        'updated_at'
    ];
}
