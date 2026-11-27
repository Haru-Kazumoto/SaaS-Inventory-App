<template>
    <div class="flex flex-col gap-5">
        <TitlePage eyebrow="Penagihan" title="Tagihan" subTitle="Semua invoice yang diterbitkan ke organisasi.">
            <template #action>
                <n-button type="primary" class="w-full sm:w-auto" @click="showCreate = true">
                    <template #icon><n-icon :component="Add20Regular" /></template>
                    Buat Tagihan
                </n-button>
            </template>
        </TitlePage>

        <DataTable
            :data="invoices.data"
            :columns="columns"
            :pagination="invoices"
            :loading="loading"
            :get-row-id="(row) => String(row.id)"
            clickable-rows
            item-label="tagihan"
            min-width="900px"
            empty-title="Tidak ada tagihan"
            :empty-description="hasActive ? 'Tidak ada tagihan yang cocok dengan filter.' : 'Tagihan dibuat otomatis saat organisasi berlangganan paket berbayar.'"
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
                        @click="setFilter('status', opt.value)"
                    >
                        {{ opt.label }}
                    </button>
                </div>
                <div class="grid w-full grid-cols-1 gap-2 sm:grid-cols-2 lg:flex lg:w-auto">
                    <n-select
                        v-model:value="filters.organization_id"
                        :options="orgOptions"
                        placeholder="Semua organisasi"
                        clearable
                        filterable
                        class="lg:!w-52"
                        @update:value="apply()"
                    />
                    <n-input v-model:value="filters.q" clearable placeholder="No. tagihan / organisasi" class="lg:!w-56">
                        <template #prefix><n-icon :component="Search20Regular" class="text-slate-400" /></template>
                    </n-input>
                </div>
            </template>

            <template #mobile-row="{ row }">
                <div class="flex flex-col gap-1.5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ row.organization }}</p>
                            <p class="font-mono text-[11px] text-slate-400">{{ row.number }}</p>
                        </div>
                        <StatusBadge kind="invoice" :status="row.display_status" />
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Jatuh tempo {{ formatDay(row.due_at) }}</span>
                        <span class="text-sm font-bold tabular-nums text-slate-900">{{ formatIDR(row.total) }}</span>
                    </div>
                </div>
            </template>
        </DataTable>

        <IssueInvoiceModal v-model:show="showCreate" :organizations="organizations" />
    </div>
</template>

<script setup lang="ts">
import { computed, h, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { createColumnHelper } from "@tanstack/vue-table";
import { NButton, NIcon, NInput, NSelect } from "naive-ui";
import { Add20Regular, Search20Regular } from "@vicons/fluent";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TitlePage from "@/Components/TitlePage.vue";
import DataTable from "@/Components/DataTable/DataTable.vue";
import type { DataTablePagination } from "@/Components/DataTable/types";
import StatusBadge from "@/Components/Feature/Platform/StatusBadge.vue";
import IssueInvoiceModal from "@/Components/Feature/Platform/IssueInvoiceModal.vue";
import { formatDay, formatIDR } from "@/Components/Feature/Platform/platform-meta";
import { useTableFilters } from "@/Composables/useTableFilters";
import { useFlash } from "@/Composables/useFlash";

defineOptions({ layout: AuthenticatedLayout });

interface InvoiceRow {
    id: number;
    number: string;
    organization: string;
    display_status: string;
    total: number;
    paid: number;
    remaining: number;
    issued_at: string;
    due_at: string;
}

const props = defineProps<{
    invoices: DataTablePagination & { data: InvoiceRow[] };
    organizations: { label: string; value: number }[];
}>();

useFlash();
const showCreate = ref(false);

const STATUS_FILTERS = [
    { label: "Semua", value: null },
    { label: "Belum dibayar", value: "unpaid" },
    { label: "Terlambat", value: "overdue" },
    { label: "Lunas", value: "paid" },
    { label: "Dibatalkan", value: "void" },
] as const;

const orgOptions = computed(() => props.organizations.map((o) => ({ label: o.label, value: String(o.value) })));

const { filters, loading, apply, hasActive } = useTableFilters(
    "platform.invoices.index",
    { q: null as string | null, status: null as string | null, organization_id: null as string | null },
    { textKeys: ["q"] },
);

function setFilter(key: "status", v: string | null) {
    if (filters[key] === v) return;
    filters[key] = v;
    apply();
}

function goShow(id: number) {
    router.visit(route("platform.invoices.show", id));
}

const col = createColumnHelper<InvoiceRow>();
const columns = computed(() => [
    col.accessor("number", {
        header: "No. tagihan",
        meta: { width: "170px" },
        enableSorting: true,
        cell: ({ getValue }) => h("span", { class: "font-mono text-[13px] font-semibold text-slate-900" }, getValue()),
    }),
    col.accessor("organization", {
        header: "Organisasi",
        meta: { minWidth: "200px" },
        enableSorting: true,
        cell: ({ getValue }) => h("span", { class: "text-[13px] text-slate-800" }, getValue()),
    }),
    col.accessor("issued_at", {
        header: "Terbit",
        meta: { width: "120px" },
        enableSorting: true,
        cell: ({ getValue }) => h("span", { class: "text-xs tabular-nums text-slate-600" }, formatDay(getValue())),
    }),
    col.accessor("due_at", {
        header: "Jatuh tempo",
        meta: { width: "120px" },
        enableSorting: true,
        cell: ({ row }) =>
            h(
                "span",
                { class: `text-xs tabular-nums ${row.original.display_status === "overdue" ? "font-semibold text-rose-600" : "text-slate-600"}` },
                formatDay(row.original.due_at),
            ),
    }),
    col.accessor("display_status", {
        header: "Status",
        meta: { width: "130px" },
        cell: ({ getValue }) => h(StatusBadge, { kind: "invoice", status: getValue() }),
    }),
    col.accessor("total", {
        header: "Total",
        meta: { width: "150px", align: "right" },
        enableSorting: true,
        cell: ({ row }) =>
            h("div", { class: "flex flex-col items-end" }, [
                h("span", { class: "text-sm font-bold tabular-nums text-slate-900" }, formatIDR(row.original.total)),
                row.original.paid && row.original.remaining
                    ? h("span", { class: "text-[11px] text-amber-700" }, `sisa ${formatIDR(row.original.remaining)}`)
                    : null,
            ]),
    }),
]);
</script>

<style scoped>
.no-scrollbar { scrollbar-width: none; }
.no-scrollbar::-webkit-scrollbar { display: none; }
</style>
