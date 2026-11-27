<template>
    <section class="rounded border border-slate-200 bg-white">
        <header
            class="flex items-start gap-3 border-b border-slate-100 px-4 py-4 sm:px-5"
        >
            <span
                v-if="step"
                class="flex h-7 w-7 shrink-0 items-center justify-center rounded bg-[#2A126C] text-xs font-bold text-white"
            >
                {{ step }}
            </span>
            <div class="min-w-0 flex-1">
                <h2 class="text-sm font-bold text-slate-900">{{ title }}</h2>
                <p v-if="description" class="mt-0.5 text-xs text-slate-500">
                    {{ description }}
                </p>
            </div>
            <slot name="aside" />
        </header>
        <div class="p-4 sm:p-5">
            <slot />
        </div>
    </section>
</template>

<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
    step?: number | string;
}>();
</script>
