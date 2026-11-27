<template>
    <div
        class="group relative overflow-hidden rounded-2xl border-l-4 border-blue-800 bg-white shadow-sm transition-all duration-300 hover:shadow-md"
    >

        <!-- Header -->
        <div class="flex items-center justify-between px-5 pb-3 pt-5">
            <div class="flex items-center gap-3">
                <!-- Icon slot or default -->
                <div
                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#003092]/8 ring-1 ring-[#003092]/15"
                >
                    <slot name="icon">
                        <n-icon
                            :component="Filter24Filled"
                            size="18"
                            color="black"
                        />
                    </slot>
                </div>

                <div class="flex flex-col leading-tight">
                    <span
                        class="text-[15px] font-semibold text-slate-800"
                    >
                        <slot name="title">{{ title }}</slot>
                    </span>
                    <span
                        class="mt-0.5 text-[11.5px] text-slate-400"
                    >
                        <slot name="subtitle">{{ subtitle }}</slot>
                    </span>
                </div>
            </div>

            <!-- Header actions slot (badge, status, etc.) -->
            <div class="flex items-center gap-2">
                <slot name="header-actions" />
            </div>
        </div>

        <!-- Divider -->
        <div
            class="mx-5 border-t border-dashed border-slate-200"
        />

        <!-- Body -->
        <div class="px-5 py-4">
            <!-- Input fields slot -->
            <div
                class="flex flex-col gap-3 lg:flex-row lg:items-center lg:flex-wrap"
            >
                <slot />
            </div>

            <!-- Hint text -->
            <p
                v-if="showHint"
                class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-400"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-3 w-3 shrink-0 opacity-60"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 16v-4M12 8h.01" />
                </svg>
                <slot name="hint">
                    <span>
                        Tekan
                        <kbd
                            class="rounded border border-slate-200 bg-slate-100 px-1 py-px font-mono text-[10px] text-slate-600"
                            >Enter</kbd
                        >
                        setelah mengisi filter teks, atau pilih nilai dari
                        dropdown untuk menerapkan filter.
                    </span>
                </slot>
            </p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Filter24Filled } from "@vicons/fluent";
import { NIcon } from "naive-ui";

defineProps({
    title: {
        type: String,
        default: "Filter Data",
    },
    subtitle: {
        type: String,
        default: "Saring berdasarkan nama, kode, atau tipe.",
    },
    showHint: {
        type: Boolean,
        default: true,
    },
});
</script>

<style scoped>
.filter-card {
    transition:
        box-shadow 0.25s ease,
        border-color 0.25s ease;
}

.filter-card:hover {
    border-color: rgb(148 163 184 / 0.6);
}

</style>
