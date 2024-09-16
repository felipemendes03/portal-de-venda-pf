<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { formatarMoeda } from '@/Utils/NumeroUtils';

const dashboard = ref([]);

onMounted(() => {
   axios.get(route('api.dashboard.index'))
       .then(response => {
        dashboard.value = response.data;
       })
       .catch(error => console.log('Error:', error));
});

</script>

<template>
    <Head title="Pioneiros da Fé" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Seja bem vindo!</h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                      <div class="grid grid-cols-3 gap-4">
                            <div class="bg-pink-800 text-white p-2 border rounded-lg">Valor total de vendas
                                <p>
                                    {{ formatarMoeda(dashboard.vl_total_vendas) }}
                                </p>
                            </div>
                            <div>
                                <div class="bg-green-800 text-white p-2 border rounded-lg">Quantidade de Pedidos
                                    <p>
                                        {{ dashboard.nr_pedidos }}
                                    </p>
                                </div>
                            </div>
                            <div>
                                <div class="bg-blue-800 text-white p-2 border rounded-lg">Aguardando Pagamento
                                    <p>
                                        {{ dashboard.nr_aguardando_pagamento }}
                                    </p>
                                </div>
                            </div>
                            <div>
                                <div class="bg-yellow-800 text-white p-2 border rounded-lg">Em preparo
                                    <p>
                                        {{ dashboard.nr_em_preparo }}
                                    </p>
                                </div>
                            </div>
                            <div>
                                <div class="bg-red-800 text-white p-2 border rounded-lg">Aguardando Retirada
                                    <p>
                                        {{ dashboard.nr_aguardando_retirada }}
                                    </p>
                                </div>
                            </div>
                            <div>
                                <div class="bg-gray-800 text-white p-2 border rounded-lg">Concluídos
                                    <p>
                                        {{ dashboard.nr_concluidos }}
                                    </p>
                                </div>
                            </div>
                            <div>
                                <div class="bg-green-500 text-white p-2 border rounded-lg">Pago em Dinheiro
                                    <p>
                                        {{ formatarMoeda(dashboard.vl_pago_dinheiro) }}
                                    </p>
                                </div>
                            </div>
                            <div>
                                <div class="bg-purple-500 text-white p-2 border rounded-lg">Pago no Cartão
                                    <p>
                                        {{ formatarMoeda(dashboard.vl_pago_cartao) }}
                                    </p>
                                </div>
                            </div>
                            <div>
                                <div class="bg-blue-500 text-white p-2 border rounded-lg">Pago no PIX
                                    <p>
                                        {{ formatarMoeda(dashboard.vl_pago_pix) }}
                                    </p>
                                </div>
                            </div>
                            <div>
                                <div class="bg-red-900 text-white p-2 border rounded-lg">Fiado
                                    <p>
                                        {{ formatarMoeda(dashboard.vl_fiado) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="p-6 text-gray-900">
                            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Total de Vendas</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="m-4">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                            Produto
                                        </th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                            Quantidade
                                        </th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                            Estoque Inicial
                                        </th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                            Estoque Atual
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="produto in dashboard.produtos">
                                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                            <div class="text-sm leading-5 font-medium text-gray-900">{{ produto.nome }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                            <div class="text-sm leading-5 text-gray-900">{{ produto.total }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                            <div class="text-sm leading-5 text-gray-900">{{ produto.estoque }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                            <div class="text-sm leading-5 text-gray-900">{{ produto.estoque - produto.total }}</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
