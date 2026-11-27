<template>
    <div class="flex flex-col gap-5">
        <TitlePage
            eyebrow="Pergerakan Barang"
            title="Barang Masuk"
            subTitle="Riwayat penambahan stok (restock) ke gudang."
        />

        <DataTable
            :data="restocks.data"
            :columns="columns"
            :pagination="restocks"
            :loading="loading"
            :get-row-id="(row) => String(row.log_id)"
            item-label="barang masuk"
            min-width="960px"
            empty-title="Belum ada barang masuk"
            :empty-description="
                hasActive
                    ? 'Tidak ada data yang cocok dengan filter saat ini.'
                    : 'Riwayat restock barang akan muncul di sini.'
            "
            @page-change="apply"
        >
            <template #toolbar>
                <div class="grid w-full grid-cols-1 gap-2 sm:grid-cols-2 lg:flex lg:items-center">
                    <n-input
                        v-model:value="filters.product_name"
                        clearable
                        placeholder="Cari nama barang..."
                        class="lg:!w-64"
                    >
                        <template #prefix>
                            <n-icon :component="Search20Regular" class="text-slate-400" />
                        </template>
                    </n-input>
                    <n-select
                        v-if="unitOptions.length > 1"
                        v-model:value="filters.unit_id"
                        :options="unitOptions"
                        :placeholder="`Semua ${t('unit').toLowerCase()}`"
                        clearable
                        filterable
                        class="lg:!w-44"
                        @update:value="apply()"
                    />
                    <n-select
                        v-model:value="filters.product_type"
                        :options="TYPE_OPTIONS"
                        placeholder="Semua tipe"
                        clearable
                        class="lg:!w-44"
                        @update:value="apply()"
                    />
                    <n-date-picker
                        v-model:formatted-value="filters.date"
                        value-format="yyyy-MM-dd"
                        format="dd MMM yyyy"
                        type="date"
                        clearable
                        placeholder="Tanggal masuk"
                        class="lg:!w-44"
                        @update:formatted-value="apply()"
                    />
                </div>
                <div class="flex shrink-0 gap-2">
                    <n-button v-if="hasActive" class="flex-1 sm:flex-none" @click="reset">
                        Reset filter
                    </n-button>
                    <RefreshButton class="flex-1 sm:flex-none" :refresh-props="['restocks']" />
                </div>
            </template>

            <template #mobile-row="{ row }">
                <div class="flex flex-col gap-2.5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-900">
                                {{ row.product_name }}
                            </p>
                            <p class="mt-0.5 truncate text-xs text-slate-500">
                                <span class="font-mono">{{ row.code }}</span>
                                <span class="text-slate-300"> · </span>
                                {{ row.warehouse_name || "-" }}
                            </p>
                        </div>
                        <span
                            class="shrink-0 rounded border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-sm font-bold tabular-nums text-emerald-800"
                        >
                            +{{ row.quantity }}
                            <span class="text-[11px] font-normal opacity-70">{{ row.uom_name }}</span>
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-2 text-xs text-slate-500">
                        <span class="flex items-center gap-2">
                            <TypeBadge :type="row.type" />
                            <span v-if="unitOptions.length > 1" class="font-medium text-slate-600">{{ row.unit_name }}</span>
                        </span>
                        <span class="shrink-0 tabular-nums">
                            {{ formatDateParts(row.occurred_at).date }} ·
                            {{ formatDateParts(row.occurred_at).time }}
                        </span>
                    </div>
                </div>
            </template>
        </DataTable>
    </div>
</template>

