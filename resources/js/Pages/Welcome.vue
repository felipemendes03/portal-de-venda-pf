<script setup>
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, onMounted } from 'vue';
import { formatarMoeda } from '@/Utils/NumeroUtils';
import { Link } from '@inertiajs/vue3';
import ContentCopy from 'vue-material-design-icons/ContentCopy.vue';

const props = defineProps({
    pedidoId: {
        type: Number,
    }
});

const mostrarAlertaPixCopiado = ref(false);
const produtos = ref([]);
const valorTotal = ref(0);
const pedido = ref({
    nome: '',
    observacao: '',
    formaPagamento: '',
    itens: []
});
const pedidoFeito = ref({});

const estagioAtual = ref(1);
const estagiosPedido = ref([
    { id: 1, nome: 'Nome', podeProximo: () =>  pedido.value.nome.length > 5},
    { id: 2, nome: 'Escolha os itens do seu pedido', podeProximo: () =>  produtos.value.filter(produto => produto.quantidade > 0).length > 0},
    { id: 3, nome: 'Alguma Observação?', podeProximo: () =>  true},
    { id: 4, nome: 'Forma de pagamento', podeProximo: () =>  pedido.value.formaPagamento !== '' },
    { id: 5, nome: 'Revise seu pedido', podeProximo: () =>  true},
]);

const navegarEstagio = (id) => {
    estagioAtual.value = id;
}

onMounted(() => {

   if(props.pedidoId > 0) {
       axios.get(route('api.pedidos.visitante.show', props.pedidoId))
       .then(response => {
        pedidoFeito.value = response.data;
        estagioAtual.value = 0;
       })
       .catch(error => {
           console.log(error);
       });
   }else{
        axios.get(route('api.pedidos.visitante.index'))
        .then(response => {
            produtos.value = response.data.produtos.map(produto => {
                return {
                    id: produto.id,
                    nome: produto.nome,
                    valor: produto.valor,
                    quantidade: 0
                };
            });
        })
        .catch(error => {
            console.log(error);
        });
   }
});

const addProduto = (produto, quantidade) => {
    produto.quantidade += quantidade;
    calcularTotal(produto);
}

const calcularTotal = (produto) => {
    ajustarQuantidade(produto);
    let total = 0;
    produtos.value.forEach(produto => {
        total += produto.valor * produto.quantidade;
    });
    valorTotal.value = total;
}

const enviarPedido = () => {
    pedido.value.itens = produtos.value.filter(produto => produto.quantidade > 0);
    axios.post(route('api.pedidos.visitante.store'), pedido.value)
    .then(response => {
        document.location.href =  "/visitante/pedido/" + response.data.id;
    })
    .catch(error => {
        console.log(error);
    });
}

const ajustarQuantidade = (produto) => {
    if (produto.quantidade < 0) {
        produto.quantidade = 0;
    }
    if (produto.quantidade > 10) {
        produto.quantidade = 10;
    }
}

const copiarChavePix = () => {
    mostrarAlertaPixCopiado.value = true;
    setTimeout(() => {
        mostrarAlertaPixCopiado.value = false;
    }, 3000);
    navigator.clipboard.writeText('pioneirosdafeaps@gmail.com');
}

</script>

