<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Alert from '@/Components/Alert.vue';
import { ref, onMounted } from 'vue'
import axios from 'axios';
import { formatarMoeda } from '@/Utils/NumeroUtils';


const alertObj = ref({
     alertas: []
});

const produtos=ref([]);
const pedidoForm=ref({});
const fluxoPedido = ref('PEDIDO');

onMounted(()=>{ 
    inicializarPedido();
    listarProdutos();
})

const listarProdutos = () => {  
    axios.get(route('produtos.index', {'orderBy': 'nome'}))
    .then((response)=>{
        produtos.value = response.data.filter(produto => produto.ativo == "S");
    })
    .catch((error)=>{
        addAlerta(error.response.data.message, "error");
    })
}

const calcularTotal = () => {
    return produtos.value.reduce((total, produto) => total + (produto.valor * (produto.quantidade || 0)), 0);
}

const fecharPedido = ( produtos ) => {
    pedidoForm.value.produtos = produtos.filter(produto => produto.quantidade > 0);
    pedidoForm.value.valorTotal = calcularTotal();
    if(pedidoForm.value.produtos.length == 0){
        addAlerta("Selecione ao menos um produto para fechar o pedido!", "warning");
        return;
    }
    pedidoForm.value.valorPago = pedidoForm.value.valorTotal;
    fluxoPedido.value = 'PAGAMENTO';
}

const finalizarPedido = ( pedido ) => {
    let request = {
        "nm_cliente": pedido.nomeCliente,
        "tp_pagamento": pedido.formaPagamento,
        "ds_observacao": pedido.observacao,
        "nr_telefone": pedido.numeroTelefone,
        "itens": pedido.produtos
    }

    axios.post(route('api.pedidos.store'), request)
    .then( (response) => {
        addAlerta("Pedido realizado com suceso!");
        listarProdutos();
        inicializarPedido();
    })
    .catch((error)=>{
        alert(error.response.data.message);
    })
}
const inicializarPedido = () => {
    fluxoPedido.value = 'PEDIDO';
    pedidoForm.value = {
        produtos:[],
        formaPagamento: '',
        nomeCliente: '',
        valorTotal: '0',
        valorPago: '0',
        troco: '0',
        observacao: ''
    }
}

const addAlerta = (mensagem, tipo) => {
    if(tipo == undefined) tipo = 'success';
    alertObj.value.alertas.push({mensagem, tipo});
    setTimeout(() => {
        alertObj.value.alertas.shift();
    }, 5000);
}

</script>

