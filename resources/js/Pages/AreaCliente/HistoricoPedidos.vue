<script setup>
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import TopMenu from './TopMenu.vue';
import { ref, onMounted } from 'vue';
import { formatarData } from '@/Utils/DataUtils';
import { formatarMoeda } from '@/Utils/NumeroUtils';

const clienteLogado = ref({})
const historicoPedidos = ref([])

onMounted(() => {
    axios.get(route('api.clientes.show'), {
        headers: {'Authorization': `${localStorage.getItem('token')}`}
    }).then(response => {
        clienteLogado.value = response.data;
    }).catch(error => {
        console.log(error);
    });

    axios.get(route('api.pedidos.visitante.historico'), {
        headers: {'Authorization': `${localStorage.getItem('token')}`}
    }).then(response => {
        historicoPedidos.value = response.data;
    }).catch(error => {
        console.log(error);
    });
})

</script>
<template>
    <Head title="Meus pedidos" />
    <TopMenu :clienteLogado="clienteLogado" v-if="clienteLogado.nome"/>
    <div class="bg-[#12183B] min-h-screen">
        <div class="flex justify-center pt-6" style="padding-top: 40px;">
            <img src="../../Assets/logo45.png" alt="Logo 45 anos Pioneiros da Fé" width="150">
        </div>
        <h1 class="text-center">
            <span class="text-4xl font-bold text-white">Social do Clube Pioneiros da Fé</span>
        </h1>
        <h2 class="text-center text-white mt-4 text-lg">
            Meus pedidos
        </h2>
        <div class="flex justify-center mt-4">
            <div>
                <table class="min-w-full divide-y divide-gray-200" style="padding: 10px;">
                     <tbody>
                        <template v-for="pedido in historicoPedidos.historico" :key="pedido.id">
                            <tr>
                                <td class="text-sm text-gray-900">
                                    <div class="px-2 py-4 mb-6 bg-[#fff] border-2 border-blue-300">
                                        <p class="text-sm text-gray-800 leading-tight">
                                            <span class="font-semibold">#{{ pedido.id }}</span>
                                        </p>
                                        <p class="text-sm text-gray-800 leading-tight">
                                            <span class="font-semibold">Data: {{ formatarData(pedido.created_at_formatted) }}</span>
                                        </p>
                                        <p class="text-sm text-gray-800 leading-tight">
                                            <span class="font-semibold">Valor: {{ formatarMoeda(pedido.vl_total)  }}</span>
                                        </p>
                                        <p class="text-sm text-gray-800 leading-tight">
                                            <span class="font-semibold">Status: {{ pedido.tp_status }}</span>
                                        </p>
                                        <p class="text-sm text-gray-800 leading-tight">
                                            <span class="font-semibold">Forma Pagamento: {{ pedido.tp_pagamento }}</span>
                                        </p>
                                        <p class="text-sm text-gray-800 leading-tight">
                                            <span class="font-semibold">Observação: {{ pedido.ds_observacao }}</span>
                                        </p>
                                        <p class="text-sm text-gray-800 leading-tight font-semibold mt-2">Itens do Pedido:</p>
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
                                </td>
                            </tr>
                        </template>
                        
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>