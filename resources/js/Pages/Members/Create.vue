<template>
    <div class="flex flex-col gap-5">
        <TitlePage eyebrow="Anggota" title="Tambah Anggota" subTitle="Buat akun untuk anggota organisasi dan atur aksesnya.">
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
            :units="units"
            :can-assign-owner="canAssignOwner"
            :processing="form.processing"
            @submit="submit"
            @cancel="goBack"
        />
    </div>
</template>

<script setup lang="ts">
import { router, useForm } from "@inertiajs/vue3";
import { NButton, NIcon } from "naive-ui";
import { ArrowLeft20Regular } from "@vicons/fluent";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TitlePage from "@/Components/TitlePage.vue";
import UserForm from "@/Components/Feature/Users/UserForm.vue";
import type { MemberFormState } from "@/Components/Feature/Users/types";

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps<{
    units: Array<{ id: number; name: string }>;
    canAssignOwner: boolean;
}>();

const form = useForm({
    fullname: "",
    username: "",
    email: "",
    phone: "",
    password: "",
    organization_role: "member" as MemberFormState["organization_role"],
    memberships: [{ unit_id: props.units.length === 1 ? props.units[0].id : null, role: "staff" }] as MemberFormState["memberships"],
    avatar: null as File | null,
});

function goBack() {
    router.visit(route("members.index"));
}

function submit() {
    form.transform((d) => ({ ...d, memberships: d.memberships.filter((m) => m.unit_id) })).post(route("members.store"), {
        forceFormData: true,
    });
}
</script>
