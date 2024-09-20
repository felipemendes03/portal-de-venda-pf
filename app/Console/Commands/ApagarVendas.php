<?php

namespace App\Console\Commands;

use App\Models\Pedido;
use App\Models\PedidosProduto;
use App\Models\SumupPagamentos;
use Illuminate\Console\Command;

class ApagarVendas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:apagar-vendas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Apagando vendas...');
        SumupPagamentos::all()->each->delete();
        PedidosProduto::all()->each->delete();
        Pedido::all()->each->delete();

        \DB::statement('ALTER TABLE sumup_pagamentos AUTO_INCREMENT = 1');
        \DB::statement('ALTER TABLE pedidos_produtos AUTO_INCREMENT = 1');
        \DB::statement('ALTER TABLE pedidos AUTO_INCREMENT = 1');

        $this->info('Vendas apagadas com sucesso!');
    }
}
