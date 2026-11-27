<template>
    <div class="flex flex-col gap-5">
        <TitlePage
            eyebrow="Organisasi"
            title="Anggota"
            :subTitle="`Atur siapa saja yang bisa mengakses aplikasi dan ${t('unit_plural').toLowerCase()} yang dikelolanya.`"
        >
            <template #action>
                <n-button type="primary" class="w-full sm:w-auto" @click="goCreate">
                    <template #icon><n-icon :component="PersonAdd20Regular" /></template>
                    Tambah Anggota
                </n-button>
            </template>
        </TitlePage>

        <DataTable
            :data="filtered"
            :columns="columns"
            :get-row-id="(row) => String(row.id)"
            min-width="900px"
            :empty-title="search ? 'Tidak ditemukan' : 'Belum ada anggota'"
            :empty-description="
                search
                    ? 'Tidak ada anggota yang cocok dengan pencarian.'
                    : 'Klik “Tambah Anggota” untuk membuat akun anggota.'
            "
        >
            <template #toolbar>
                <n-input
                    v-model:value="search"
                    clearable
                    placeholder="Cari nama, username, email, telepon..."
                    class="w-full lg:!w-80"
                >
                    <template #prefix>
                        <n-icon :component="Search20Regular" class="text-slate-400" />
                    </template>
                </n-input>
            </template>

            <template #mobile-row="{ row }">
                <div class="flex flex-col gap-3">
                    <div class="flex items-center gap-3">
                        <Avatar :user="row" />
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ row.fullname }}</p>
                            <p class="truncate text-xs text-slate-500">@{{ row.username }}</p>
                        </div>
                        <span
                            class="shrink-0 rounded border px-1.5 py-0.5 text-[11px] font-semibold"
                            :class="roleBadgeClass(row.organization_role)"
                        >
                            {{ roleLabel(row.organization_role) }}
                        </span>
                    </div>
                    <div class="grid grid-cols-1 gap-1 text-xs text-slate-600">
                        <span class="flex items-center gap-1.5 truncate">
                            <n-icon :component="Call16Regular" class="text-slate-400" />{{ row.phone || "-" }}
                        </span>
                        <span class="flex items-center gap-1.5 truncate">
                            <n-icon :component="Mail16Regular" class="text-slate-400" />{{ row.email || "-" }}
                        </span>
                    </div>
                    <div v-if="row.units.length" class="flex flex-wrap gap-1">
                        <span v-for="u in row.units" :key="u.id" class="rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-[11px] text-slate-600">
                            {{ u.name }} · {{ UNIT_ROLE_LABEL[u.role] ?? u.role }}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <n-button @click="goEdit(row)">
                            <template #icon><n-icon :component="Edit20Regular" /></template>
                            Edit
                        </n-button>
                        <n-button type="error" ghost :disabled="isSelf(row)" @click="openDelete(row)">
                            <template #icon><n-icon :component="Delete20Regular" /></template>
                            Hapus
                        </n-button>
                    </div>
                </div>
            </template>
        </DataTable>

        <!-- KONFIRMASI HAPUS -->
        <n-modal
            v-model:show="showDelete"
            preset="card"
            :bordered="false"
            title="Hapus anggota?"
            class="!w-[calc(100vw-2rem)] max-w-md !rounded"
            :segmented="{ footer: true }"
            :mask-closable="!deleting"
        >
            <div class="flex items-center gap-3">
                <Avatar v-if="selected" :user="selected" />
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-slate-900">{{ selected?.fullname }}</p>
                    <p class="truncate text-xs text-slate-500">
                        @{{ selected?.username }} · {{ roleLabel(selected?.organization_role) }}
                    </p>
                </div>
            </div>
            <p class="mt-4 rounded border border-rose-200 bg-rose-50 px-3 py-2 text-xs leading-relaxed text-rose-700">
                Akun ini tidak bisa login lagi dan seluruh aktivitasnya akan dihapus permanen.
                Tindakan ini tidak dapat dibatalkan.
            </p>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <n-button :disabled="deleting" @click="showDelete = false">Batal</n-button>
                    <n-button type="error" :loading="deleting" @click="confirmDelete">Ya, hapus</n-button>
                </div>
            </template>
        </n-modal>
    </div>
</template>

<script setup lang="ts">
import { computed, defineComponent, h, ref, type PropType } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { createColumnHelper } from "@tanstack/vue-table";
import { NButton, NIcon, NInput, NModal, useMessage } from "naive-ui";
import {
    Call16Regular,
    Delete20Regular,
    Edit20Regular,
    Mail16Regular,
    PersonAdd20Regular,
    Search20Regular,
} from "@vicons/fluent";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TitlePage from "@/Components/TitlePage.vue";
import DataTable from "@/Components/DataTable/DataTable.vue";
import { roleBadgeClass, roleLabel, UNIT_ROLE_LABEL } from "@/Components/Feature/Users/user-meta";
import type { MemberRow } from "@/Components/Feature/Users/types";
import { useTerms } from "@/Composables/useTerms";
import { useFlash } from "@/Composables/useFlash";

defineOptions({ layout: AuthenticatedLayout });

type UserRow = MemberRow;

const props = defineProps<{ members: MemberRow[] }>();

const { t } = useTerms();
useFlash();

