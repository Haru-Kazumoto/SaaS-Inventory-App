<template>
    <n-button text @click="handleGoPrevious" class="self-start mt-auto">
        <template #icon>
            <n-icon :component="IosArrowLtr24Filled" />
        </template>
        KEMBALI
    </n-button>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { router } from '@inertiajs/vue3';
import { IosArrowLtr24Filled } from '@vicons/fluent';
import { NButton, NIcon } from 'naive-ui';

export default defineComponent({
    name: "PreviousButton",
    props: {
        route: {
            type: String,
            required: true,
            default: "#"
        }
    },
    setup(props) {
        function handleGoPrevious() {
            router.visit(route(props.route));
        }

        return {
            handleGoPrevious,
            IosArrowLtr24Filled
        }
    },
    components: {
        NButton,
        NIcon
    }
})
</script>

<style scoped></style>