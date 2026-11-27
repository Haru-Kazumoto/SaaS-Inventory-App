<template>
    <span
        v-if="type"
        class="inline-flex shrink-0 items-center whitespace-nowrap rounded border px-1.5 py-0.5 text-[11px] font-semibold"
        :class="
            type === 'DURABLE'
                ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                : 'border-sky-200 bg-sky-50 text-sky-700'
        "
    >
        {{ type === "DURABLE" ? "Tidak habis pakai" : "Habis pakai" }}
    </span>
    <span v-else class="text-slate-300">—</span>
</template>

<script setup lang="ts">
defineProps<{ type?: string | null }>();
</script>
