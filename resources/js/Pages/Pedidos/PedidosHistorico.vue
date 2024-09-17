<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue'
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Alert from '@/Components/Alert.vue';
import { formatarMoeda } from '@/Utils/NumeroUtils';
import { formatarData } from '@/Utils/DataUtils';

const alertObj = ref({
     alertas: []
});
const pedidos = ref([]);
const pedidosFiltrados = ref([]);
const tipoStatusTodos = ref('');
const tipoPagamentoFiltrados = ref('');
const q = ref('');

onMounted(()=>{ 
    listarPedidos();
})

const filtrar = () => {
    const searchWords = q.value.toLowerCase().split(" ");
    let filtrados = [];

    searchWords.forEach(word => {
        filtrados = pedidos.value.filter(item =>
            item.nm_cliente.toLowerCase().includes(word) ||
            item.tp_pagamento.toLowerCase().includes(word) ||
            item.tp_status.toLowerCase().includes(word) ||
            item.selecionado
        );
    });

    pedidosFiltrados.value = filtrados;
};

const listarPedidos = () => {  
    axios.get(route('api.pedidos.index'))
    .then((response)=>{
        pedidos.value = response.data;
        pedidos.value.forEach(pedido => {
            pedido.mostrarItens = false;
        });
        pedidosFiltrados.value = pedidos.value;
    })
    .catch((error)=>{
        addAlerta(error.response.data.message, 'error');
    })
}

const subtotal = () => {
    return pedidosFiltrados.value
    .filter(pedido => pedido.selecionado)
    .reduce((acc, item) => (acc*1) + (item.vl_total*1), 0);
};

const marcarModificado = (pedido) => {
    pedido.modificado = true;
    pedido.selecionado = true;
}

const alterarTipoStatusFiltrados = (tipoStatusTodos) => {
    pedidosFiltrados.value.forEach(pedido => {
        if(pedido.selecionado){
            pedido.tp_status = tipoStatusTodos;
            marcarModificado(pedido);
        }
    });
}

const alterarTipoPagamentoFiltrados = (tipoPagamentoTodos) => {
    pedidosFiltrados.value.forEach(pedido => {
        if(pedido.selecionado){
            pedido.tp_pagamento = tipoPagamentoTodos;
            marcarModificado(pedido);
        }
        
    });
}

const salvarAlteracoes = () => {
    let pedidosAlterados = pedidosFiltrados.value.filter(pedido => pedido.modificado);
    let totalAlterados = 0;

    pedidosAlterados.forEach(pedido => {
        axios.put(route('api.pedidos.update', {id: pedido.id}), pedido)
        .then((response)=>{
            totalAlterados++;
            if(totalAlterados == pedidosAlterados.length){
                addAlerta('Pedidos atualizados com sucesso!');
                listarPedidos();
            }
        })
        .catch((error)=>{
            addAlerta(error.response.data.message, 'error');
        })
    });
}

const detalhesPedido = (pedido) => {
    if(pedido.mostrarItens){
        pedido.mostrarItens = false;
        return;
    }
    if(!pedido.itens){
        axios.get(route('api.pedidos.show', {id: pedido.id}))
        .then((response)=>{
            pedido.itens = response.data.itens;
            pedido.dh_pedido = response.data.dh_pedido;
            pedido.mostrarItens = true;

        })
        .catch((error)=>{
            addAlerta(error.response.data.message, 'error');
        })
    }else{
        pedido.mostrarItens = true;
    }
}

const addAlerta = (mensagem, tipo) => {
    if(tipo == undefined) tipo = 'success';
    alertObj.value.alertas.push({mensagem, tipo});
    setTimeout(() => {
        alertObj.value.alertas.shift();
    }, 5000);
}

const toggleSelection = () => {
    const isChecked = event.target.checked;
    pedidosFiltrados.value.forEach(pedido => {
        if(!pedido.modificado){
            pedido.selecionado = isChecked;
        }
    });
}

</script>

