<template>
    <div class="flex flex-col gap-5">
        <TitlePage
            eyebrow="Platform"
            title="Pengguna"
            subTitle="Semua akun di seluruh organisasi. Gunakan untuk bantuan akun, mis. reset password."
        />

        <DataTable
            :data="users.data"
            :columns="columns"
            :pagination="users"
            :loading="loading"
            :get-row-id="(row) => String(row.id)"
            item-label="pengguna"
            min-width="900px"
            empty-title="Tidak ada pengguna"
            :empty-description="hasActive ? 'Tidak ada pengguna yang cocok dengan filter.' : 'Belum ada akun terdaftar.'"
            @page-change="apply"
        >
            <template #toolbar>
                <div class="grid w-full grid-cols-1 gap-2 sm:grid-cols-3 lg:flex lg:items-center">
                    <n-input v-model:value="filters.q" clearable placeholder="Cari nama, username, email..." class="lg:!w-72">
                        <template #prefix><n-icon :component="Search20Regular" class="text-slate-400" /></template>
                    </n-input>
                    <n-select
                        v-model:value="filters.organization_id"
                        :options="orgOptions"
                        placeholder="Semua organisasi"
                        clearable
                        filterable
                        class="lg:!w-56"
                        @update:value="apply()"
                    />
                    <n-select
                        v-model:value="filters.role"
                        :options="ROLE_OPTIONS"
                        placeholder="Semua role"
                        clearable
                        class="lg:!w-44"
                        @update:value="apply()"
                    />
                </div>
            </template>

            <template #mobile-row="{ row }">
                <div class="flex flex-col gap-3">
                    <div class="flex items-center gap-3">
                        <img v-if="row.profile_image_url" :src="row.profile_image_url" alt="" class="h-9 w-9 shrink-0 rounded-full border border-slate-200 object-cover" />
                        <span v-else class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-[11px] font-semibold text-slate-600">{{ initials(row.fullname) }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ row.fullname }}</p>
                            <p class="truncate text-xs text-slate-500">@{{ row.username }} · {{ row.organization || "Tanpa organisasi" }}</p>
                        </div>
                    </div>
                    <n-button :disabled="row.id === currentUserId" @click="askReset(row)">
                        <template #icon><n-icon :component="Key20Regular" /></template>
                        Reset password
                    </n-button>
                </div>
            </template>
        </DataTable>

        <!-- Konfirmasi reset -->
        <n-modal
            v-model:show="showConfirm"
            preset="card"
            :bordered="false"
            title="Reset password?"
            class="!w-[calc(100vw-2rem)] max-w-md !rounded"
            :segmented="{ footer: true }"
        >
            <p class="text-sm text-slate-600">
                Password <b class="text-slate-900">{{ selected?.fullname }}</b> (@{{ selected?.username }}) akan diganti dengan
                password acak baru. Password lama tidak bisa dipakai lagi.
            </p>
            <template #footer>
                <div class="flex justify-end gap-2">
                    <n-button :disabled="resetting" @click="showConfirm = false">Batal</n-button>
                    <n-button type="primary" :loading="resetting" @click="doReset">Ya, reset</n-button>
                </div>
            </template>
        </n-modal>

        <!-- Hasil password baru -->
        <n-modal
            v-model:show="showPassword"
            preset="card"
            :bordered="false"
            title="Password baru"
            class="!w-[calc(100vw-2rem)] max-w-md !rounded"
            :segmented="{ footer: true }"
        >
            <p class="mb-3 text-sm text-slate-600">
                Berikan password ini ke pengguna. Password <b>hanya ditampilkan sekali</b>.
            </p>
            <div class="flex items-center gap-2 rounded border border-slate-200 bg-slate-50 px-3 py-2">
                <code class="flex-1 select-all font-mono text-base font-bold tracking-wider text-slate-900">{{ newPassword }}</code>
                <n-button size="small" @click="copy">
                    <template #icon><n-icon :component="Copy20Regular" /></template>
                    {{ copied ? "Tersalin" : "Salin" }}
                </n-button>
            </div>
            <template #footer>
                <div class="flex justify-end"><n-button type="primary" @click="showPassword = false">Selesai</n-button></div>
            </template>
        </n-modal>
    </div>
</template>

