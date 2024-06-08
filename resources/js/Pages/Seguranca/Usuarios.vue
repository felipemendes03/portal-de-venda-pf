<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Pencil from 'vue-material-design-icons/Pencil.vue';
import Delete from 'vue-material-design-icons/Delete.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const showModalUser = ref(false);
const showModalDeleteUser = ref(false);
const userSelected = ref({});


defineProps({
    users: {
        type: Object,
    },
    profiles: {
        type: Object,
    },
});

const cadastrarOuEditarUsuario = (usuario) => {
    showModalUser.value = true;
    userSelected.value = usuario;
}

const apagarUsuario = (usuario) => {
    showModalDeleteUser.value = true;
    userSelected.value = usuario;
}

const salvarUsuario = () => {
    axios.post(route('security.users.post'), userSelected.value)
    .then(() => {
        window.location.reload();
    })
    .catch( (ex) => {
        alert(ex.response.data.message);
    }
    );
}

const apagarRegistro = () => {
    axios.delete(route('security.users.delete', {id: userSelected.value.id}))
    .then(() => {
        window.location.reload();
    })
    .catch( (ex) => {
        alert(ex.response.data.message);
    }
    );
}

</script>

<template>
    <Head title="Usuários" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Usuários</h2>
        </template>
        <div class="py-12">
            <div class="mx-2">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                   <table class="w-full table-auto my-2">
                        <thead>
                            <tr>
                                <th class="p-4 border border-slate-200"
                                    v-for="header in ['ID', 'Nome','E-mail', 'Ativo', 'Perfil']">
                                    <p class="block font-sans text-sm antialiased font-normal leading-none text-blue-gray-900 opacity-70">
                                        {{ header }}
                                    </p>
                                </th>
                                <th class="p-4 border border-slate-200">
                                    <p class="flex justify-center block font-sans text-sm antialiased font-normal leading-none text-blue-gray-900 opacity-70">
                                        <SecondaryButton @click="cadastrarOuEditarUsuario({})">Novo</SecondaryButton>
                                    </p>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            <tr v-for="user in users">
                                <td class="border border-slate-200"> {{ user.id }}</td>
                                <td class="border border-slate-200"> {{ user.name }}</td>
                                <td class="border border-slate-200"> {{ user.email }}</td>
                                <td class="border border-slate-200"> {{ user.fg_ativo == 'S' ? 'sim' : 'não' }} </td>
                                <td class="border border-slate-200"> 
                                    <select name="id_perfil_tb" id="id_perfil_tb" v-model="user.id_perfil" class="mt-1 block w-full" disabled>
                                        <option value=""></option>
                                        <option v-for="profile in profiles" :value="profile.id">{{ profile.nm_perfil }}</option>
                                    </select>
                                </td>
                                <td class="border border-slate-200">
                                    <div class="flex text-center">
                                        <Pencil fillColor="#134e4a" title="Editar" @click="cadastrarOuEditarUsuario(user)" class="cursor-pointer border-2 p-2"/>
                                        <Delete fillColor="#be123c" title="Apagar"  @click="apagarUsuario(user)" class="cursor-pointer border-2 ml-1 p-2"/>
                                    </div>
                                </td>
                            </tr>
                        </tbody> 
                    </table>
                </div>
            </div>
        </div>
        <Modal :show="showModalUser" @close="showModalUser=false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Cadastro de Usuário
                </h2>
                <div>
                    <InputLabel for="perfilId" value="ID" />
                    <TextInput
                        id="perfilId"
                        ref="perfilId"
                        v-model="userSelected.id"
                        type="text"
                        class="mt-1 block w-full"
                        disabled
                    />
                </div>
                <div>
                    <InputLabel for="name" value="Nome" />
                    <TextInput
                        id="name"
                        ref="name"
                        v-model="userSelected.name"
                        type="text"
                        class="mt-1 block w-full"
                    />
                </div>
                <div>
                    <InputLabel for="email" value="E-mail" />
                    <TextInput
                        id="email"
                        ref="email"
                        v-model="userSelected.email"
                        type="text"
                        class="mt-1 block w-full"
                    />
                </div>
                <div>
                    <InputLabel for="flagAtivo" value="Ativo" />
                    <select name="flagAtivo" id="flagAtivo" v-model="userSelected.fg_ativo" class="mt-1 block w-full">
                        <option value="S">Sim</option>
                        <option value="N">Não</option>
                    </select>
                </div>
                <div>
                    <InputLabel for="password" value="Senha" />
                    <TextInput
                        id="password"
                        ref="password"
                        v-model="userSelected.password"
                        type="text"
                        class="mt-1 block w-full"
                    />
                </div>
                <div>
                    <InputLabel for="id_perfil" value="ID do Perfil" />
                    <select name="id_perfil" id="id_perfil" v-model="userSelected.id_perfil" class="mt-1 block w-full">
                        <option value=""></option>
                        <option v-for="profile in profiles" :value="profile.id">{{ profile.nm_perfil }}</option>
                    </select>
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="showModalUser=false"> Cancelar </SecondaryButton>
                    <PrimaryButton class="ms-3" @click="salvarUsuario()">
                        Salvar
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
        <Modal :show="showModalDeleteUser" @close="showModalDeleteUser=false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Deseja realmente apagar o usuário {{ userSelected.name }}?
                </h2>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="showModalDeleteUser=false"> Cancelar </SecondaryButton>
                    <PrimaryButton class="ms-3" @click="apagarRegistro()">
                        Apagar
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>