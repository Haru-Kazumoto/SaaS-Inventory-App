<template>
    <div
        class="flex flex-col gap-4 pb-5 border-b border-slate-200 sm:flex-row sm:items-end sm:justify-between"
    >
        <div class="flex flex-col gap-1.5 min-w-0">
            <span
                v-if="eyebrow"
                class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#2A126C]"
            >
                {{ eyebrow }}
            </span>
            <span
                class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 leading-tight"
            >
                {{ title }}
            </span>
            <span class="text-sm text-slate-500 leading-relaxed">
                {{ subTitle }}
            </span>
        </div>

        <div class="shrink-0">
            <slot name="action"></slot>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent({
    props: {
        title: {
            type: String,
            required: false,
            default: "Title Page",
        },
        subTitle: {
            type: String,
            required: false,
            default: "Subtitle Page",
        },
        eyebrow: {
            type: String,
            required: false,
            default: "",
        },
    },
});
</script>

<style scoped></style>
