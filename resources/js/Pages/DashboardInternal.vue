<template>
    <div>
        <h1>HALO DEVELOPER!</h1>
    </div>
</template>

<script lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { defineComponent } from 'vue'

export default defineComponent({
    setup () {
        

        return {}
    },
    layout: AuthenticatedLayout
})
</script>