<template>
    <n-button type="info" :size="size" @click="emit('export')">
        <template #icon>
            <n-icon :component="DocumentArrowDown16Regular"></n-icon>
        </template>

        {{ buttonText }}
    </n-button>
</template>

<script lang="ts">
import { defineComponent, PropType } from "vue";
import { DocumentArrowDown16Regular } from "@vicons/fluent";
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
            default: () => "Export Data",
        },
    },
    emits: ["export"],
    setup(props, { emit }) {
        return {
            emit,
            DocumentArrowDown16Regular
        };
    },
    components: {
        NButton,
        NIcon,
    },
});
</script>
