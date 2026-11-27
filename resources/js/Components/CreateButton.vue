<template>
    <n-button type="primary" :size="size" @click="emit('createRoute')">
        <template #icon>
            <template v-if="iconType === 'add'">
                <n-icon :component="Add12Regular"></n-icon>
            </template>
            <template v-else>
                <n-icon :component="ArrowUpload24Filled"></n-icon>
            </template>
        </template>

        {{ buttonText }}
    </n-button>
</template>

<script lang="ts">
import { defineComponent, PropType } from "vue";
import { Add12Regular, ArrowUpload24Filled } from "@vicons/fluent";
import { NButton, NIcon } from "naive-ui";
import { Size } from "naive-ui/es/button/src/interface";

export default defineComponent({
    name: "CreateButton",
    props: {
        size: {
            type: String as unknown as () => PropType<Size>,
            required: false,
            default: () => "medium",
        },
        buttonText: { 
            type: String, 
            required: false,
            default: () => 'Buat Data Baru'
        },
        iconType: {
            type: String,
            required: false,
            default: () => 'add'
        }
    },
    emits: ["createRoute"],
    setup(props, { emit }) {
        return {
            emit,
            Add12Regular,
            ArrowUpload24Filled
        };
    },
    components: {
        NButton,
        NIcon,
    },
});
</script>
