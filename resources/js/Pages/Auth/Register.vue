<script lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { NInput, NButton, NDivider, NImage } from 'naive-ui';
import { defineComponent, onMounted } from 'vue';
import { ref } from 'vue';
import TextInput from '@/Components/TextInput.vue';

export default defineComponent({
    setup() {
        const typewriter = ref(null);

        onMounted(() => {
            new Typewriter(typewriter.value, {
                loop: true,
                delay: 50
            })
                .typeString("Solusi inventaris modern")
                .pauseFor(2000)
                .deleteAll()
                .typeString("Kelola stok dengan mudah")
                .pauseFor(2000)
                .deleteAll()
                .typeString("Mudah, Cepat, dan Tepat.")
                .pauseFor(2000)
                .deleteAll()
                .start();
        });

        return {
            typewriter
        }
    },
    components: {
        Head, Link, GuestLayout, NButton, NInput, TextInput, NDivider, NImage
    }
});

</script>

<template>
    <GuestLayout>

        <Head title="Log in" />

        <div class="flex justify-center items-center h-screen">
            <div class="flex flex-col bg-white w-1/2 h-3/4 rounded-md shadow-lg">
                <header class="w-full p-2 h-24 bg-indigo-700 flex rounded-t-md text-white">
                    <div class="flex items-center gap-2">
                        <n-image width="80" src="/images/logo-yatindo-hd.png"></n-image>
                        <div class="flex flex-col">
                            <span class="text-lg">REGISTRASI AKUN</span>
                        </div>
                    </div>
                </header>

                <!-- 
                <div class="w-1/2 px-20 flex flex-col justify-center gap-7">
                    <div>
                        <div class="flex flex-col gap-1">
                            <span class="text-4xl font-semibold">Login</span>
                            <p class="text-xl">Let's get started!</p>
                        </div>
                        <n-divider />
                        <form @submit.prevent="" class="flex flex-col gap-3">
                            <TextInput size="large" label="Username" :ignoreSpace="true" />
                            <TextInput size="large" label="Password" type="password" />
                            <n-button type="info" size="large" :strong="true">Login</n-button>
                        </form>
                    </div>

                    <div class="flex ms-auto items-center gap-1">
                        <span class="text-gray-400 text-xs">Powered By</span>
                        <n-image width="30" src="/images/vendor.png" />
                    </div>
                </div>


                <div class="w-1/2 bg-indigo-700 flex flex-col justify-center items-center rounded-r-md">
                    <img src="/images/logo-yatindo-hd.png" alt="Login Image" class="w-1/2" />
                    <span class="text-3xl text-white font-semibold">INVENTORY MANAGEMENT</span>
                    <p class="text-lg text-white">
                        <span ref="typewriter" class="running-text"></span>
                    </p>
                </div> -->

            </div>
        </div>
    </GuestLayout>
</template>