<template>
    <Head title="Welcome" />
    <div class="bg-[#12183B] min-h-screen">
        <div class="flex justify-center pt-6">
            <img src="../Assets/logo45.png" alt="Logo 45 anos Pioneiros da Fé" width="150">
        </div>
        <div v-if="mostrarAlertaPixCopiado" class="flex justify-center pt-6 fixed top-0 w-full" style="z-index: 1000;">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                Pix copiado para a área de transferência
            </div>
        </div>
        <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl mx-auto">
            <main class="mt-6 center">
                <h1 class="text-center">
                    <span class="text-4xl font-bold text-white">Social para comeorar o </span>
                    <span class="text-4xl font-bold text-[#FFD700]">45º Aniversário </span>
                    <span class="text-4xl font-bold text-white">do Clube Pioneiros da Fé</span>
                </h1>
                <h2 class="text-center text-white mt-4 text-lg">
                    Serviço de autoatendimento
                </h2>
                <div class="justify-center mt-6 bg-[#12188B] p-2" v-if="estagioAtual === 0">
                    <div class="text-white text-center block mt-4">
                        Seu pedido foi realizado com sucesso!
                    </div>
                    <div class="text-white text-center block mt-4 text-lg">
                        Seu número de pedido é: {{ pedidoFeito.id }}
                    </div>
                    <div class="text-white text-center block mt-4" v-if="pedidoFeito.tp_status=='PENDENTE_PAGAMENTO'">
                        Para finalizar seu pedido, efetue o pagamento no valor de {{ formatarMoeda(pedidoFeito.vl_total) }} no caixa. <br>
                        <div class="mt-4 text-center">
                                <span class="text-lg">Para adiantar, você pode pagar via PIX para a chave pix abaixo e apresentar o comprovante no caixa.</span>
                                <p class="bg-white text-black p-2 m-2 flex flex-row items-center justify-center rounded-lg">
                                   pioneirosdafeaps@gmail.com 
                                    <ContentCopy class="ml-2 cursor-pointer" title="Copiar chave" @click="copiarChavePix()"></ContentCopy>
                                </p>
                        </div>
                    </div>
                    <div v-else class="text-white text-center block mt-4">
                        O status do seu pedido é: {{ pedidoFeito.tp_status }}
                    </div>
                    <div class="mt-4 text-center">
                        <Link :href="route('welcome')" class="w-full bg-[#FFD700] text-[#12183B] py-2 rounded-lg">
                            <div class="w-full bg-[#FFD700] text-[#12183B] py-2 rounded-lg">
                                Fazer outro pedido
                            </div>
                        </Link>
                    </div>
                </div>
                <div class="mt-6" v-if="estagioAtual === 1">
                    <input v-model="pedido.nome" type="text" class="w-full px-4 py-2 text-black rounded-lg" placeholder="Digite seu nome"> 
                    <div class="text-white text-center block mt-4" v-if="pedido.nome.length > 0 && pedido.nome.length < 6">
                        Falta(m) pelo menos {{ 6 - pedido.nome.length }} caractere(s)
                    </div>
                </div>
                <div v-if="estagioAtual === 2">
                    <span class="text-white text-center block mt-4">
                        Escolha os itens do seu pedido
                    </span>
                    <ul>
                        <li v-for="produto in produtos" :key="produto.id">
                            <div class="flex justify-between items-center bg-white text-[#12183B] py-2 mt-4 rounded-lg">
                                <span class="px-4">{{ produto.nome }} - {{ formatarMoeda(produto.valor) }}</span>
                                <div class="flex justify-between items-center bg-white text-[#12183B] py-2 mt-4 rounded-lg">
                                    <button class="m-2 px-3 py-2 rounded-lg bg-[#FFD700]" @click="addProduto(produto, -1)">-</button>
                                    <input type="number" v-model="produto.quantidade" class="rounded-lg" min="0" max="10" value="0" @change="calcularTotal(produto)">
                                    <button class="m-2 px-3 py-2 rounded-lg bg-[#FFD700]" @click="addProduto(produto, 1)" >+</button>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="flex justify-between items-center bg-white text-[#12183B] py-2 mt-4 rounded-lg">
                                <span class="px-4">Total</span>
                                <span class="px-4">{{ formatarMoeda(valorTotal) }}</span>
                            </div>
                        </li>
                    </ul>
                </div>
                <div v-else-if="estagioAtual === 3">
                    <span class="text-white text-center block mt-4">
                        Alguma Observação?
                    </span>
                    <textarea v-model="pedido.observacao" class="w-full px-4 py-2 text-black rounded-lg" placeholder="Digite aqui sua observação"></textarea>
                </div>
                <div v-else-if="estagioAtual === 4">
                    <span class="text-white text-center block mt-4">
                        Forma de pagamento
                    </span>
                    <div class="flex justify-between items-center bg-white text-[#12183B] py-2 mt-4 rounded-lg">
                        <span class="px-4">Dinheiro</span>
                        <input type="radio" name="formaPagamento" v-model="pedido.formaPagamento" value="DINHEIRO" class="m-2 px-4 py-2 rounded-lg">
                    </div>
                    <div class="flex justify-between items-center bg-white text-[#12183B] py-2 mt-4 rounded-lg">
                        <span class="px-4">Pix</span>
                        <input type="radio" name="formaPagamento" v-model="pedido.formaPagamento" value="PIX" class="m-2 px-4 py-2 rounded-lg">
                    </div>
                    <div class="flex justify-between items-center bg-white text-[#12183B] py-2 mt-4 rounded-lg">
                        <span class="px-4">Cartão</span>
                        <input type="radio" name="formaPagamento" v-model="pedido.formaPagamento" value="CARTAO" class="m-2 px-4 py-2 rounded-lg">
                    </div>
                    <div v-show="false" class="flex justify-between items-center bg-white text-[#12183B] py-2 mt-4 rounded-lg">
                        <span class="px-4">Fiado <span class="text-sm">(Pagar no final da Social)</span></span>
                        <input type="radio" name="formaPagamento" v-model="pedido.formaPagamento" value="FIADO" class="m-2 px-4 py-2 rounded-lg">
                    </div>
                </div>
                <div v-else-if="estagioAtual === 5">
                    <span class="text-white text-center block mt-4">
                        Revise seu pedido
                    </span>
                    <ul>
                        <li>
                            <div class="flex justify-between items-center bg-white text-[#12183B] py-2 mt-4 rounded-lg">
                                <span class="px-4">Seu nome: {{ pedido.nome }}</span>
                            </div>
                        </li>
                        <li v-for="produto in produtos" :key="produto.id">
                            <div v-if="produto.quantidade > 0" class="flex justify-between items-center bg-white text-[#12183B] py-2 mt-4 rounded-lg">
                                <span class="px-4">{{ produto.quantidade }} x {{ produto.nome }}</span>
                                <span class="px-4">{{ formatarMoeda(produto.valor * produto.quantidade) }}</span>
                                
                            </div>
                        </li>
                        <li>
                            <div class="flex justify-between items-center bg-white text-[#12183B] py-2 mt-4 rounded-lg">
                                <span class="px-4">Total</span>
                                <span class="px-4">{{ formatarMoeda(valorTotal) }}</span>
                            </div>
                        </li>
                        <li>
                            <div class="flex justify-between items-center bg-white text-[#12183B] py-2 mt-4 rounded-lg">
                                <span class="px-4">Observação: {{ pedido.observacao }} </span>
                            </div>
                        </li>
                        <li>
                            <div class="flex justify-between items-center bg-white text-[#12183B] py-2 mt-4 rounded-lg">
                                <span class="px-4">Forma de pagamento: {{ pedido.formaPagamento }} </span>
                            </div>
                        </li>
                    </ul>
                </div>
                <div>
                    <button @click="navegarEstagio(estagioAtual-1)" class="w-full bg-[#FFD700] text-[#12183B] py-2 mt-4 rounded-lg" v-if="estagioAtual > 1">Voltar</button>
                    <button @click="navegarEstagio(estagioAtual+1)" class="w-full bg-[#FFD700] text-[#12183B] py-2 mt-4 rounded-lg mb-6" v-if="estagioAtual < (estagiosPedido.length) && estagiosPedido.filter(e => e.id === estagioAtual && e.podeProximo() ).length > 0">Avançar</button>
                    <button class="w-full bg-[#FFD700] text-[#12183B] py-2 mt-4 rounded-lg mb-6" v-if="estagioAtual === (estagiosPedido.length)" @click="enviarPedido">Enviar Pedido</button>
                </div>
            </main>
        </div>
    </div>
</template>
