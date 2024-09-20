<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue'
import PrimaryButton from '@/Components/PrimaryButton.vue';


const pedidos = ref([]);
const _setTimeout = ref(null);

const props = defineProps({
    nomeStatus: {
        type: String,
        required: true
    },
    nomeBotao: {
        type: String,
        required: true
    },
    nomePagina: {
        type: String,
        required: true
    },
    nomeProximaAcao: {
        type: String,
        required: true
    }
});

onMounted(()=>{ 
    listarPedidos();
})

onUnmounted(()=>{ 
    clearTimeout(_setTimeout.value);
})


const listarPedidos = () => {  
    axios.get(route('api.pedidos.index', {
        status: props.nomeStatus,
        comItens: true,
        orderBy: 'updated_at',
    }))
    .then((response)=>{
        pedidos.value = response.data;
    })
    .catch((error)=>{
        alert(error.response.data.message);
    })
    _setTimeout.value = setTimeout(listarPedidos, 5000);
}

const proximoEstagio = (pedido) => {
    pedido.tp_status = props.nomeProximaAcao;
    pedido.click = true
    axios.put(route('api.pedidos.update', pedido.id), pedido)
    .then((response)=>{
        listarPedidos();
    })
    .catch((error)=>{
        alert(error.response.data.message);
    })
}

</script>

<template>
    <Head title="Loja Pioneiros da Fé"/>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ nomePagina }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-2">
                    <div>
                        <div class="grid sm:grid-cols-3 lg:grid-cols-4 gap-4">
                            <div v-for="pedido in pedidos" :key="pedido.id" class="flex flex-col p-4 border-4 bg-blue-200 border-blue-900">
                                <div>
                                    <h3 class="text-lg font-semibold text-blue-900">Pedido #{{ pedido.id }}</h3>
                                    <div class="w-full font-semibold bg-blue-300 p-2">
                                        {{ pedido.nm_cliente }}
                                    </div>
                                    <ul>
                                        <li v-for="item in pedido.itens" :key="item.id">
                                        <span class="text-lg font-semibold">{{ item.qt_produto }} x {{ item.nm_produto }}</span>
                                        </li>
                                    </ul>
                                    <div v-if="pedido.ds_observacao" class="bg-red-200 mt-4 p-2 mb-2">
                                        <span class="text-lg font-semibold">Obs.: {{ pedido.ds_observacao }}</span>
                                    </div>
                                </div>
                                <div class="flex justify-end mt-auto">
                                <PrimaryButton v-show="!pedido.click" @click="proximoEstagio(pedido)" class="w-full text-center">{{nomeBotao}}</PrimaryButton>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
