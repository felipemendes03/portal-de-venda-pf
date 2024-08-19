<script setup>
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
        <!--
        { "historico": [ { "id": 19, "id_cadastro_cliente": 1, "cadastro_cliente": null, "nm_cliente": "Danilo", "ds_observacao": null, "vl_total": "10.50", "tp_status": "PENDENTE_PAGAMENTO", "tp_pagamento": "CARTAO", "created_at": "2024-08-19T11:22:49.000000Z", "updated_at": "2024-08-19T11:22:49.000000Z" } ] }
        -->
        <div class="flex justify-center mt-4">
            <div class="w-1/2">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left">Data</th>
                            <th class="text-left">Valor</th>
                            <th class="text-left">Status</th>
                            <th class="text-left">Pagamento</th>
                        </tr>
                    </thead>
                    <tbody class="bg-gray-200">
                        <tr v-for="pedido in historicoPedidos.historico" :key="pedido.id">
                            <td>{{ formatarData(pedido.created_at_formatted) }}</td>
                            <td>{{ formatarMoeda(pedido.vl_total) }}</td>
                            <td>{{ pedido.tp_status }}</td>
                            <td>{{ pedido.tp_pagamento }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>