<script setup lang="ts">
import { computed, h, ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { createColumnHelper } from "@tanstack/vue-table";
import { NButton, NIcon, NInput, NModal, NSelect } from "naive-ui";
import { Copy20Regular, Key20Regular, Search20Regular } from "@vicons/fluent";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TitlePage from "@/Components/TitlePage.vue";
import DataTable from "@/Components/DataTable/DataTable.vue";
import type { DataTablePagination } from "@/Components/DataTable/types";
import { formatDay, initials } from "@/Components/Feature/Platform/platform-meta";
import { useTableFilters } from "@/Composables/useTableFilters";
import { useFlash } from "@/Composables/useFlash";

defineOptions({ layout: AuthenticatedLayout });

interface UserRow {
    id: number;
    fullname: string;
    username: string;
    email?: string | null;
    phone?: string | null;
    role?: string | null;
    role_code?: string | null;
    organization?: string | null;
    unit?: string | null;
    profile_image_url: string | null;
    created_at?: string | null;
}

const props = defineProps<{
    users: DataTablePagination & { data: UserRow[] };
    organizations: { label: string; value: number }[];
}>();

useFlash();
const page = usePage();
const currentUserId = (page.props as any).auth?.user?.id;

const ROLE_OPTIONS = [
    { label: "Pemilik", value: "owner" },
    { label: "Admin organisasi", value: "admin" },
    { label: "Anggota unit", value: "member" },
    { label: "Pengelola platform", value: "platform" },
];

const orgOptions = computed(() => [
    { label: "Tanpa organisasi", value: "none" },
    ...props.organizations.map((o) => ({ label: o.label, value: String(o.value) })),
]);

const { filters, loading, apply, hasActive } = useTableFilters(
    "platform.users.index",
    { q: null as string | null, organization_id: null as string | null, role: null as string | null },
    { textKeys: ["q"] },
);

/* ---------------- Reset password ---------------- */
const showConfirm = ref(false);
const showPassword = ref(false);
const resetting = ref(false);
const selected = ref<UserRow | null>(null);
const newPassword = ref("");
const copied = ref(false);

function askReset(row: UserRow) {
    selected.value = row;
    showConfirm.value = true;
}

function doReset() {
    if (!selected.value) return;
    router.post(route("platform.users.reset-password", selected.value.id), {}, {
        preserveScroll: true,
        onStart: () => (resetting.value = true),
        onFinish: () => (resetting.value = false),
        onSuccess: (p) => {
            showConfirm.value = false;
            const pwd = (p.props as any).flash?.generated_password;
            if (pwd) {
                newPassword.value = pwd;
                copied.value = false;
                showPassword.value = true;
            }
        },
    });
}

async function copy() {
    try {
        await navigator.clipboard.writeText(newPassword.value);
        copied.value = true;
    } catch {
        copied.value = false;
    }
}

/* ---------------- Kolom ---------------- */
const muted = (t?: string | null) => h("span", { class: "text-[11px] text-slate-400" }, t || "-");
const col = createColumnHelper<UserRow>();

const columns = computed(() => [
    col.accessor("fullname", {
        header: "Pengguna",
        meta: { minWidth: "240px" },
        enableSorting: true,
        cell: ({ row }) =>
            h("div", { class: "flex items-center gap-3" }, [
                row.original.profile_image_url
                    ? h("img", { src: row.original.profile_image_url, alt: "", class: "h-8 w-8 shrink-0 rounded-full border border-slate-200 object-cover" })
                    : h("span", { class: "flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-[11px] font-semibold text-slate-600" }, initials(row.original.fullname)),
                h("div", { class: "flex min-w-0 flex-col" }, [
                    h("span", { class: "truncate text-[13px] font-semibold text-slate-900" }, row.original.fullname),
                    muted(`@${row.original.username} · ${row.original.email ?? "-"}`),
                ]),
            ]),
    }),
    col.accessor("organization", {
        header: "Organisasi",
        meta: { minWidth: "180px" },
        enableSorting: true,
        cell: ({ row }) =>
            h("div", { class: "flex flex-col" }, [
                h("span", { class: row.original.organization ? "text-xs font-medium text-slate-800" : "text-xs italic text-slate-400" }, row.original.organization ?? "Tanpa organisasi"),
                row.original.unit ? muted(row.original.unit) : null,
            ]),
    }),
    col.accessor("role", {
        header: "Role",
        meta: { width: "150px" },
        cell: ({ getValue }) =>
            h("span", { class: "whitespace-nowrap rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-[11px] font-semibold text-slate-600" }, (getValue() ?? "-").replace(/_/g, " ")),
    }),
    col.accessor("created_at", {
        header: "Terdaftar",
        meta: { width: "120px" },
        enableSorting: true,
        cell: ({ getValue }) => h("span", { class: "text-xs tabular-nums text-slate-600" }, formatDay(getValue())),
    }),
    col.display({
        id: "action",
        header: "Aksi",
        meta: { width: "160px", align: "right", sticky: true },
        cell: ({ row }) =>
            h(
                NButton,
                {
                    size: "small",
                    disabled: row.original.id === currentUserId,
                    onClick: () => askReset(row.original),
                },
                { icon: () => h(NIcon, null, { default: () => h(Key20Regular) }), default: () => "Reset password" },
            ),
    }),
]);
</script>
