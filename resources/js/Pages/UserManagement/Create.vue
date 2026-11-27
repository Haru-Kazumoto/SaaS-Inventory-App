<template>
    <div class="flex flex-col gap-5">
        <TitlePage
            eyebrow="Manajemen Pengguna"
            title="Tambah Pengguna"
            subTitle="Buat akun baru untuk admin jurusan atau superadmin."
        >
            <template #action>
                <n-button class="w-full sm:w-auto" @click="goBack">
                    <template #icon><n-icon :component="ArrowLeft20Regular" /></template>
                    Kembali
                </n-button>
            </template>
        </TitlePage>

        <UserForm
            mode="create"
            :form="form"
            :majors="majors"
            :roles="roles"
            :processing="form.processing"
            @submit="submit"
            @cancel="goBack"
        />
    </div>
</template>

<script setup lang="ts">
import { router, useForm } from "@inertiajs/vue3";
import { NButton, NIcon, useNotification } from "naive-ui";
import { ArrowLeft20Regular } from "@vicons/fluent";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TitlePage from "@/Components/TitlePage.vue";
import UserForm from "@/Components/Feature/Users/UserForm.vue";

defineOptions({ layout: AuthenticatedLayout });

defineProps<{
    majors: Array<{ name: string; code: string }>;
    roles: Array<{ name: string }>;
}>();

const notification = useNotification();

const form = useForm({
    username: "",
    fullname: "",
    password: "",
    email: "",
    role: "",
    major: "",
    phone: "",
    avatar: null as File | null,
});

function goBack() {
    router.visit(route("user.index"));
}

function submit() {
    form.post(route("user.store"), {
        forceFormData: true,
        onSuccess: () => notification.success({ title: "Pengguna berhasil dibuat", duration: 3000 }),
        onError: () =>
            notification.error({
                title: "Gagal membuat pengguna",
                content: "Periksa kembali data yang ditandai merah.",
                duration: 5000,
                closable: true,
            }),
    });
}
</script>
