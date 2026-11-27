<template>
    <div class="flex flex-col gap-5">
        <TitlePage eyebrow="Penagihan" title="Pembayaran" subTitle="Semua pembayaran yang diterima dari organisasi." />

        <DataTable
            :data="payments.data"
            :columns="columns"
            :pagination="payments"
            :loading="loading"
            :get-row-id="(row) => String(row.id)"
            clickable-rows
            item-label="pembayaran"
            min-width="900px"
            empty-title="Belum ada pembayaran"
            :empty-description="hasActive ? 'Tidak ada pembayaran yang cocok dengan filter.' : 'Catat pembayaran dari halaman detail tagihan.'"
            @row-click="(row) => router.visit(route('platform.invoices.show', row.invoice_id))"
            @page-change="apply"
        >
            <template #toolbar>
                <div class="no-scrollbar -mx-4 flex gap-1.5 overflow-x-auto px-4 pb-0.5 lg:mx-0 lg:px-0" role="tablist">
                    <button
                        v-for="opt in METHOD_FILTERS"
                        :key="opt.label"
                        type="button"
                        role="tab"
                        :aria-selected="filters.method === opt.value"
                        class="shrink-0 rounded border px-3 py-1.5 text-xs font-semibold transition-colors duration-150"
                        :class="
                            filters.method === opt.value
                                ? 'border-[#2A126C] bg-[#2A126C] text-white'
                                : 'border-slate-200 bg-white text-slate-600 hover:border-[#2A126C]/40 hover:text-[#2A126C]'
                        "
                        @click="setMethod(opt.value)"
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
                    <n-input v-model:value="filters.q" clearable placeholder="No. tagihan / referensi" class="lg:!w-56">
                        <template #prefix><n-icon :component="Search20Regular" class="text-slate-400" /></template>
                    </n-input>
                </div>
            </template>

            <template #mobile-row="{ row }">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-slate-900">{{ row.organization }}</p>
                        <p class="text-[11px] text-slate-400">
                            <span class="font-mono">{{ row.invoice_number }}</span> · {{ labelOf(PAYMENT_METHODS, row.method) }} ·
                            {{ formatDay(row.paid_at) }}
                        </p>
                    </div>
                    <span class="shrink-0 text-sm font-bold tabular-nums text-emerald-700">+{{ formatIDR(row.amount) }}</span>
                </div>
            </template>
        </DataTable>
    </div>
</template>

<script setup lang="ts">
import { computed, h } from "vue";
import { router } from "@inertiajs/vue3";
import { createColumnHelper } from "@tanstack/vue-table";
import { NIcon, NInput, NSelect } from "naive-ui";
import { Search20Regular } from "@vicons/fluent";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TitlePage from "@/Components/TitlePage.vue";
import DataTable from "@/Components/DataTable/DataTable.vue";
import type { DataTablePagination } from "@/Components/DataTable/types";
import StatusBadge from "@/Components/Feature/Platform/StatusBadge.vue";
import { PAYMENT_METHODS, formatDateTime, formatDay, formatIDR, labelOf } from "@/Components/Feature/Platform/platform-meta";
import { useTableFilters } from "@/Composables/useTableFilters";
import { useFlash } from "@/Composables/useFlash";

defineOptions({ layout: AuthenticatedLayout });

interface PaymentRow {
    id: number;
    invoice_id: number;
    invoice_number: string;
    organization: string;
    amount: number;
    method: string;
    status: string;
    provider: string;
    reference?: string | null;
    paid_at: string;
}

const props = defineProps<{
    payments: DataTablePagination & { data: PaymentRow[] };
    organizations: { label: string; value: number }[];
}>();

useFlash();

const METHOD_FILTERS = [{ label: "Semua", value: null as string | null }, ...PAYMENT_METHODS];
const orgOptions = computed(() => props.organizations.map((o) => ({ label: o.label, value: String(o.value) })));

const { filters, loading, apply, hasActive } = useTableFilters(
    "platform.payments.index",
    { q: null as string | null, method: null as string | null, organization_id: null as string | null },
    { textKeys: ["q"] },
);

function setMethod(v: string | null) {
    if (filters.method === v) return;
    filters.method = v;
    apply();
}

const muted = (t?: string | null) => h("span", { class: "text-[11px] text-slate-400" }, t || "-");
const col = createColumnHelper<PaymentRow>();

const columns = computed(() => [
    col.accessor("paid_at", {
        header: "Tanggal",
        meta: { width: "160px" },
        enableSorting: true,
        cell: ({ getValue }) => h("span", { class: "text-xs tabular-nums text-slate-700" }, formatDateTime(getValue())),
    }),
    col.accessor("organization", {
        header: "Organisasi",
        meta: { minWidth: "200px" },
        enableSorting: true,
        cell: ({ row }) =>
            h("div", { class: "flex flex-col" }, [
                h("span", { class: "text-[13px] font-semibold text-slate-900" }, row.original.organization),
                h("span", { class: "font-mono text-[11px] text-slate-400" }, row.original.invoice_number),
            ]),
    }),
    col.accessor("method", {
        header: "Metode",
        meta: { width: "150px" },
        cell: ({ row }) =>
            h("div", { class: "flex flex-col" }, [
                h("span", { class: "text-xs text-slate-700" }, labelOf(PAYMENT_METHODS, row.original.method)),
                muted(row.original.reference ? `Ref ${row.original.reference}` : row.original.provider),
            ]),
    }),
    col.accessor("status", {
        header: "Status",
        meta: { width: "120px" },
        cell: ({ getValue }) => h(StatusBadge, { kind: "payment", status: getValue() }),
    }),
    col.accessor("amount", {
        header: "Jumlah",
        meta: { width: "150px", align: "right" },
        enableSorting: true,
        cell: ({ getValue }) => h("span", { class: "text-sm font-bold tabular-nums text-emerald-700" }, `+${formatIDR(getValue())}`),
    }),
]);
</script>

<style scoped>
.no-scrollbar { scrollbar-width: none; }
.no-scrollbar::-webkit-scrollbar { display: none; }
</style>
