<template>
    <transition name="fade">
        <div v-if="visible"
            class="fixed inset-0 z-[9999] flex items-center justify-center backdrop-blur-sm bg-black/20">
            <div class="flex flex-col items-center">
                <!-- Logo -->
                 <n-image width="200" src="/images/logo-yatindo-hd.png"/>
                <!-- <img src="/images/logo-yatindo-hd.png" alt="Logo" class="w-16 h-16 animate-bounce mb-4" /> -->
                <!-- Teks Loading -->
                <span class="text-white text-lg font-semibold">Loading...</span>
            </div>
        </div>
    </transition>
</template>

<script lang="ts">
import { NImage } from 'naive-ui';
import { defineComponent } from 'vue';

export default defineComponent({
    props: {
        visible: { type: Boolean, required: true}
    },
    components: {
        NImage,
    }
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