<template>
    <Head title="Loja Pioneiros da Fé"/>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pedidos Histórico</h2>
        </template>
        <Alert :alertaObj="alertObj" />
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-2">
                    <Link :href="route('pedidos')">
                        <PrimaryButton>Novo</PrimaryButton>
                    </Link>
                    <div class="mb-2">
                        <input id="pesquisa" class="mt-1 w-full" placeholder="Pesquisar..." v-model="q" v-on:keyup="filtrar">
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="border px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        #
                                        <input type="checkbox" @click="toggleSelection">
                                    </th>
                                    <th scope="col" style="min-width: 300px;" class="border px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Cliente
                                    </th>
                                    <th scope="col" class="border px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total
                                    </th>
                                    <th scope="col" style="min-width: 300px;" class="border px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" style="min-width: 200px;" class="border px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Pagamento
                                    </th>
                                    <th scope="col" class="border px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        
                                    </th>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-4 text-sm text-gray-900 text-right border"></td>
                                    <td class="px-4 text-sm text-gray-900 border">
                                        <select 
                                            :disabled="pedidosFiltrados.filter(p => p.selecionado).length == 0"
                                            class="w-full" v-model="tipoStatusTodos" @change="alterarTipoStatusFiltrados(tipoStatusTodos)">
                                            <option value="PENDENTE_PAGAMENTO">Pendente Pagamento</option>
                                            <option value="EM_PREPARO">Em Preparo</option>
                                            <option value="AGUARDANDO_RETIRADA">Aguardando Retirada</option>
                                            <option value="ENTREGUE">Entregue</option>
                                            <option value="CANCELADO">Cancelado</option>
                                        </select>
                                    </td>
                                    <td class="px-4 text-sm text-gray-900 border">
                                        <select 
                                            :disabled="pedidosFiltrados.filter(p => p.selecionado).length == 0"
                                            class="w-full" v-model="tipoPagamentoFiltrados" @change="alterarTipoPagamentoFiltrados(tipoPagamentoFiltrados)">
                                            <option value="DINHEIRO">Dinheiro</option>
                                            <option value="CARTAO">Cartão</option>
                                            <option value="PIX">Pix</option>
                                            <option value="FIADO">Fiado</option>
                                        </select>
                                    </td>
                                    <td colspan="1" class="px-4 text-sm text-gray-900 border"></td>
                                </tr>
                            </thead>
                            <tbody>
                            <template v-for="pedido in pedidosFiltrados" :key="pedido.id">
                                    <tr :class="pedido.modificado ? 'bg-yellow-100' : ''">
                                        <td class="px-4 text-sm text-gray-900 border">
                                            {{ pedido.id }}
                                            <input :disabled="pedido.modificado" type="checkbox" v-model="pedido.selecionado">
                                        </td>
                                        <td class="px-4 text-sm text-gray-900 border">
                                            <input 
                                                v-if="!pedido.id_cadastro_cliente"
                                                v-model="pedido.nm_cliente" 
                                                @change="marcarModificado(pedido)" 
                                                class="w-full" type="text">
                                            <span v-else>{{ pedido.nm_cliente }}</span>
                                        </td>
                                        <td class="px-4 text-sm text-gray-900 border">{{ formatarMoeda(pedido.vl_total) }}</td>
                                        <td class="px-4 text-sm text-gray-900 border">
                                            <select class="w-full" v-model="pedido.tp_status" @change="marcarModificado(pedido)">
                                                <option value="PENDENTE_PAGAMENTO">Pendente Pagamento</option>
                                                <option value="EM_PREPARO">Em Preparo</option>
                                                <option value="AGUARDANDO_RETIRADA">Aguardando Retirada</option>
                                                <option value="ENTREGUE">Entregue</option>
                                                <option value="CANCELADO">Cancelado</option>
                                            </select>
                                        </td>
                                        <td class="px-4 text-sm text-gray-900 border">
                                            <select class="w-full" v-model="pedido.tp_pagamento" @change="marcarModificado(pedido)">
                                                <option value="DINHEIRO">Dinheiro</option>
                                                <option value="CARTAO">Cartão</option>
                                                <option value="PIX">Pix</option>
                                                <option value="FIADO">Fiado</option>
                                            </select>
                                        </td>
                                        <td class="px-4 text-sm text-gray-900 border">
                                            <PrimaryButton @click="detalhesPedido(pedido)">...</PrimaryButton>
                                        </td>
                                    </tr>
                                    <tr v-if="pedido.mostrarItens" class="">
                                        <td colspan="6" class="text-sm text-gray-900">
                                        <div class="px-2 py-4 mb-6 bg-blue-50 border-2 border-blue-300">
                                                <h3 class="font-semibold text-lg text-gray-800 leading-tight mt-2">Detalhes</h3>
                                                <p class="text-sm text-gray-800 leading-tight"><span class="font-semibold">Observação: </span>
                                                    <input type="text" v-model="pedido.ds_observacao" @change="marcarModificado(pedido)" class="w-full">
                                                </p>
                                                <p class="text-sm text-gray-800 leading-tight"><span class="font-semibold">WhatsApp: </span>
                                                    <input type="number" placeholder="5511900000000" v-model="pedido.nr_telefone" @change="marcarModificado(pedido)" class="w-full">
                                                </p>
                                                <p class="text-sm text-gray-800 leading-tight font-semibold mt-2">Itens do Pedido:</p>
                                                <p class="text-sm text-gray-800 leading-tight">
                                                <span class="font-semibold">Data Criação: </span>
                                                {{ formatarData(pedido.dh_pedido) }}
                                                </p>
                                                <div class="overflow-x-auto">
                                                    <table class="min-w-full divide-y divide-gray-200 my-2">
                                                        <thead>
                                                            <tr>
                                                                <th scope="col" class="border text-left text-xs font-medium uppercase tracking-wider">
                                                                    Produto
                                                                </th>
                                                                <th scope="col" class="border text-left text-xs font-medium uppercase tracking-wider">
                                                                    Quantidade
                                                                </th>
                                                                <th scope="col" class="border text-left text-xs font-medium uppercase tracking-wider">
                                                                    Valor Unitário
                                                                </th>
                                                                <th scope="col" class="border text-left text-xs font-medium uppercase tracking-wider">
                                                                    Valor Total
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr v-for="item in pedido.itens" :key="item.id">
                                                                <td class="px-4 text-sm text-gray-900 border"> {{ item.nm_produto }} </td>
                                                                <td class="px-4 text-sm text-gray-900 border">{{ item.qt_produto }}</td>
                                                                <td class="px-4 text-sm text-gray-900 border">{{ formatarMoeda(item.vl_produto) }}</td>
                                                                <td class="px-4 text-sm text-gray-900 border">{{ formatarMoeda(item.vl_total) }}</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                        </div>
                                        </td>
                                    </tr>
                            </template>
                            
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2" class="px-4 text-sm text-gray-900 text-right border"></td>
                                    <td class="p-4 text-gray-900 border">
                                        {{ formatarMoeda(subtotal()) }}
                                    </td>
                                    <td colspan="3" class="px-4 text-sm text-gray-900 border"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="mt-2 text-right">
                        <PrimaryButton @click="salvarAlteracoes()">Salvar</PrimaryButton>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
