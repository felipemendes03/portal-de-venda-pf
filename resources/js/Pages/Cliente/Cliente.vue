<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const clienteSelecionado = ref({});
const showModaCliente = ref(false);
const clientes = ref([]);
const alertas = ref([]);

const cadastrarOuEditarCliente = (cliente) => {
    showModaCliente.value = true;
    if(cliente.id){
        clienteSelecionado.value = {...cliente};
    } else {
        clienteSelecionado.value = {};
    }
}

onMounted(()=>{ 
    listarClientes();
})

const listarClientes = () => {  
    axios.get(route('api.admin.clientes.index'))
    .then((response)=>{
        clientes.value = response.data;
    })
    .catch((error)=>{
        alert(error.response.data.message);
    })
}

const salvarCliente = () => {
    const clienteData = {
        nome: clienteSelecionado.value.nome,
        cpf: clienteSelecionado.value.cpf,
        ativo: clienteSelecionado.value.ativo,
        password: clienteSelecionado.value.password,
        fiado: clienteSelecionado.value.fiado
    };

    const request = clienteSelecionado.value.id 
        ? axios.put(route('api.admin.clientes.update', clienteSelecionado.value.id), clienteData)
        : axios.post(route('api.admin.clientes.store'), clienteData);

    request
        .then((response) => {
            addAlerta(response.data.message);
            listarClientes();
            clienteSelecionado.value = {};
            showModaCliente.value = false;
        })
        .catch((error) => {
            addAlerta(error.response.data.message, 'error');
        });
}

const addAlerta = (mensagem, tipo = 'success') => {
    alertas.value.push({mensagem, tipo});
    setTimeout(() => {
        alertas.value.shift();
    }, 5000);
}

</script>

<template>

    <Head title="Loja Pioneiros da Fé" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Cadastro de Clientes</h2>
        </template>
        <div v-for="alerta in alertas" class="flex justify-center pt-6 fixed top-0 w-full" style="z-index: 1000;">
            <div :class="alerta.tipo === 'error' ? 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative' : 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative'" role="alert">
                {{ alerta.mensagem }}
            </div>
        </div>
        <div class="py-12">
            <div class="mx-2">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <table class="w-full table-auto my-2">
                        <thead>
                            <tr>
                                <th class="p-4 border border-slate-200" v-for="header in ['ID', 'Nome', 'CPF', 'Ativo']">
                                    <p
                                        class="block font-sans text-sm antialiased font-normal leading-none text-blue-gray-900 opacity-70">
                                        {{ header }}
                                    </p>
                                </th>
                                <th class="p-4 border border-slate-200">
                                    <p
                                        class="flex justify-center block font-sans text-sm antialiased font-normal leading-none text-blue-gray-900 opacity-70">
                                        <SecondaryButton @click="cadastrarOuEditarCliente({})">Novo</SecondaryButton>
                                    </p>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            <tr v-for="cliente in clientes" :key="cliente.id">
                                <td class="border border-slate-200">{{ cliente.id }}</td>
                                <td class="border border-slate-200">{{ cliente.nome }}</td>
                                <td class="border border-slate-200">{{ cliente.cpf }}</td>
                                <td class="border border-slate-200">{{ cliente.ativo ? "SIM" : "NÃO" }}</td>
                                <td class="border border-slate-200">
                                    <p
                                        class="py-2 flex justify-center block font-sans text-sm antialiased font-normal leading-none text-blue-gray-900 opacity-70">
                                        <SecondaryButton @click="cadastrarOuEditarCliente(cliente)">Editar</SecondaryButton>
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <Modal :show="showModaCliente" @close="showModaCliente = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Cadastro de Cliente
                </h2>
                <div>
                    <InputLabel for="perfilId" value="ID" />
                    <TextInput id="perfilId" ref="perfilId" v-model="clienteSelecionado.id" type="text"
                        class="mt-1 block w-full" disabled />
                </div>
                <div>
                    <InputLabel for="nome" value="Nome" />
                    <TextInput id="nome" ref="nome" v-model="clienteSelecionado.nome" type="text" class="mt-1 block w-full" />
                </div>
                <div>
                    <InputLabel for="cpf" value="cpf" />
                    <TextInput v-cpf-mask id="cpf" ref="cpf" v-model="clienteSelecionado.cpf" type="text"
                        class="mt-1 block w-full" />
                </div>
                <div>
                    <InputLabel for="flagAtivo" value="Ativo" />
                    <select name="flagAtivo" id="flagAtivo" v-model="clienteSelecionado.ativo" class="mt-1 block w-full">
                        <option value="1">Sim</option>
                        <option value="0">Não</option>
                    </select>
                </div>
                <div>
                    <InputLabel for="fiado" value="Permite Fiado" />
                    <select name="fiado" id="fiado" v-model="clienteSelecionado.fiado" class="mt-1 block w-full">
                        <option value="1">Sim</option>
                        <option value="0">Não</option>
                    </select>
                </div>

                <div>
                    <InputLabel for="password" value="Senha" />
                    <TextInput id="password" ref="password" v-model="clienteSelecionado.password" type="text"
                        class="mt-1 block w-full" />
                </div>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="showModaCliente = false"> Cancelar </SecondaryButton>
                    <PrimaryButton class="ms-3" @click="salvarCliente()">
                        Salvar
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>