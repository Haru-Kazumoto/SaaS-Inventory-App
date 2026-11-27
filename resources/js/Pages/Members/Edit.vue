<template>
    <div class="flex flex-col gap-5">
        <TitlePage eyebrow="Anggota" title="Ubah Anggota" :subTitle="`Perbarui data akun ${member.fullname}.`">
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
            :units="units"
            :can-assign-owner="canAssignOwner"
            :initial-avatar="member.profile_image_url"
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
import type { MemberFormState, MemberRow } from "@/Components/Feature/Users/types";

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps<{
    units: Array<{ id: number; name: string }>;
    canAssignOwner: boolean;
    member: MemberRow & { memberships: Array<{ unit_id: number; role: MemberFormState["memberships"][number]["role"] }> };
}>();

const form = useForm({
    fullname: props.member.fullname ?? "",
    username: props.member.username ?? "",
    email: props.member.email ?? "",
    phone: props.member.phone ?? "",
    password: "",
    organization_role: (props.member.organization_role ?? "member") as MemberFormState["organization_role"],
    memberships: (props.member.memberships.length
        ? props.member.memberships.map((m) => ({ ...m }))
        : [{ unit_id: null, role: "staff" }]) as MemberFormState["memberships"],
    avatar: null as File | null,
});

function goBack() {
    router.visit(route("members.index"));
}

function submit() {
    form.transform((d) => ({ ...d, _method: "PUT", memberships: d.memberships.filter((m) => m.unit_id) })).post(
        route("members.update", props.member.id),
        { preserveScroll: true, forceFormData: true },
    );
}
</script>
