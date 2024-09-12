<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SumupPagamentos extends Model
{
    protected $table = 'sumup_pagamentos';
    protected $fillable = ['id_pedido', 'id_checkout_reference', 'tp_situacao'];
    protected $hidden = ['created_at', 'updated_at'];

}
