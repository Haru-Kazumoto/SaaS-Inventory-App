<template>
    <div class="flex flex-col gap-5">
        <TitlePage
            eyebrow="Pergerakan Barang"
            :title="isDurable ? 'Barang Keluar — Tidak Habis Pakai' : 'Barang Keluar — Habis Pakai'"
            :subTitle="
                isDurable
                    ? 'Barang yang masih dipinjam dan belum dikembalikan.'
                    : 'Riwayat pemakaian barang habis pakai.'
            "
        />

        <DataTable
            :data="lines.data"
            :columns="columns"
            :pagination="lines"
            :loading="loading"
            item-label="baris"
            min-width="980px"
            :empty-title="isDurable ? 'Tidak ada barang yang dipinjam' : 'Belum ada pemakaian'"
            :empty-description="
                hasActive
                    ? 'Tidak ada data yang cocok dengan filter saat ini.'
                    : isDurable
                      ? 'Semua barang tidak habis pakai sudah kembali.'
                      : 'Pemakaian barang habis pakai akan muncul di sini.'
            "
            @page-change="apply"
        >
            <template #toolbar>
                <div class="grid w-full grid-cols-1 gap-2 sm:grid-cols-2 lg:flex lg:items-center">
                    <n-input
                        v-model:value="filters.borrower_name"
                        clearable
                        placeholder="Cari nama peminjam..."
                        class="lg:!w-60"
                    >
                        <template #prefix>
                            <n-icon :component="Search20Regular" class="text-slate-400" />
                        </template>
                    </n-input>
                    <n-input
                        v-model:value="filters.product_code"
                        clearable
                        placeholder="Kode barang"
                        class="lg:!w-40"
                    >
                        <template #prefix>
                            <n-icon :component="Tag20Regular" class="text-slate-400" />
                        </template>
                    </n-input>
                    <n-select
                        v-model:value="filters.warehouse_id"
                        :options="warehouseOptions"
                        placeholder="Semua gudang"
                        clearable
                        filterable
                        class="lg:!w-48"
                        @update:value="apply()"
                    />
                    <n-date-picker
                        v-model:formatted-value="filters.date"
                        value-format="yyyy-MM-dd"
                        format="dd MMM yyyy"
                        type="date"
                        clearable
                        placeholder="Tanggal keluar"
                        class="lg:!w-44"
                        @update:formatted-value="apply()"
                    />
                </div>
                <div class="flex shrink-0 gap-2">
                    <n-button v-if="hasActive" class="flex-1 sm:flex-none" @click="reset">
                        Reset filter
                    </n-button>
                    <RefreshButton
                        class="flex-1 sm:flex-none"
                        :refresh-props="['lines', 'warehouses']"
                    />
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
                                <span class="font-mono">{{ row.product_code }}</span>
                                <span class="text-slate-300"> · </span>
                                {{ row.warehouse_name || "-" }}
                            </p>
                        </div>
                        <span
                            class="shrink-0 rounded border px-2 py-0.5 text-sm font-bold tabular-nums"
                            :class="
                                isDurable
                                    ? 'border-amber-200 bg-amber-50 text-amber-800'
                                    : 'border-slate-200 bg-slate-50 text-slate-800'
                            "
                        >
                            {{ row.outstanding_qty }}
                            <span class="text-[11px] font-normal opacity-70">{{ row.uom_name }}</span>
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-2 text-xs text-slate-500">
                        <span class="truncate">
                            <span class="text-slate-400">Oleh</span>
                            <span class="font-medium text-slate-700"> {{ row.borrower_name }}</span>
                        </span>
                        <span class="shrink-0 tabular-nums">
                            {{ when(row).date }} · {{ when(row).time }}
                        </span>
                    </div>
                </div>
            </template>
        </DataTable>
    </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { createColumnHelper } from "@tanstack/vue-table";
import { NButton, NDatePicker, NIcon, NInput, NSelect } from "naive-ui";
import { Search20Regular, Tag20Regular } from "@vicons/fluent";
import TitlePage from "@/Components/TitlePage.vue";
import RefreshButton from "@/Components/RefreshButton.vue";
import DataTable from "@/Components/DataTable/DataTable.vue";
import type { DataTablePagination } from "@/Components/DataTable/types";
import {
    codeCell,
    dateCell,
    personCell,
    productCell,
    qtyCell,
    rowNumberCell,
    textCell,
} from "./line-cells";
import { formatDateParts } from "@/Components/Feature/Borrowing/borrow-status";
import { useTableFilters } from "@/Composables/useTableFilters";
import type { OutgoingLine } from "./types";


const props = defineProps<{
    kind: "durable" | "consumable";
    lines: DataTablePagination & { data: OutgoingLine[] };
    warehouses?: Array<{ id: number; name: string }>;
}>();

const isDurable = computed(() => props.kind === "durable");

const { filters, loading, apply, hasActive, reset } = useTableFilters(
    props.kind === "durable" ? "movements.loaned" : "movements.consumed",
    {
        borrower_name: null as string | null,
        product_code: null as string | null,
        warehouse_id: null as string | null,
        date: null as string | null,
    },
    { textKeys: ["borrower_name", "product_code"] },
);

const warehouseOptions = computed(() =>
    (props.warehouses ?? []).map((w) => ({ label: w.name, value: String(w.id) })),
);

const when = (row: OutgoingLine) =>
    formatDateParts(row.borrowed_at ?? row.created_at);

const col = createColumnHelper<OutgoingLine>();

const columns = computed(() => [
    col.display({
        id: "index",
        header: "#",
        meta: { width: "52px", align: "center" },
        cell: ({ row }) => rowNumberCell(props.lines, row.index),
    }),
    col.accessor("borrower_name", {
        header: "Peminjam",
        meta: { minWidth: "200px" },
        enableSorting: true,
        cell: ({ getValue }) => personCell(getValue()),
    }),
    col.accessor("product_name", {
        header: "Barang",
        meta: { minWidth: "220px" },
        enableSorting: true,
        cell: ({ row }) => productCell(row.original.product_name, row.original.category_name),
    }),
    col.accessor("product_code", {
        header: "Kode",
        meta: { width: "130px" },
        cell: ({ getValue }) => codeCell(getValue()),
    }),
    col.accessor("warehouse_name", {
        header: "Asal gudang",
        meta: { minWidth: "140px" },
        cell: ({ getValue }) => textCell(getValue()),
    }),
    col.accessor((r) => Number(r.outstanding_qty) || 0, {
        id: "qty",
        header: isDurable.value ? "Belum kembali" : "Jumlah",
        meta: { width: "130px", align: "right" },
        enableSorting: true,
        cell: ({ row }) =>
            qtyCell(
                row.original.outstanding_qty,
                row.original.uom_name,
                isDurable.value ? "amber" : "slate",
            ),
    }),
    col.accessor((r) => r.borrowed_at ?? r.created_at ?? "", {
        id: "when",
        header: "Waktu keluar",
        meta: { width: "130px" },
        enableSorting: true,
        cell: ({ getValue }) => dateCell(getValue()),
    }),
]);
</script>
