<template>
    <div class="flex flex-col gap-5">
        <TitlePage eyebrow="Platform" title="Organisasi" subTitle="Semua pelanggan SaaS beserta paket, status, dan tunggakannya.">
            <template #action>
                <n-button type="primary" class="w-full sm:w-auto" @click="showForm = true">
                    <template #icon><n-icon :component="Add20Regular" /></template>
                    Tambah Organisasi
                </n-button>
            </template>
        </TitlePage>

        <DataTable
            :data="organizations.data"
            :columns="columns"
            :pagination="organizations"
            :loading="loading"
            :get-row-id="(row) => String(row.id)"
            clickable-rows
            item-label="organisasi"
            min-width="980px"
            empty-title="Belum ada organisasi"
            :empty-description="hasActive ? 'Tidak ada organisasi yang cocok dengan filter.' : 'Tambahkan pelanggan pertama.'"
            @row-click="(row) => goShow(row.id)"
            @page-change="apply"
        >
            <template #toolbar>
                <div class="no-scrollbar -mx-4 flex gap-1.5 overflow-x-auto px-4 pb-0.5 lg:mx-0 lg:px-0" role="tablist">
                    <button
                        v-for="opt in STATUS_FILTERS"
                        :key="opt.label"
                        type="button"
                        role="tab"
                        :aria-selected="filters.status === opt.value"
                        class="shrink-0 rounded border px-3 py-1.5 text-xs font-semibold transition-colors duration-150"
                        :class="
                            filters.status === opt.value
                                ? 'border-[#2A126C] bg-[#2A126C] text-white'
                                : 'border-slate-200 bg-white text-slate-600 hover:border-[#2A126C]/40 hover:text-[#2A126C]'
                        "
                        @click="setStatus(opt.value)"
                    >
                        {{ opt.label }}
                    </button>
                </div>
                <n-input v-model:value="filters.q" clearable placeholder="Cari nama, pemilik, email..." class="w-full lg:!w-72">
                    <template #prefix><n-icon :component="Search20Regular" class="text-slate-400" /></template>
                </n-input>
            </template>

            <template #mobile-row="{ row }">
                <div class="flex flex-col gap-2">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ row.name }}</p>
                            <p class="truncate text-xs text-slate-500">{{ row.owner_email || row.owner_name || "-" }}</p>
                        </div>
                        <StatusBadge kind="organization" :status="row.status" />
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>{{ row.plan || "Tanpa paket" }} · {{ row.users_count }} pengguna</span>
                        <span v-if="row.outstanding" class="font-semibold tabular-nums text-amber-700">
                            Tunggakan {{ formatIDR(row.outstanding) }}
                        </span>
                    </div>
                </div>
            </template>
        </DataTable>

        <OrganizationFormModal v-model:show="showForm" :plans="plans" />
    </div>
</template>

