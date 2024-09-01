<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const clienteSelecionado = ref({});
const showModaCliente = ref(false);

const cadastrarOuEditarCliente = (cliente) => {
    showModaCliente.value = true;
    clienteSelecionado = cliente;
}

</script>

<template>

    <Head title="Loja Pioneiros da Fé" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Cadastro de Clientes</h2>
        </template>
        <div class="py-12">
            <div class="mx-2">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <table class="w-full table-auto my-2">
                        <thead>
                            <tr>
                                <th class="p-4 border border-slate-200" v-for="header in ['ID', 'Nome', 'Cpf', 'Ativo']">
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
                            <tr>
                                <td class="border border-slate-200">1</td>
                                <td class="border border-slate-200">Dan</td>
                                <td class="border border-slate-200">123</td>
                                <td class="border border-slate-200">Sim</td>
                                <td class="border border-slate-200">
                                    <p
                                        class="py-2 flex justify-center block font-sans text-sm antialiased font-normal leading-none text-blue-gray-900 opacity-70">
                                        <SecondaryButton @click="cadastrarOuEditarCliente({})">Editar</SecondaryButton>
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
                    <InputLabel for="name" value="Nome" />
                    <TextInput id="name" ref="name" v-model="clienteSelecionado.name" type="text" class="mt-1 block w-full" />
                </div>
                <div>
                    <InputLabel for="cpf" value="cpf" />
                    <TextInput id="cpf" ref="cpf" v-model="clienteSelecionado.cpf" type="text"
                        class="mt-1 block w-full" />
                </div>
                <div>
                    <InputLabel for="flagAtivo" value="Ativo" />
                    <select name="flagAtivo" id="flagAtivo" v-model="clienteSelecionado.fg_ativo" class="mt-1 block w-full">
                        <option value="S">Sim</option>
                        <option value="N">Não</option>
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