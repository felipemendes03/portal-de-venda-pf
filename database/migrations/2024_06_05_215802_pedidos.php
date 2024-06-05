<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('nm_cliente');
            $table->text('ds_observacao')->nullable();
            $table->decimal('vl_total', 10, 2);
            $table->enum('tp_status', ['PENDENTE_PAGAMENTO', 'EM_PREPARO', 'AGUARDANDO_RETIRADA', 'ENTREGUE', 'CANCELADO'])->default('PENDENTE_PAGAMENTO');
            $table->enum('tp_pagamento', ['DINHEIRO', 'CARTAO', 'PIX']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
