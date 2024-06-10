<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref, onMounted } from 'vue'
import axios from 'axios';
import { formatarMoeda } from '@/Utils/NumeroUtils';

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
    axios.get(route('produtos.index'))
    .then((response)=>{
        produtos.value = response.data;
    })
    .catch((error)=>{
        alert(error.response.data.message);
    })

}


const cadastrarProduto = (produto) => {

    if(produto.id){
        axios.put(route('produtos.update', produto.id),{
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
        return;
    }

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
        id: produto.id,
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
                    <div class="m-2 font-semibold text-xl text-gray-800 leading-tight">Cadastro de Produtos</div>
                    <div class="m-2">  
                        <div>
                            <InputLabel for="nomeProduto" value="Nome Produto"/>
                            <TextInput class="lg:w-1/2 w-full"
                            type="text" name="nomeProduto" id="nomeProduto" v-model="produtoForm.nomeProduto"/> 
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
                        <div class="my-4">
                            <PrimaryButton @click="cadastrarProduto(produtoForm)">Salvar</PrimaryButton> 
                        </div>
                    </div>
                    <hr>
                    <div class="m-2">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        id
                                    </th>
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
                                        Ativo
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="produto in produtos">
                                    <td class="px-4 text-sm text-gray-900">{{ produto.id }}</td>
                                    <td class="px-4 text-sm text-gray-900">{{ produto.nome }}</td>
                                    <td class="px-4 text-sm text-gray-900">{{ formatarMoeda(produto.valor) }}</td>
                                    <td class="px-4 text-sm text-gray-900">{{ produto.ativo == "S" ? "SIM" : "NÃO" }}</td>
                                    <td class="px-4 text-sm text-gray-900"><PrimaryButton @click="editarProduto(produto)">editar</PrimaryButton></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>       

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
