<script setup>
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, onMounted } from 'vue';
import { formatarMoeda } from '@/Utils/NumeroUtils';
import { Link } from '@inertiajs/vue3';
import ContentCopy from 'vue-material-design-icons/ContentCopy.vue';
import TopMenu from './AreaCliente/TopMenu.vue';

const props = defineProps({
    pedidoId: {
        type: Number,
    }
});

const desabilitarBotaoPagamento = ref(false);
const mostrarAlertaPixCopiado = ref(false);
const mostrarAlertaErro = ref(false);
const mensagemDeErro =  ref('');
const produtos = ref([]);
const valorTotal = ref(0);
const clienteLogado = ref({})
const pedido = ref({
    nome: '',
    observacao: '',
    formaPagamento: '',
    itens: []
});

const cadastro = ref({
    nome: '',
    cpf: '',
    senha: '',
    confirmacaoSenha: ''
});

const pedidoFeito = ref({});

const estagioAtual = ref(1);
const showPassword = ref(false);

const ESTAGIO_INICIO = 1;
const ESTAGIO_VALIDAR_CADASTRO = 1.1;
const ESTAGIO_CADASTRO = 1.2;
const ESTAGIO_LOGIN = 1.3;
const ESTAGIO_NOME = 2;
const ESTAGIO_PRODUTOS = 3;
const ESTAGIO_OBSERVACAO = 4;
const ESTAGIO_FORMA_PAGAMENTO = 5;
const ESTAGIO_REVISAO = 6;

const estagiosPedido = ref([
    { id: ESTAGIO_NOME, nome: 'Nome', podeProximo: () =>  pedido.value.nome.replace(/\s/g, '').length > 5, podeVoltar: () =>  true},
    { id: ESTAGIO_PRODUTOS, nome: 'Escolha os itens do seu pedido', podeProximo: () =>  produtos.value.filter(produto => produto.quantidade > 0).length > 0, podeVoltar: () =>  !clienteLogado.value.nome },
    { id: ESTAGIO_OBSERVACAO, nome: 'Alguma Observação?', podeProximo: () =>  true, podeVoltar: () =>  true},
    { id: ESTAGIO_FORMA_PAGAMENTO, nome: 'Forma de pagamento', podeProximo: () =>  pedido.value.formaPagamento !== '' , podeVoltar: () =>  true},
    { id: ESTAGIO_REVISAO, nome: 'Revise seu pedido', podeProximo: () =>  true, podeVoltar: () =>  true},
]);

const podeCadastro = () => {
    return cadastro.value.nome.replace(/\s/g, '').length > 5 
        && cadastro.value.senha.replace(/\s/g, '').length > 5 
        && validarCpf(cadastro.value.cpf)
        && (cadastro.value.senha === cadastro.value.confirmacaoSenha || showPassword.value);
}

const efetuarCadastro = () => {
    axios.post(route('api.clientes.store'), cadastro.value)
    .then(response => {
        let token = response.data.token;
        let tokenType = response.data.token_type;
        localStorage.setItem('token', tokenType + ' ' + token);
    })
    .catch(error => {
        console.log(error);
    });
}

const efetuarLogin = () => {
    axios.post(route('api.clientes.login'), cadastro.value)
    .then(response => {
        let token = response.data.token;
        let tokenType = response.data.token_type;
        localStorage.setItem('token', tokenType + ' ' + token);
        window.location.reload();
    })
    .catch(error => {
        mostrarAlertaErro.value = true;
        if(error.response.status == 401){
            mostrarMensagemErro(error.response.data.error);
        }else{
            mostrarMensagemErro("Houve um erro ao tentar efetuar o login.");
        }
        console.log(error);
    });
}

const mostrarMensagemErro = (mensagem) => {
    mostrarAlertaErro.value = true;
    mensagemDeErro.value = mensagem;
    setTimeout(() => {
        mostrarAlertaErro.value = false;
        mensagemDeErro.value = '';
    }, 3000);
}

const navegarEstagio = (id) => {
    if(estagioAtual.value > id){
        id = Math.ceil(id);
    }
    estagioAtual.value = id;
}

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
}

