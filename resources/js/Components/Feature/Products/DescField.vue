<template>
    <div :class="['flex flex-col gap-1', $attrs.class]">
        <span class="text-[10.5px] font-semibold uppercase tracking-[0.14em] text-slate-400">
            {{ label }}
        </span>
        <div class="mt-0.5">
            <slot />
        </div>
    </div>
</template>

<script setup lang="ts">
defineProps<{ label: string }>();
</script>