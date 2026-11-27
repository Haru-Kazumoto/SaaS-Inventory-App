<template>
    <div class="flex flex-col gap-5">
        <TitlePage
            eyebrow="Manajemen Pengguna"
            title="Ubah Pengguna"
            :subTitle="`Perbarui data akun ${data.fullname}.`"
        >
            <template #action>
                <n-button class="w-full sm:w-auto" @click="goBack">
                    <template #icon><n-icon :component="ArrowLeft20Regular" /></template>
                    Kembali
                </n-button>
            </template>
        </TitlePage>

        <UserForm
            mode="edit"
            :form="form"
            :majors="majors"
            :roles="roles"
            :initial-avatar="data.profile_image_url"
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

const props = defineProps<{
    majors: Array<{ name: string; code: string }>;
    roles: Array<{ name: string }>;
    data: {
        id: number | string;
        username: string;
        fullname: string;
        email: string;
        phone: string;
        role?: { name: string } | null;
        major?: { code: string } | null;
        profile_image_url?: string | null;
    };
}>();

const notification = useNotification();

const form = useForm({
    username: props.data.username ?? "",
    fullname: props.data.fullname ?? "",
    password: "", // kosong = tidak ganti password
    email: props.data.email ?? "",
    role: props.data.role?.name ?? "",
    major: props.data.major?.code ?? "",
    phone: props.data.phone ?? "",
    avatar: null as File | null,
});

function goBack() {
    router.visit(route("user.index"));
}

function submit() {
    form.transform((d) => ({ ...d, _method: "PUT" })).post(route("user.update", props.data.id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => notification.success({ title: "Perubahan disimpan", duration: 3000 }),
        onError: () =>
            notification.error({
                title: "Gagal menyimpan perubahan",
                content: "Periksa kembali data yang ditandai merah.",
                duration: 5000,
                closable: true,
            }),
    });
}
</script>
