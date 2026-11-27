<template>
    <n-button type="primary" :size="size" @click="onRefresh" :loading :disabled="loading">
        <template #icon>
            <n-icon :component="ArrowClockwise20Filled" />
        </template>
        Refresh
    </n-button>
</template>

<script setup lang="ts">
import { ArrowClockwise20Filled } from "@vicons/fluent";
import { NButton, NIcon, useMessage } from "naive-ui";
import { router } from "@inertiajs/vue3";
import { ref } from "vue";

// props: array of prop names to reload (partial reload)
const props = withDefaults(
    defineProps<{
        refreshProps: string[];
        /** Samakan dengan ukuran input di sebelahnya (default: medium) */
        size?: "tiny" | "small" | "medium" | "large";
    }>(),
    { size: "medium" },
);

const message = useMessage();
const loading = ref<boolean>(false);

function onRefresh() {
    loading.value = true;
    router.reload({
        only: props.refreshProps,
        onSuccess: () => message.success("Diperbarui!"),
        onFinish: () => loading.value = false
    });
}
</script>
