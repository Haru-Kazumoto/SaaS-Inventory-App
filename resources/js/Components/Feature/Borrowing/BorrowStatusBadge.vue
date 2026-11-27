<template>
    <span
        class="inline-flex items-center gap-1.5 whitespace-nowrap rounded border px-2 py-0.5 text-[11px] font-semibold"
        :class="meta.badge"
    >
        <span
            class="h-1.5 w-1.5 shrink-0 rounded-full"
            :class="meta.dot"
            aria-hidden="true"
        ></span>
        {{ meta.label }}
    </span>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { borrowStatusMeta } from "./borrow-status";

const props = defineProps<{ status?: string | null }>();

const meta = computed(() => borrowStatusMeta(props.status));
</script>