<script setup lang="ts">
import { computed, h, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { createColumnHelper } from "@tanstack/vue-table";
import { NButton, NIcon, NInput } from "naive-ui";
import { Add20Regular, Search20Regular } from "@vicons/fluent";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TitlePage from "@/Components/TitlePage.vue";
import DataTable from "@/Components/DataTable/DataTable.vue";
import type { DataTablePagination } from "@/Components/DataTable/types";
import StatusBadge from "@/Components/Feature/Platform/StatusBadge.vue";
import OrganizationFormModal from "@/Components/Feature/Platform/OrganizationFormModal.vue";
import { BILLING_CYCLES, TEMPLATES, formatDay, formatIDR, initials, labelOf } from "@/Components/Feature/Platform/platform-meta";
import { useTableFilters } from "@/Composables/useTableFilters";
import { useFlash } from "@/Composables/useFlash";

defineOptions({ layout: AuthenticatedLayout });

interface OrgRow {
    id: number;
    name: string;
    template: string;
    status: string;
    owner_name?: string | null;
    owner_email?: string | null;
    users_count: number;
    plan?: string | null;
    billing_cycle?: string | null;
    period_end?: string | null;
    outstanding: number;
}

defineProps<{
    organizations: DataTablePagination & { data: OrgRow[] };
    plans: { id: number; name: string; price_monthly: number; price_yearly: number }[];
}>();

useFlash();
const showForm = ref(false);

const STATUS_FILTERS = [
    { label: "Semua", value: null },
    { label: "Aktif", value: "active" },
    { label: "Masa coba", value: "trial" },
    { label: "Menunggak", value: "past_due" },
    { label: "Ditangguhkan", value: "suspended" },
    { label: "Berhenti", value: "cancelled" },
] as const;

const { filters, loading, apply, hasActive } = useTableFilters(
    "platform.organizations.index",
    { q: null as string | null, status: null as string | null },
    { textKeys: ["q"] },
);

function setStatus(v: string | null) {
    if (filters.status === v) return;
    filters.status = v;
    apply();
}

function goShow(id: number) {
    router.visit(route("platform.organizations.show", id));
}

const muted = (t?: string | null) => h("span", { class: "text-[11px] text-slate-400" }, t || "-");
const col = createColumnHelper<OrgRow>();

const columns = computed(() => [
    col.accessor("name", {
        header: "Organisasi",
        meta: { minWidth: "240px" },
        enableSorting: true,
        cell: ({ row }) =>
            h("div", { class: "flex items-center gap-3" }, [
                h(
                    "div",
                    { class: "flex h-9 w-9 shrink-0 items-center justify-center rounded bg-[#2A126C]/[0.06] text-[11px] font-bold text-[#2A126C]" },
                    initials(row.original.name),
                ),
                h("div", { class: "flex min-w-0 flex-col" }, [
                    h("span", { class: "truncate text-[13px] font-semibold text-slate-900" }, row.original.name),
                    muted(row.original.owner_email || row.original.owner_name),
                ]),
            ]),
    }),
    col.accessor("template", {
        header: "Jenis",
        meta: { width: "130px" },
        cell: ({ getValue }) => h("span", { class: "text-xs text-slate-600" }, labelOf(TEMPLATES as any, getValue())),
    }),
    col.accessor("plan", {
        header: "Paket",
        meta: { width: "150px" },
        cell: ({ row }) =>
            h("div", { class: "flex flex-col" }, [
                h("span", { class: "text-xs font-semibold text-slate-800" }, row.original.plan || "—"),
                row.original.plan
                    ? muted(`${labelOf(BILLING_CYCLES as any, row.original.billing_cycle)} · s.d. ${formatDay(row.original.period_end)}`)
                    : null,
            ]),
    }),
    col.accessor("status", {
        header: "Status",
        meta: { width: "130px" },
        cell: ({ getValue }) => h(StatusBadge, { kind: "organization", status: getValue() }),
    }),
    col.accessor("users_count", {
        header: "Pengguna",
        meta: { width: "90px", align: "right" },
        enableSorting: true,
        cell: ({ getValue }) => h("span", { class: "text-sm font-semibold tabular-nums text-slate-900" }, getValue()),
    }),
    col.accessor("outstanding", {
        header: "Tunggakan",
        meta: { width: "130px", align: "right" },
        enableSorting: true,
        cell: ({ getValue }) =>
            getValue()
                ? h("span", { class: "text-sm font-semibold tabular-nums text-amber-700" }, formatIDR(getValue()))
                : h("span", { class: "text-xs text-slate-300" }, "—"),
    }),
    col.display({
        id: "action",
        header: "",
        meta: { width: "90px", align: "right", sticky: true },
        cell: ({ row }) =>
            h(
                NButton,
                { size: "small", type: "primary", secondary: true, onClick: (e: MouseEvent) => (e.stopPropagation(), goShow(row.original.id)) },
                { default: () => "Kelola" },
            ),
    }),
]);
</script>

<style scoped>
.no-scrollbar { scrollbar-width: none; }
.no-scrollbar::-webkit-scrollbar { display: none; }
</style>
