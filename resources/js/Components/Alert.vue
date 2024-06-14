<script setup>

const propriedades = defineProps( {
    alertaObj: {
        type: Object,
        required: true
    }
});

const removeAlerta = (index) => {
    propriedades.alertaObj.alertas.splice(index, 1);
}

</script>

<template>
    <div class="w-full">
        <div v-for="(alerta, index) in alertaObj.alertas" :style="{top: `${index * 4}rem`}" class="px-2 justify-center pt-6 fixed w-full" style="z-index: 1000;">
            <div 
            :class="{
                'bg-green-100 border-green-400 text-green-700': alerta.tipo == 'success',
                'bg-red-100 border-red-400 text-red-700': alerta.tipo == 'error',
                'bg-yellow-100 border-yellow-400 text-yellow-700': alerta.tipo == 'warning'
            }"
            class="px-4 py-3 rounded relative" role="alert">
                {{ alerta.mensagem }}
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                    <button @click="removeAlerta(index)" class="text-2xl leading-none hover:opacity-50">&times;</button>
                </span>
            </div>
        </div>
    </div>
</template>