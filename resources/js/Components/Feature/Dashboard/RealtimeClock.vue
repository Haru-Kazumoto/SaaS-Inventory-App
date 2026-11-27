<template>
    <div
        class="flex items-center gap-3 px-4 py-2.5 rounded border transition-colors duration-150"
        :class="
            variant === 'dark'
                ? 'border-white/15 bg-white/[0.06]'
                : 'border-slate-200 bg-white hover:border-slate-300'
        "
    >
        <!-- Icon Container -->
        <div
            class="w-9 h-9 rounded flex items-center justify-center shrink-0"
            :class="
                variant === 'dark'
                    ? 'bg-white/10 text-[#F5D90A]'
                    : 'bg-[#2A126C]/[0.06] text-[#2A126C]'
            "
        >
            <component :is="Clock20Regular" class="w-5 h-5" />
        </div>

        <!-- Time Text -->
        <div class="flex flex-col justify-center text-left">
            <span
                class="text-[10px] uppercase tracking-[0.16em] font-semibold leading-none mb-1"
                :class="variant === 'dark' ? 'text-white/50' : 'text-slate-400'"
            >
                Waktu Sekarang
            </span>
            <span
                class="text-base font-semibold tabular-nums tracking-tight leading-tight"
                :class="variant === 'dark' ? 'text-white' : 'text-slate-900'"
            >
                {{ realtimeClock }}
            </span>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted, ref } from "vue";
import { Clock20Regular } from "@vicons/fluent";

withDefaults(defineProps<{ variant?: "light" | "dark" }>(), {
    variant: "light",
});

const realtimeClock = ref("");

let interval: any = null;

const updateClock = () => {
    realtimeClock.value = new Date().toLocaleTimeString("id-ID", {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
    });
};

onMounted(() => {
    updateClock();

    interval = setInterval(() => {
        updateClock();
    }, 1000);
});

onUnmounted(() => {
    clearInterval(interval);
});
</script>
