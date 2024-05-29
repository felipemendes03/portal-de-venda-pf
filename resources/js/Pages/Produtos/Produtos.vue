<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref, onMounted } from 'vue'
import axios from 'axios';

const produtos=ref([]);
const produtoForm=ref({
    id:null,
    nomeProduto:null,
    valorProduto: null,
    flagProdutoAtivo:null
});
onMounted(()=>{ 
    listarProdutos();
})
const listarProdutos = () => {  
    axios.get(route('produtos.list'))
    .then((response)=>{
        produtos.value = response.data;
    })
    .catch((error)=>{
        alert(error.response.data.message);
    })

}


const cadastrarProduto = (produto) => {
    axios.post(route('produtos.create'),{
        nome: produto.nomeProduto,
        valor: produto.valorProduto,
        ativo: produto.flagProdutoAtivo
    })
    .then((response) =>{
        alert(response.data.message);
       listarProdutos();
    })
    .catch((error)=>{
        alert(error.response.data.message);
    })
    produtoForm.value = {
        id:null,
        nomeProduto:null,
        valorProduto:null,
        flagProdutoAtivo:null     
    }
}
const editarProduto = (produto) => {
    produtoForm.value = {
        nomeProduto: produto.nome,
        valorProduto: produto.valor,
        flagProdutoAtivo: produto.ativo
    };
}
</script>

<template>
    <Head title="Loja Pioneiros da Fé"/>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Produtos</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900"> cadastre seu pedido! </div>
                       

                    <div class="m-2">  
                        <div>
                            <InputLabel for="nomeProduto" value="Nome Produto"/>
                            <TextInput type="text" name="nomeProduto" id="nomeProduto" v-model="produtoForm.nomeProduto"/> 
                        </div>
                        <div>
                            <InputLabel for="valorProduto" value="Valor"/>
                            <TextInput type="number" min="0" name="valorProduto" id="valorProduto" v-model="produtoForm.valorProduto"/>
                        </div>
                        <div>
                            <InputLabel for="flagProdutoAtivo" value="Ativo"/>
                            <select name="flagProdutoAtivo" id="flagProdutoAtivo" v-model="produtoForm.flagProdutoAtivo">
                                <option value=""></option>
                                <option value="S">Sim</option>
                                <option value="N">Não</option>
                            </select>


                        </div>
                        <div>
                            <PrimaryButton @click="cadastrarProduto(produtoForm)">Adicionar</PrimaryButton> 
                        </div>
                    </div>
                    <div class="m-2">
                    <table class="w-full"> 
                        <tr>
                            <th class="border">id</th>
                            <th class="border">Nome do Produto</th>
                            <th class="border">Valor</th>
                            <th class="border">Ativo</th>
                            <th class="border"></th>

                        </tr>
                        <tr v-for="produto in produtos">
                            <td class="border">{{ produto.id }}</td>
                            <td class="border">{{ produto.nome }}</td>
                            <td class="border">{{ produto.valor }}</td>
                            <td class="border">{{ produto.ativo }}</td>
                            <td class="border"><PrimaryButton @click="editarProduto(produto)">editar</PrimaryButton></td>
                        </tr>

                    </table>
                </div>       

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
