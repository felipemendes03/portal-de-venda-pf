<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerfisPermissao extends Model
{

    protected $table = "perfis_permissoes";
    public $timestamps = false;
    
    protected $fillable = [
        'id',
        'id_permissao',
        'id_perfil',
    ];
}
