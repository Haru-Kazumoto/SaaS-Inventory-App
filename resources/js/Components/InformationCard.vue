<template>
    <div
        class="group relative flex items-center p-4 rounded border border-slate-200 border-l-[3px] bg-white transition-colors duration-150"
        :class="[accentClass, clickable ? 'cursor-pointer hover:bg-slate-50/70' : '']"
    >
        <ArrowRight20Regular
            v-if="showArrow"
            class="absolute right-3 top-3 h-4 w-4 text-slate-300 transition-all duration-150 group-hover:translate-x-0.5 group-hover:text-[#2A126C]"
        />

        <div class="flex items-center gap-4 w-full">
            <div
                class="flex items-center justify-center w-11 h-11 rounded shrink-0"
                :class="backgroundColor"
                v-if="icon"
            >
                <n-icon size="22" :class="iconColor" :component="icon" />
            </div>

            <div class="flex flex-col min-w-0">
                <span
                    class="text-[10px] font-semibold text-slate-400 uppercase tracking-[0.14em] block mb-1 truncate"
                >
                    {{ title }}
                </span>
                <div class="flex items-baseline gap-1">
                    <span
                        :class="[
                            'text-2xl font-bold tracking-tight tabular-nums',
                            subTitleColor,
                        ]"
                    >
                        {{ count }}
                    </span>
                    <span class="text-xs font-medium text-slate-400 lowercase">
                        {{ unit }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { computed, defineComponent, type Component } from "vue";
import { NIcon } from "naive-ui";
import { ArrowRight20Regular } from "@vicons/fluent";

const ACCENT_MAP: Record<string, string> = {
    "border-emerald-500": "border-l-emerald-500",
    "border-amber-500": "border-l-amber-500",
    "border-orange-500": "border-l-orange-500",
    "border-violet-500": "border-l-violet-500",
    "border-indigo-500": "border-l-indigo-500",
    "border-rose-500": "border-l-rose-500",
    "border-teal-500": "border-l-teal-500",
    "border-sky-500": "border-l-sky-500",
};

export default defineComponent({
    props: {
        icon: { type: Object as () => Component, default: null },
        title: { type: String, required: true },
        count: { type: Number, required: true },
        unit: { type: String, default: null },
        iconColor: { type: String, default: "text-blue-600" },
        borderColor: { type: String, default: "border-blue-500" },
        backgroundColor: { type: String, default: "bg-blue-50" },
        subTitleColor: { type: String, default: "text-slate-800" },
        showArrow: { type: Boolean, default: false },
        clickable: { type: Boolean, default: false },
    },
    setup(props) {
        const accentClass = computed(
            () => ACCENT_MAP[props.borderColor] ?? "border-l-slate-300",
        );

        return { accentClass };
    },
    components: { NIcon, ArrowRight20Regular },
});
</script>