<script setup lang="ts">
import { computed, h } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useTerms } from "@/Composables/useTerms";
import { useFlash } from "@/Composables/useFlash";
import { createColumnHelper, type ColumnDef } from "@tanstack/vue-table";
import { NButton, NDatePicker, NIcon, NInput, NSelect } from "naive-ui";
import { Search20Regular } from "@vicons/fluent";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TitlePage from "@/Components/TitlePage.vue";
import RefreshButton from "@/Components/RefreshButton.vue";
import DataTable from "@/Components/DataTable/DataTable.vue";
import type { DataTablePagination } from "@/Components/DataTable/types";
import TypeBadge from "@/Components/Feature/Products/TypeBadge.vue";
import {
    codeCell,
    dateCell,
    productCell,
    qtyCell,
    rowNumberCell,
    textCell,
} from "@/Components/Feature/Lines/line-cells";
import { formatDateParts } from "@/Components/Feature/Borrowing/borrow-status";
import { useTableFilters } from "@/Composables/useTableFilters";

defineOptions({ layout: AuthenticatedLayout });

interface RestockLog {
    log_id: number;
    move_type: string;
    quantity: number;
    product_name: string;
    code: string;
    type: string;
    unit_name: string;
    reason?: string | null;
    created_by_name?: string | null;
    warehouse_name?: string | null;
    uom_name?: string | null;
    occurred_at: string;
}

const props = defineProps<{
    restocks: DataTablePagination & { data: RestockLog[] };
}>();

const TYPE_OPTIONS = [
    { label: "Tidak habis pakai", value: "DURABLE" },
    { label: "Habis pakai", value: "CONSUMABLE" },
];

const { filters, loading, apply, hasActive, reset } = useTableFilters(
    "movements.restocks",
    {
        product_name: null as string | null,
        unit_id: null as string | null,
        product_type: null as string | null,
        date: null as string | null,
    },
    { textKeys: ["product_name"] },
);

useFlash();
const page = usePage();
const { t } = useTerms();
const unitOptions = computed(() =>
    ((page.props as any).units ?? []).map((u: { id: number; name: string }) => ({ label: u.name, value: String(u.id) })),
);

const col = createColumnHelper<RestockLog>();

const columns = computed(() => {
    const list: ColumnDef<RestockLog, any>[] = [
        col.display({
            id: "index",
            header: "#",
            meta: { width: "52px", align: "center" },
            cell: ({ row }) => rowNumberCell(props.restocks, row.index),
        }),
    ];

    if (unitOptions.value.length > 1) {
        list.push(
            col.accessor("unit_name", {
                header: t("unit"),
                meta: { width: "110px" },
                enableSorting: true,
                cell: ({ getValue }) =>
                    h(
                        "span",
                        {
                            class: "rounded border border-[#2A126C]/15 bg-[#2A126C]/[0.05] px-1.5 py-0.5 text-[11px] font-semibold uppercase text-[#2A126C]",
                        },
                        getValue() || "-",
                    ),
            }),
        );
    }

    list.push(
        col.accessor("product_name", {
            header: "Barang",
            meta: { minWidth: "220px" },
            enableSorting: true,
            cell: ({ row }) => productCell(row.original.product_name, `Satuan: ${row.original.uom_name ?? "-"}`),
        }),
        col.accessor("code", {
            header: "Kode",
            meta: { width: "130px" },
            cell: ({ getValue }) => codeCell(getValue()),
        }),
        col.accessor("type", {
            header: "Tipe",
            meta: { width: "150px" },
            cell: ({ getValue }) => h(TypeBadge, { type: getValue() }),
        }),
        col.accessor("warehouse_name", {
            header: "Gudang tujuan",
            meta: { minWidth: "140px" },
            cell: ({ getValue }) => textCell(getValue()),
        }),
        col.accessor((r) => Number(r.quantity) || 0, {
            id: "quantity",
            header: "Jumlah masuk",
            meta: { width: "130px", align: "right" },
            enableSorting: true,
            cell: ({ row }) =>
                qtyCell(row.original.quantity, row.original.uom_name, "emerald", "+"),
        }),
        col.accessor("occurred_at", {
            header: "Waktu masuk",
            meta: { width: "130px" },
            enableSorting: true,
            cell: ({ getValue }) => dateCell(getValue()),
        }),
    );

    return list;
});
</script>
