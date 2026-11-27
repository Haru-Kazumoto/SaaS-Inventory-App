<template>
    <div
        class="relative flex items-center gap-3 overflow-hidden rounded border border-slate-200 bg-white py-3 pl-5 pr-4"
    >
        <span
            :class="['absolute inset-y-0 left-0 w-[3px]', colorConfig.accent]"
            aria-hidden="true"
        ></span>
        <div
            :class="[
                'flex h-9 w-9 shrink-0 items-center justify-center rounded',
                colorConfig.iconBg,
            ]"
        >
            <n-icon
                :component="icon"
                size="18"
                :color="colorConfig.iconColor"
            />
        </div>

        <div class="flex min-w-0 flex-1 flex-col">
            <span
                :class="[
                    'text-[10.5px] font-semibold uppercase tracking-[0.14em]',
                    colorConfig.label,
                ]"
            >
                {{ label }}
            </span>
            <div class="flex items-baseline gap-1">
                <span :class="['text-xl font-bold tabular-nums', colorConfig.count]">
                    {{ count ?? 0 }}
                </span>
                <span :class="['text-xs font-normal', colorConfig.unit]">
                    {{ unit }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import type { Component } from "vue";
import { NIcon } from "naive-ui";

type Color = "blue" | "green" | "red" | "amber" | "gray";

const props = defineProps<{
    icon: Component;
    label: string;
    count: number | null | undefined;
    unit: string;
    color: Color;
}>();

const colorMap: Record<
    Color,
    {
        accent: string;
        iconBg: string;
        iconColor: string;
        label: string;
        count: string;
        unit: string;
    }
> = {
    blue: {
        accent: "bg-[#2A126C]",
        iconBg: "bg-[#2A126C]/[0.07]",
        iconColor: "#2A126C",
        label: "text-slate-500",
        count: "text-slate-900",
        unit: "text-slate-400",
    },
    green: {
        accent: "bg-emerald-500",
        iconBg: "bg-emerald-50",
        iconColor: "#047857",
        label: "text-slate-500",
        count: "text-slate-900",
        unit: "text-slate-400",
    },
    red: {
        accent: "bg-rose-500",
        iconBg: "bg-rose-50",
        iconColor: "#be123c",
        label: "text-slate-500",
        count: "text-slate-900",
        unit: "text-slate-400",
    },
    amber: {
        accent: "bg-amber-500",
        iconBg: "bg-amber-50",
        iconColor: "#b45309",
        label: "text-slate-500",
        count: "text-slate-900",
        unit: "text-slate-400",
    },
    gray: {
        accent: "bg-slate-400",
        iconBg: "bg-slate-100",
        iconColor: "#475569",
        label: "text-slate-500",
        count: "text-slate-900",
        unit: "text-slate-400",
    },
};

const colorConfig = computed(() => colorMap[props.color]);
</script>