onMounted(() => {
    axios.get(route('api.clientes.show'), {
        headers: {
            'Authorization': `${localStorage.getItem('token')}`
        }
    })
    .then(response => {
        estagioAtual.value = ESTAGIO_PRODUTOS;
        clienteLogado.value = response.data;
    })
    .catch(error => {
        console.log(error);
    });


   if(props.pedidoId > 0) {
        carregarStatusPedido();
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


const carregarStatusPedido = () => {
    axios.get(route('api.pedidos.visitante.show', props.pedidoId))
       .then(response => {
        pedidoFeito.value = response.data;
        estagioAtual.value = 0;
       })
       .catch(error => {
           console.log(error);
       });
}
const pagarComCartao = () => {

    desabilitarBotaoPagamento.value = true;

    axios.post(route('api.sumup.checkout', {pedidoId: pedidoFeito.value.id}))
    .then(response => {
        const sumupCard = SumUpCard.mount({
            id: 'sumup-card',
            checkoutId: response.data.id,
            title: 'Pioneiros da Fé',
            currency: 'BRL',
            locale: 'pt-BR',
            country: 'BR',
            onLoad: () => {
                desabilitarBotaoPagamento.value = false;
            },
            onResponse: (type, body) => {
                if(type === 'success'){
                    sumupCard.unmount();
                    if( body.status === 'PAID'){
                            axios.post(route('api.sumup.confirmar-pagamento', {pedidoId: pedidoFeito.value.id}))
                        .then(response => {
                            alert('Pagamento confirmado com sucesso');
                        })
                        .catch(error => {
                            mostrarMensagemErro(error.response.data.message);
                        })
                        .finally(() => {
                            carregarStatusPedido();
                        });
                    } else {
                        carregarStatusPedido();
                        mostrarMensagemErro('Houve um erro ao tentar confirmar o pagamento');
                    }
                }
            },
        });
    })
    .catch(error => {
       mostrarMensagemErro(error.response.data.message);
    })
    .finally(() => {
        carregarStatusPedido();
    });
}

const addProduto = (produto, quantidade) => {
    produto.quantidade += quantidade;
    calcularTotal(produto);
}

const calcularTotal = (produto) => {
    if(produto){
        ajustarQuantidade(produto);
    }

    let total = 0;
    produtos.value.forEach(p => {
        total += p.valor * p.quantidade;
    });
    valorTotal.value = total;
}

const enviarPedido = () => {
    pedido.value.itens = produtos.value.filter(produto => produto.quantidade > 0);
    axios.post(route('api.pedidos.visitante.store'),
        {
            pedido: pedido.value
        },
        {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        }
    ).then(response => {
        document.location.href =  "/visitante/pedido/" + response.data.id;
    })
    .catch(error => {
        if(error.response.status == 422){
            let msg = error.response.data.message;
            error.response.data.itens.forEach(item =>{
                msg += "\n" + item;
            })
            mostrarMensagemErro(msg);
            produtos.value = produtos.value.filter(produto => error.response.data.itens.indexOf(produto.nome) < 0);
            estagioAtual.value = 2;
            calcularTotal();
        } else {
            mostrarMensagemErro("Houve um erro ao tentar enviar o pedido.");
        }
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

const temAcaiNoCopo = () => {
    return produtos.value.filter(produto => produto.quantidade > 0 && produto.nome.indexOf('Açaí no copo') > -1).length > 0;
}

const obterAcompanhamentosDoAcai = () => {
    let acaiNoCopo = produtos.value.filter(produto => produto.quantidade > 0 && produto.nome.indexOf('Açaí no copo') > -1);
    return acaiNoCopo[0].nome.split(":")[1].replace(" e ",",").split(",");
}

const addObservacaoRemoverItemDoAcaiNoCopo = (acompanhamento) => {
    if(pedido.value.observacao.indexOf('Açaí sem') === -1){
        pedido.value.observacao += ' Açaí';
    }
    pedido.value.observacao += ' sem ' + acompanhamento.trim() + ';';
}

const avancarCadastro = () => {
    verificarSeCadastroExiste().then(existe => {
        if(existe){
            estagioAtual.value = ESTAGIO_LOGIN;
        }else{
            estagioAtual.value = ESTAGIO_CADASTRO;
        }
    });
}

const validarCpf = (cpf) => {
    cpf = cpf.replace(/[^\d]+/g, '');

    if (cpf.length !== 11) {
        return false;
    }

    if (/^(\d)\1+$/.test(cpf)) {
        return false;
    }

    let soma = 0;
    for (let i = 0; i < 9; i++) {
        soma += parseInt(cpf.charAt(i)) * (10 - i);
    }
    let resto = 11 - (soma % 11);
    let digito1 = (resto === 10 || resto === 11) ? 0 : resto;

    soma = 0;
    for (let i = 0; i < 10; i++) {
        soma += parseInt(cpf.charAt(i)) * (11 - i);
    }
    resto = 11 - (soma % 11);
    let digito2 = (resto === 10 || resto === 11) ? 0 : resto;

    return digito1 === parseInt(cpf.charAt(9)) && digito2 === parseInt(cpf.charAt(10));
}

const verificarSeCadastroExiste = () => {
    return axios.post(route('api.clientes.verificar-castrado'), {cpf: cadastro.value.cpf})
    .then(response => {
        return response.data.existe;
    })
    .catch(error => {
        console.log(error);
    });
}

</script>

<template>
    <Head title="Welcome" />
    <TopMenu :clienteLogado="clienteLogado" v-if="clienteLogado.nome"/>
    <div class="bg-[#12183B] min-h-screen">
        <div class="flex justify-center pt-6" style="padding-top: 40px;">
            <img src="../Assets/logo45.png" alt="Logo 45 anos Pioneiros da Fé" width="150">
        </div>
        <div v-if="mostrarAlertaPixCopiado" class="flex justify-center pt-6 fixed top-0 w-full" style="z-index: 1000;">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                Pix copiado para a área de transferência
            </div>
        </div>
        <div v-if="mostrarAlertaErro" class="flex justify-center pt-6 fixed top-0 w-full" style="z-index: 1000;">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                {{ mensagemDeErro }}
            </div>
        </div>
        <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl mx-auto">
            <main class="mt-6 center">
                <h1 class="text-center">
                    <span class="text-4xl font-bold text-white">Social do Clube Pioneiros da Fé</span>
                </h1>
                <h2 class="text-center text-white mt-4 text-lg">
                    Serviço de autoatendimento
                </h2>
                <div class="justify-center mt-6 bg-[#12188B] p-2" v-if="estagioAtual === 0">
                    <div class="text-center block mt-1  mt-1 bg-blue-100 p-2 rounded-lg">
                        Seu pedido foi realizado com sucesso!
                    </div>
                    <div class="text-center block mt-1  mt-1 bg-blue-100 p-2 rounded-lg text-lg">
                        Seu número de pedido é: {{ pedidoFeito.id }}
                    </div>
                    <div class="text-white text-center block mt-1  mt-1 bg-red-700 p-2 rounded-lg" v-if="pedidoFeito.tp_status=='PENDENTE_PAGAMENTO'">
                        Para finalizar seu pedido, efetue o pagamento no valor de {{ formatarMoeda(pedidoFeito.vl_total) }} no caixa ou use a opção para pagar online. <br>

                        <div v-if="pedidoFeito.tp_pagamento=='CARTAO'">
                            <button @click="pagarComCartao()" 
                                class="w-full bg-[#FFD700] text-[#12183B] py-2 mt-4 rounded-lg"
                                :disabled="desabilitarBotaoPagamento"
                                :class="{ 'cursor-not-allowed bg-[#ccc]': desabilitarBotaoPagamento }"
                                >
                                Pagar com cartão de crédito online
                            </button>
                            <div style="background-color: blanchedalmond;" id="sumup-card"></div>
                        </div>
                        <div class="mt-4 text-center" v-else>
                                <span class="text-lg">Para adiantar, você pode pagar via PIX para a chave pix abaixo e apresentar o comprovante no caixa.</span>
                                <p class="bg-white text-black p-2 m-2 flex flex-row items-center justify-center rounded-lg">
                                   pioneirosdafeaps@gmail.com 
                                    <ContentCopy class="ml-2 cursor-pointer" title="Copiar chave" @click="copiarChavePix()"></ContentCopy>
                                </p>
                        </div>
                    </div>
                    <div v-else class="text-white text-center block mt-1  mt-1 bg-red-700 p-2 rounded-lg">
                        O status do seu pedido é: {{ pedidoFeito.tp_status }}
                    </div>
                    <div class="mt-4 text-center">
                        <Link :href="route('welcome')">
                            <div class="w-full bg-[#FFD700] text-[#12183B] py-2 rounded-lg">
                                Fazer outro pedido
                            </div>
                        </Link>
                        <Link :href="route('meus-pedidos')" v-if="clienteLogado.nome">
                            <div class="w-full bg-[#FFD700] text-[#12183B] py-2 mt-2 rounded-lg">
                                Acompanhar pedidos
                            </div>
                        </Link>
                    </div>
                </div>
                <div class="mt-6" v-if="estagioAtual === ESTAGIO_INICIO">
                    <span class="text-white">
                        Bem vindo(a) ao nosso serviço de autoatendimento. <br>
                        Você pode se cadastrar para acompnhar todos os seus pedidos ou fazer um pedido sem se cadastrar.
                    </span>
                    <button class="w-full bg-[#FFD700] text-[#12183B] py-2 mt-4 rounded-lg" @click="navegarEstagio(ESTAGIO_NOME)">
                        Fazer pedido sem se cadastrar
                    </button>
                    <button class="w-full bg-[#FFD700] text-[#12183B] py-2 mt-4 rounded-lg" @click="navegarEstagio(ESTAGIO_VALIDAR_CADASTRO)">
                        Fazer cadastro/login e acompanhar pedidos
                    </button>                    
                </div>
                <div class="mt-6" v-if="estagioAtual > 1 && estagioAtual < 2">
                    <span class="text-white text-center block mt-4 text-lg">
                        Faça seu cadastro para acompanhar todos os seus pedidos
                    </span>
                    <div v-if="estagioAtual === ESTAGIO_VALIDAR_CADASTRO">
                        <span class="text-white">CPF</span>
                        <input v-model="cadastro.cpf" 
                            v-cpf-mask 
                            type="text" 
                            class="w-full px-4 py-2 text-black rounded-lg" 
                            minlength="14"
                            maxlength="14"
                            placeholder="Digite seu CPF"
                            >
                        <div class="text-white text-center block mt-1  mt-1 bg-red-700 p-2 rounded-lg" v-if="cadastro.cpf.replace(/\s/g, '').length > 0 
                                && !validarCpf(cadastro.cpf)">
                            CPF inválido
                        </div>

                        <button @click="avancarCadastro()" v-if="validarCpf(cadastro.cpf)" class="w-full bg-[#FFD700] text-[#12183B] py-2 mt-4 rounded-lg ">Avançar</button>

                    </div>
                    <div v-if="estagioAtual === ESTAGIO_CADASTRO">
                        <span class="text-white block">CPF: {{ cadastro.cpf }}</span>
                        <span class="text-white">Nome</span>
                        <input v-model="cadastro.nome" type="text" class="w-full px-4 py-2 text-black rounded-lg" placeholder="Digite seu nome"> 
                        <div class="text-white text-center block mt-1  mt-1 bg-red-700 p-2 rounded-lg" v-if="cadastro.nome.replace(/\s/g, '').length > 0 && cadastro.nome.replace(/\s/g, '').length < 6">
                            Falta(m) pelo menos mais {{ 6 - cadastro.nome.replace(/\s/g, '').length }} letra(s)
                        </div>
                        <span class="text-white">Senha</span>
                        <div class="relative w-full">
                            <input :type="showPassword ? 'text' : 'password'" 
                                v-model="cadastro.senha" 
                                class="w-full px-4 py-2 text-black rounded-lg" 
                                placeholder="Digite sua senha">
                            <button type="button" 
                                    @click="togglePasswordVisibility" 
                                    class="absolute inset-y-0 right-0 px-4 py-2">
                                    <div v-if="showPassword">
                                        <i class="fa fa-eye-slash" ></i>
                                    </div>
                                    <div v-else="!showPassword">
                                        <i class="fa fa-eye" ></i> 
                                    </div>
                            </button>
                        </div>
                        <div class="text-white text-center block mt-1  mt-1 bg-red-700 p-2 rounded-lg" v-if="cadastro.senha.replace(/\s/g, '').length > 0 && cadastro.senha.replace(/\s/g, '').length < 6">
                            A senha deve ter pelo menos 6 caracteres
                        </div>
                        <span class="text-white" v-if="!showPassword">Confirme sua senha</span>
                        <input v-if="!showPassword" v-model="cadastro.confirmacaoSenha" type="password" class="w-full px-4 py-2 text-black rounded-lg" placeholder="Confirme sua senha">
                        <div class="text-white text-center block mt-1  mt-1 bg-red-700 p-2 rounded-lg" 
                            v-if="!showPassword && cadastro.confirmacaoSenha && cadastro.senha !== cadastro.confirmacaoSenha">
                            As senhas não conferem
                        </div>
                        <button class="w-full bg-[#FFD700] text-[#12183B] py-2 mt-4 rounded-lg"
                            v-bind:class="{ 'cursor-not-allowed bg-[#ccc]': !podeCadastro() }"
                            @click="efetuarCadastro()" 
                            :disabled="!podeCadastro()">
                            Cadastrar
                        </button>
                    </div>
                    <div v-if="estagioAtual === ESTAGIO_LOGIN">
                        <span class="text-white block">CPF: {{ cadastro.cpf }}</span>
                        <span class="text-white">Senha</span>
                        <div class="relative w-full">
                            <input :type="showPassword ? 'text' : 'password'" 
                                v-model="cadastro.senha" 
                                class="w-full px-4 py-2 text-black rounded-lg" 
                                placeholder="Digite sua senha">
                            <button type="button" 
                                    @click="togglePasswordVisibility" 
                                    class="absolute inset-y-0 right-0 px-4 py-2">
                                    <div v-if="showPassword">
                                        <i class="fa fa-eye-slash" ></i>
                                    </div>
                                    <div v-else="!showPassword">
                                        <i class="fa fa-eye" ></i> 
                                    </div>
                            </button>
                        </div>
                        <div class="text-white text-center block mt-1  mt-1 bg-red-700 p-2 rounded-lg" v-if="cadastro.senha.replace(/\s/g, '').length > 0 && cadastro.senha.replace(/\s/g, '').length < 6">
                            A senha deve ter pelo menos 6 caracteres
                        </div>

                        <button class="w-full bg-[#FFD700] text-[#12183B] py-2 mt-4 rounded-lg"
                            v-bind:class="{ 'cursor-not-allowed bg-[#ccc]': cadastro.senha.replace(/\s/g, '').length < 6 }"
                            @click="efetuarLogin()" 
                            :disabled="cadastro.senha.replace(/\s/g, '').length < 6">
                            Entrar
                        </button>
                    </div>
                </div>
                <div class="mt-6" v-if="estagioAtual === ESTAGIO_NOME">
                    <input v-model="pedido.nome" type="text" class="w-full px-4 py-2 text-black rounded-lg" placeholder="Digite seu nome"> 
                    <div class="text-white text-center block mt-1  mt-1 bg-red-700 p-2 rounded-lg" v-if="pedido.nome.replace(/\s/g, '').length > 0 && pedido.nome.replace(/\s/g, '').length < 6">
                        Falta(m) pelo menos mais {{ 6 - pedido.nome.replace(/\s/g, '').length }} letra(s)
                    </div>
                </div>
                <div v-if="estagioAtual === ESTAGIO_PRODUTOS">
                    <span class="text-white text-center block mt-1  mt-1 bg-red-700 p-2 rounded-lg">
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
                <div v-else-if="estagioAtual === ESTAGIO_OBSERVACAO">
                    <span class="text-white text-center block mt-1  mt-1 bg-red-700 p-2 rounded-lg">
                        Alguma Observação?
                    </span>
                    <div v-if="temAcaiNoCopo()">
                        <div v-for="acompanhamentoAcai in obterAcompanhamentosDoAcai()">
                            <button v-if="pedido.observacao.indexOf(acompanhamentoAcai) === -1"
                             @click="addObservacaoRemoverItemDoAcaiNoCopo(acompanhamentoAcai)" 
                                class="w-full bg-blue-200 text-[#12183B] rounded-lg p-2 mb-1">
                                Açaí sem {{ acompanhamentoAcai }}
                            </button>
                        </div>
                    </div>
                    <textarea v-model="pedido.observacao" class="w-full px-4 py-2 text-black rounded-lg" placeholder="Digite aqui sua observação"></textarea>
                </div>
                <div v-else-if="estagioAtual === ESTAGIO_FORMA_PAGAMENTO">
                    <span class="text-white text-center block mt-1  mt-1 bg-red-700 p-2 rounded-lg">
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
                    <div v-show="clienteLogado.fiado" class="flex justify-between items-center bg-white text-[#12183B] py-2 mt-4 rounded-lg">
                        <span class="px-4">Fiado <span class="text-sm">(Pagar no final da Social)</span></span>
                        <input type="radio" name="formaPagamento" v-model="pedido.formaPagamento" value="FIADO" class="m-2 px-4 py-2 rounded-lg">
                    </div>
                </div>
                <div v-else-if="estagioAtual === ESTAGIO_REVISAO">
                    <span class="text-white text-center block mt-1  mt-1 bg-red-700 p-2 rounded-lg">
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
                    <button @click="navegarEstagio(estagioAtual-1)" class="w-full bg-[#FFD700] text-[#12183B] py-2 mt-4 rounded-lg" v-if="estagiosPedido.filter(e => e.id === estagioAtual && e.podeVoltar() ).length > 0">Voltar</button>
                    <button @click="navegarEstagio(estagioAtual+1)" class="w-full bg-[#FFD700] text-[#12183B] py-2 mt-4 rounded-lg mb-6" v-if="estagioAtual < (estagiosPedido.length) && estagiosPedido.filter(e => e.id === estagioAtual && e.podeProximo() ).length > 0">Avançar</button>
                    <button class="w-full bg-[#FFD700] text-[#12183B] py-2 mt-4 rounded-lg mb-6" v-if="estagioAtual === (estagiosPedido.length)" @click="enviarPedido">Enviar Pedido</button>
                </div>
            </main>
        </div>
    </div>
</template>
