<template>
    <span
        class="inline-flex items-center gap-1.5 whitespace-nowrap rounded border px-2 py-0.5 text-[11px] font-semibold"
        :class="meta.badge"
    >
        <span class="h-1.5 w-1.5 shrink-0 rounded-full" :class="meta.dot" aria-hidden="true"></span>
        {{ meta.label }}
    </span>
</template>

<script setup lang="ts">
import { computed } from "vue";

const props = defineProps<{ status?: string | null }>();

const META: Record<string, { label: string; badge: string; dot: string }> = {
    PENDING: {
        label: "Menunggu",
        badge: "border-amber-200 bg-amber-50 text-amber-700",
        dot: "bg-amber-500",
    },
    APPROVED: {
        label: "Disetujui",
        badge: "border-emerald-200 bg-emerald-50 text-emerald-700",
        dot: "bg-emerald-500",
    },
    REJECTED: {
        label: "Ditolak",
        badge: "border-rose-200 bg-rose-50 text-rose-700",
        dot: "bg-rose-500",
    },
};

const meta = computed(
    () =>
        META[(props.status || "").toUpperCase()] ?? {
            label: props.status || "-",
            badge: "border-slate-200 bg-slate-50 text-slate-600",
            dot: "bg-slate-400",
        },
);
</script>
