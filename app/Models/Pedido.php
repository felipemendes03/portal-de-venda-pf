<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $fillable = [
        'nm_cliente',
        'vl_total',
        'tp_status',
        'tp_pagamento',
        'ds_observacao',
    ];
}
