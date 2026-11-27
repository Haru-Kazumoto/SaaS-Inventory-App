<template>
    <component
        :is="href ? Link : 'div'"
        :href="href"
        class="group relative block overflow-hidden bg-white rounded border border-slate-200 p-5 pl-6 transition-colors duration-150"
        :class="href ? 'hover:border-[#2A126C]/40' : ''"
    >
        <!-- Aksen kategori -->
        <span
            class="absolute inset-y-0 left-0 w-[3px]"
            :class="accentClass"
            aria-hidden="true"
        ></span>

        <div class="flex items-start justify-between gap-3">
            <div
                class="flex items-center justify-center w-10 h-10 rounded"
                :class="iconBgClass"
            >
                <component
                    :is="icon_title"
                    class="w-5 h-5"
                    :class="iconColorClass"
                />
            </div>

            <ArrowRight20Regular
                v-if="href"
                class="w-4 h-4 text-slate-300 transition-all duration-150 group-hover:translate-x-0.5 group-hover:text-[#2A126C]"
            />
        </div>

        <p
            class="mt-4 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500"
        >
            {{ title }}
        </p>

        <div class="mt-1 flex items-baseline gap-1.5">
            <p
                class="text-4xl font-bold text-slate-900 tabular-nums leading-none"
            >
                {{ formattedTotal }}
            </p>
            <span class="text-[11px] font-medium text-slate-400 lowercase">
                {{ unit }}
            </span>
        </div>

        <div
            class="mt-3 flex items-center gap-1.5 border-t border-slate-100 pt-3 text-xs font-medium text-slate-500"
        >
            <component
                :is="icon_status"
                class="w-3.5 h-3.5 shrink-0"
                :class="iconColorClass"
            />
            {{ title_status }}
        </div>
    </component>
</template>

<script setup lang="ts">
import { computed, type Component } from "vue";
import { Link } from "@inertiajs/vue3";
import { ArrowRight20Regular } from "@vicons/fluent";

interface StatisticInfo {
    title: string;
    total: number;
    unit?: string;
    href?: string;
    icon_title: Component;
    icon_status: Component;
    title_status: string;
    base_color: string;
}

const props = defineProps<StatisticInfo>();

const palettes: Record<
    string,
    { accent: string; iconBg: string; iconColor: string }
> = {
    blue: {
        accent: "bg-blue-600",
        iconBg: "bg-blue-50",
        iconColor: "text-blue-700",
    },
    emerald: {
        accent: "bg-emerald-600",
        iconBg: "bg-emerald-50",
        iconColor: "text-emerald-700",
    },
    amber: {
        accent: "bg-amber-500",
        iconBg: "bg-amber-50",
        iconColor: "text-amber-700",
    },
};

const palette = computed(
    () =>
        palettes[props.base_color] ?? {
            accent: "bg-slate-400",
            iconBg: "bg-slate-100",
            iconColor: "text-slate-600",
        },
);

const accentClass = computed(() => palette.value.accent);
const iconBgClass = computed(() => palette.value.iconBg);
const iconColorClass = computed(() => palette.value.iconColor);
const unit = computed(() => props.unit ?? "item");
const formattedTotal = computed(() =>
    new Intl.NumberFormat("id-ID").format(props.total),
);
</script>
