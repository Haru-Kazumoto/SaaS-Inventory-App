<template>
    <button
        type="button"
        class="group flex items-center gap-2.5 rounded border border-emerald-600 bg-emerald-50 py-1.5 pl-1.5 pr-3.5 text-left transition-colors duration-150 hover:bg-emerald-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40"
        :title="title"
        @click="$emit('click')"
    >
        <span
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded bg-emerald-600 text-white transition-colors duration-150 group-hover:bg-white group-hover:text-emerald-700"
        >
            <n-icon :component="DocumentTableArrowRight20Regular" size="18" />
        </span>
        <span class="flex flex-col leading-tight">
            <span
                class="text-sm font-semibold text-emerald-800 transition-colors duration-150 group-hover:text-white"
            >
                {{ label }}
            </span>
            <span
                v-if="caption"
                class="text-[11px] text-emerald-700/80 transition-colors duration-150 group-hover:text-white/80"
            >
                {{ caption }}
            </span>
        </span>
    </button>
</template>

<script setup lang="ts">
import { NIcon } from "naive-ui";
import { DocumentTableArrowRight20Regular } from "@vicons/fluent";

withDefaults(
    defineProps<{
        /** Teks utama tombol */
        label?: string;
        /** Keterangan kecil di bawah label, mis. "Rekap 2026 · .xlsx" */
        caption?: string;
        /** Tooltip native */
        title?: string;
    }>(),
    { label: "Export Excel", caption: ".xlsx", title: "Unduh data dalam format Excel" },
);

defineEmits<{ (e: "click"): void }>();
</script>
