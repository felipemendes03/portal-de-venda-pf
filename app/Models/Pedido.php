<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $fillable = [
        'id_cadastro_cliente',
        'nm_cliente',
        'vl_total',
        'tp_status',
        'tp_pagamento',
        'ds_observacao',
        'nr_telefone'
    ];
}
