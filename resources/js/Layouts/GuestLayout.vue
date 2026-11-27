<template>
    <n-config-provider :theme-overrides="themeConfig">
        <body class="min-h-screen font-sans antialiased bg-indigo-200 overflow-hidden">
            <slot />
        </body>
    </n-config-provider>
</template>

<script lang="ts">
import { NConfigProvider } from 'naive-ui';
import { defineComponent } from 'vue';
import { themeConfig } from '@/Utils/global-theme-config';

export default defineComponent({
    setup() {
        return {
            themeConfig
        }
    },
    components: {
        NConfigProvider
    }
});
</script>
