<template>
    <Head title="Organisasi tidak aktif" />
    <main class="flex min-h-screen items-center justify-center bg-slate-50 px-4">
        <section class="w-full max-w-md rounded border border-slate-200 bg-white p-8 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded bg-rose-50 text-rose-600">
                <n-icon :component="LockClosed24Regular" size="26" />
            </div>
            <p class="mt-4 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#2A126C]">{{ organization.name }}</p>
            <h1 class="mt-1 text-xl font-bold tracking-tight text-slate-900">
                {{ organization.status === "cancelled" ? "Langganan telah berhenti" : "Akses ditangguhkan sementara" }}
            </h1>
            <p class="mt-2 text-sm leading-relaxed text-slate-600">
                Akun organisasi Anda sedang tidak aktif sehingga aplikasi belum bisa digunakan.
                Data Anda tetap aman. Silakan hubungi pemilik organisasi atau tim kami untuk mengaktifkannya kembali.
            </p>
            <n-button class="mt-6" block @click="logout">
                <template #icon><n-icon :component="SignOut24Regular" /></template>
                Keluar
            </n-button>
        </section>
    </main>
</template>

<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3";
import { NButton, NIcon } from "naive-ui";
import { LockClosed24Regular, SignOut24Regular } from "@vicons/fluent";

defineProps<{ organization: { name: string; status: string } }>();

function logout() {
    router.post(route("logout"));
}
</script>
