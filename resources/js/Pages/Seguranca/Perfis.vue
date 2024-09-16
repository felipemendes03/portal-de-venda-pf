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
import axios from 'axios';

const showModalProfile = ref(false);
const showModalDeleteProfile = ref(false);
const profileSelected = ref({});

defineProps({
    profiles: {
        type: Object,
    },
    permissions: {
        type: Object,
    },
});

const cadastrarOuEditarPerfil = (perfil) => {
    showModalProfile.value = true;
    profileSelected.value = perfil;
    profileSelected.value.permissionsSelected = [];
    axios.get(route('security.profiles.permissions.get', {idProfile: profileSelected.value.id}))
    .then((response) => {
        profileSelected.value.permissionsSelected = response.data || [];
    })
     .catch( (ex) => {
        alert(ex.response.data.message);
     }
    );
}

const apagarUsuario = (perfil) => {
    showModalDeleteProfile.value = true;
    profileSelected.value = perfil;
}

const apagarRegistro = () => {
    axios.delete(route('security.profiles.delete', {id: profileSelected.value.id}))
    .then(() => {
        window.location.reload();
     })
     .catch( (ex) => {
        alert(ex.response.data.message);
     }
    );
}

const salvarRegistro = () => {
    axios.post(route('security.profiles.post'), profileSelected.value)
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
    <Head title="Perfis" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Perfis</h2>
        </template>
        <div class="py-12">
            <div class="mx-2">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto my-2">
                            <thead>
                                <tr>
                                    <th class="p-4 border border-slate-200">
                                        <p class="block font-sans text-sm antialiased font-normal leading-none text-blue-gray-900 opacity-70">
                                            ID
                                        </p>
                                    </th>
                                    <th class="p-4 border border-slate-200 w-full">
                                        <p class="block font-sans text-sm antialiased font-normal leading-none text-blue-gray-900 opacity-70">
                                            Nome
                                        </p>
                                    </th>
                                    <th class="p-4 border border-slate-200">
                                        <p class="flex justify-center block font-sans text-sm antialiased font-normal leading-none text-blue-gray-900 opacity-70">
                                            <SecondaryButton @click="cadastrarOuEditarPerfil({})">Novo</SecondaryButton>
                                        </p>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                <tr v-for="perfil in profiles">
                                    <td class="border border-slate-200"> {{ perfil.id }}</td>
                                    <td class="border border-slate-200"> {{ perfil.nm_perfil }}</td>
                                    <td class="border border-slate-200">
                                        <div class="flex text-center">
                                            <Pencil fillColor="#134e4a" title="Editar" @click="cadastrarOuEditarPerfil(perfil)" class="cursor-pointer border-2 p-2"/>
                                            <Delete fillColor="#be123c" title="Apagar" @click="apagarUsuario(perfil)" class="cursor-pointer border-2 ml-1 p-2"/>
                                        </div>
                                    </td>
                                </tr>
                            </tbody> 
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <Modal :show="showModalProfile" @close="showModalProfile=false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Cadastro de Perfil
                </h2>
                <div>
                    <InputLabel for="seg_perfil_id" value="ID" />
                    <TextInput
                        id="seg_perfil_id"
                        ref="seg_perfil_id"
                        v-model="profileSelected.id"
                        type="number"
                        class="mt-1 block w-full"
                        disabled
                    />
                </div>
                <div>
                    <InputLabel for="nm_perfil" value="Nome do Perfil" />
                    <TextInput
                        id="nm_perfil"
                        ref="nm_perfil"
                        v-model="profileSelected.nm_perfil"
                        type="text"
                        class="mt-1 block w-full"
                    />
                </div>
            
                <div class="mt-4">
                    <span class="text-lg font-medium text-gray-900">
                        Permissões:
                    </span>
                    <ul>
                        <li v-for="permission in permissions">
                            <label class="flex items-center">
                                <input type="checkbox" :value="permission.id" v-model="profileSelected.permissionsSelected">
                                <span class="ms-2 text-sm text-gray-600">{{ permission.nm_permissao }}</span>
                            </label>
                        </li>
                    </ul>
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="showModalProfile=false"> Cancelar </SecondaryButton>
                    <PrimaryButton class="ms-3" @click="salvarRegistro()">
                        Salvar
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
        <Modal :show="showModalDeleteProfile" @close="showModalDeleteProfile=false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Apagar Perfil
                </h2>
                <span>Você confirma a exclusão do perfil "{{ profileSelected.nm_perfil }}"?</span>
                
                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="showModalDeleteProfile=false"> Cancelar </SecondaryButton>
                    <PrimaryButton class="ms-3" @click="apagarRegistro()">
                        Sim
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>