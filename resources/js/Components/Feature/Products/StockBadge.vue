<template>
    <span
        class="inline-flex items-center gap-1.5 whitespace-nowrap rounded border px-2 py-0.5 text-[11px] font-semibold"
        :class="status.badge"
    >
        <span class="h-1.5 w-1.5 rounded-full" :class="status.dot" aria-hidden="true"></span>
        {{ status.label }}
    </span>
</template>

<script setup lang="ts">
import { computed } from "vue";

const props = defineProps<{ qty: number | string | null | undefined }>();

const status = computed(() => {
    const n = Number(props.qty) || 0;
    if (n <= 0)
        return { label: "Habis", badge: "border-rose-200 bg-rose-50 text-rose-700", dot: "bg-rose-500" };
    if (n <= 5)
        return { label: "Menipis", badge: "border-amber-200 bg-amber-50 text-amber-700", dot: "bg-amber-500" };
    if (n <= 20)
        return { label: "Cukup", badge: "border-sky-200 bg-sky-50 text-sky-700", dot: "bg-sky-500" };
    return { label: "Aman", badge: "border-emerald-200 bg-emerald-50 text-emerald-700", dot: "bg-emerald-500" };
});
</script>
