<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class Perfil extends Model
{

    protected $table = "perfis";
    public $timestamps = false;
    
    protected $fillable = [
        'id',
        'nm_perfil'
    ];
}