<template>
    <Head title="Loja Pioneiros da Fé"/>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pedidos</h2>
        </template>
        <Alert :alertaObj="alertObj" />
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-2">
                    <div class="m-2 font-semibold text-xl text-gray-800 leading-tight">
                        
                        <Link :href="route('pedidos.historico')">
                            <PrimaryButton>Histórico</PrimaryButton>
                        </Link>
                    </div>
                    <div class="m-2 font-semibold text-xl text-gray-800 leading-tight">Novo pedido</div>
                    <div v-show="fluxoPedido=='PEDIDO'">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-full">
                                            Nome do Produto
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Valor
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            nowrap>
                                            Estoque
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            nowrap>
                                            Quantidade
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Subtotal
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="produto in produtos">
                                        <td class="px-4 text-sm text-gray-900">{{ produto.nome }}</td>
                                        <td class="px-4 text-sm text-gray-900" nowrap>{{ formatarMoeda(produto.valor) }}</td>
                                        <td class="px-4 text-sm text-gray-900">{{ produto.estoqueDisponivel }}</td>
                                        <td class="px-4 text-sm text-gray-900">
                                            <input 
                                            class="w-full"
                                            type="number" min="0" :max="produto.estoqueDisponivel" 
                                            v-model="produto.quantidade" />
                                        </td>
                                        <td class="px-4 text-sm text-gray-900" nowrap>
                                            {{ formatarMoeda(produto.valor * produto.quantidade) }}
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="px-4 text-sm text-gray-900 text-right">Total</td>
                                        <td class="px-4 text-sm text-gray-900">
                                            {{ formatarMoeda(calcularTotal()) }}
                                        </td>
                                    </tr>
                                </tfoot>
                                
                            </table>
                        </div>
                        <div class="my-4 text-end">
                            <PrimaryButton @click="fecharPedido(produtos)">Fechar Pedido</PrimaryButton>
                        </div>
                    </div>
                    <div v-show="fluxoPedido=='PAGAMENTO'">
                        <div class="m-2 font-semibold text-xl text-gray-800 leading-tight">Forma de pagamento</div>
                        
                        <div>
                            <span>Resumo do pedido</span>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-full">
                                                Nome do Produto
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Valor
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                nowrap>
                                                Quantidade
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Subtotal
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="produto in pedidoForm.produtos">
                                            <td class="px-4 text-sm text-gray-900">{{ produto.nome }}</td>
                                            <td class="px-4 text-sm text-gray-900">{{ formatarMoeda(produto.valor) }}</td>
                                            <td class="px-4 text-sm text-gray-900">{{ produto.quantidade }}</td>
                                            <td class="px-4 text-sm text-gray-900">
                                                {{ formatarMoeda(produto.valor * produto.quantidade) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="px-4 text-sm text-gray-900 text-right">Total</td>
                                            <td class="px-4 text-sm text-gray-900">
                                                {{ formatarMoeda(pedidoForm.valorTotal) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="m-2 sm:w-1/2">
                            <div>
                                <InputLabel for="nomeCliente" value="Nome Cliente*"/>
                                <input class="w-full" type="text" name="nomeCliente" id="nomeCliente" v-model="pedidoForm.nomeCliente"/>
                            </div>
                            <div>
                                <InputLabel for="numeroWhatsApp" value="Número do WhatsApp"/>
                                <input class="w-full" type="number" placeholder="11900000000" name="numeroWhatsApp" id="numeroWhatsApp" v-model="pedidoForm.numeroTelefone"/>
                            </div>
                            <div>
                                <InputLabel for="formaPagamento" value="Forma de pagamento*"/>
                                <select class="w-full" name="formaPagamento" id="formaPagamento" v-model="pedidoForm.formaPagamento">
                                    <option value=""></option>
                                    <option value="DINHEIRO">Dinheiro</option>
                                    <option value="CARTAO">Cartão</option>
                                    <option value="PIX">Pix</option>
                                    <option value="FIADO">Fiado</option>
                                </select>
                            </div>
                            <div>
                                <InputLabel for="valorPago" value="Valor pago*"/>
                                <input class="w-full" type="number" min="0" name="valorPago" id="valorPago" v-model="pedidoForm.valorPago"/>
                            </div>
                            <div class="my-4">
                                <span>Troco: {{ formatarMoeda(pedidoForm.valorPago - pedidoForm.valorTotal) }}</span>
                                <br>
                                <span v-if="pedidoForm.valorPago < pedidoForm.valorTotal" class="text-red-500">Valor pago menor que o total</span>
                            </div>
                            <div>
                                <InputLabel for="observacao" value="Observação"/>
                                <input class="w-full" type="text" name="observacao" id="observacao" v-model="pedidoForm.observacao"/>
                            </div>
                            <div class="my-4">
                                <SecondaryButton @click="fluxoPedido='PEDIDO'" class="mr-2">Voltar</SecondaryButton>
                                <PrimaryButton @click="finalizarPedido(pedidoForm)"
                                :disabled="pedidoForm.valorPago < pedidoForm.valorTotal || !pedidoForm.formaPagamento || !pedidoForm.nomeCliente"
                                :class="{ 'cursor-not-allowed': pedidoForm.valorPago < pedidoForm.valorTotal  || !pedidoForm.formaPagamento || !pedidoForm.nomeCliente,
                                          'bg-red-800': pedidoForm.valorPago < pedidoForm.valorTotal || !pedidoForm.formaPagamento || !pedidoForm.nomeCliente
                                 }"
                                >Finalizar Pedido</PrimaryButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
