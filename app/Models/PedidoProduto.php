<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoProduto extends Model
{
    protected $fillable = [
        'id_pedido',
        'id_produto',
        'qt_produto',
        'vl_produto',
        'vl_total'
    ];
}