const message = useMessage();
const currentUserId = (usePage().props as any).auth?.user?.id;
const isSelf = (row: UserRow) => row.id === currentUserId;

/* ---------------- Avatar kecil ---------------- */
const Avatar = defineComponent({
    props: { user: { type: Object as PropType<UserRow>, required: true } },
    setup(p) {
        return () =>
            h(
                "div",
                {
                    class: "flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full border border-slate-200 bg-slate-50 text-[11px] font-semibold text-slate-600",
                },
                p.user.profile_image_url
                    ? h("img", { src: p.user.profile_image_url, alt: "", class: "h-full w-full object-cover" })
                    : (p.user.fullname || "?")
                          .trim()
                          .split(/\s+/)
                          .slice(0, 2)
                          .map((w) => w[0])
                          .join("")
                          .toUpperCase(),
            );
    },
});

/* ---------------- Filter ---------------- */
const search = ref("");
const filtered = computed(() => {
    const s = search.value.trim().toLowerCase();
    return props.members.filter((u) => {
        if (!s) return true;
        return [u.fullname, u.username, u.email, u.phone].some((v) =>
            (v ?? "").toString().toLowerCase().includes(s),
        );
    });
});

/* ---------------- Aksi ---------------- */
function goCreate() {
    router.visit(route("members.create"));
}
function goEdit(row: UserRow) {
    router.visit(route("members.edit", row.id));
}

const showDelete = ref(false);
const deleting = ref(false);
const selected = ref<UserRow | null>(null);

function openDelete(row: UserRow) {
    selected.value = row;
    showDelete.value = true;
}

function confirmDelete() {
    if (!selected.value) return;
    router.delete(route("members.destroy", selected.value.id), {
        preserveScroll: true,
        onStart: () => (deleting.value = true),
        onFinish: () => (deleting.value = false),
        onSuccess: () => (showDelete.value = false),
        onError: () => message.error("Gagal menghapus anggota."),
    });
}

/* ---------------- Kolom ---------------- */
const muted = (t?: string | null) => h("span", { class: "text-[11px] text-slate-400" }, t || "-");
const col = createColumnHelper<UserRow>();

const columns = computed(() => [
    col.accessor("fullname", {
        header: "Anggota",
        meta: { minWidth: "240px" },
        enableSorting: true,
        cell: ({ row }) =>
            h("div", { class: "flex items-center gap-3" }, [
                h(Avatar, { user: row.original }),
                h("div", { class: "flex min-w-0 flex-col" }, [
                    h("span", { class: "flex items-center gap-1.5" }, [
                        h("span", { class: "truncate text-[13px] font-semibold text-slate-900" }, row.original.fullname),
                        isSelf(row.original)
                            ? h(
                                  "span",
                                  { class: "rounded bg-slate-100 px-1 text-[10px] font-semibold text-slate-500" },
                                  "Anda",
                              )
                            : null,
                    ]),
                    muted(`@${row.original.username}`),
                ]),
            ]),
    }),
    col.display({
        id: "contact",
        header: "Kontak",
        meta: { minWidth: "220px" },
        cell: ({ row }) =>
            h("div", { class: "flex min-w-0 flex-col" }, [
                h("span", { class: "truncate text-xs text-slate-700" }, row.original.email || "-"),
                muted(row.original.phone),
            ]),
    }),
    col.accessor((r) => r.organization_role ?? "", {
        id: "role",
        header: "Peran",
        meta: { width: "150px" },
        enableSorting: true,
        cell: ({ row }) =>
            h(
                "span",
                {
                    class: `whitespace-nowrap rounded border px-1.5 py-0.5 text-[11px] font-semibold ${roleBadgeClass(row.original.organization_role)}`,
                },
                roleLabel(row.original.organization_role),
            ),
    }),
    col.display({
        id: "units",
        header: () => t("unit_plural"),
        meta: { minWidth: "200px" },
        cell: ({ row }) =>
            row.original.organization_role !== "member"
                ? muted(`Semua ${t("unit_plural").toLowerCase()}`)
                : h(
                      "div",
                      { class: "flex flex-wrap gap-1" },
                      row.original.units.length
                          ? row.original.units.map((u) =>
                                h(
                                    "span",
                                    { class: "whitespace-nowrap rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-[11px] text-slate-600" },
                                    `${u.name} · ${UNIT_ROLE_LABEL[u.role] ?? u.role}`,
                                ),
                            )
                          : [muted("Belum ada")],
                  ),
    }),
    col.display({
        id: "action",
        header: "Aksi",
        meta: { width: "180px", align: "right", sticky: true },
        cell: ({ row }) =>
            h("div", { class: "flex justify-end gap-1.5" }, [
                h(
                    NButton,
                    { size: "small", onClick: () => goEdit(row.original) },
                    {
                        icon: () => h(NIcon, null, { default: () => h(Edit20Regular) }),
                        default: () => "Edit",
                    },
                ),
                h(
                    NButton,
                    {
                        size: "small",
                        type: "error",
                        ghost: true,
                        disabled: isSelf(row.original),
                        title: isSelf(row.original) ? "Tidak bisa menghapus akun sendiri" : undefined,
                        onClick: () => openDelete(row.original),
                    },
                    {
                        icon: () => h(NIcon, null, { default: () => h(Delete20Regular) }),
                        default: () => "Hapus",
                    },
                ),
            ]),
    }),
]);
</script>
