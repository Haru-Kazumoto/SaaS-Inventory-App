<template>
    <div class="flex flex-col gap-5">
        <TitlePage
            :eyebrow="`Penghapusan Barang${major ? ` · ${major.toUpperCase()}` : ''}`"
            title="Barang Dihapuskan"
            subTitle="Barang rusak atau sudah pensiun yang telah dikeluarkan dari gudang dan tidak lagi dihitung sebagai stok."
        >
            <template #action>
                <ExportExcelButton
                    class="w-full sm:w-auto"
                    caption="Daftar barang dihapuskan · .xlsx"
                    title="Unduh daftar barang dihapuskan dalam format Excel"
                    @click="handleExport"
                />
            </template>
        </TitlePage>

        <DataTable
            :data="offProducts.data"
            :columns="columns"
            :pagination="offProducts"
            :loading="loading"
            clickable-rows
            item-label="barang"
            min-width="980px"
            empty-title="Belum ada barang dihapuskan"
            :empty-description="
                hasActive
                    ? 'Tidak ada barang yang cocok dengan pencarian.'
                    : 'Barang yang dikeluarkan dari gudang akan tercatat di sini.'
            "
            @row-click="goDetail"
            @page-change="apply"
        >
            <template #toolbar>
                <n-input
                    v-model:value="filters.name"
                    clearable
                    placeholder="Cari nama barang..."
                    class="w-full lg:!w-72"
                >
                    <template #prefix>
                        <n-icon :component="Search20Regular" class="text-slate-400" />
                    </template>
                </n-input>
                <RefreshButton
                    class="w-full sm:w-auto"
                    :refresh-props="['offProducts']"
                />
            </template>

            <template #mobile-row="{ row }">
                <div class="flex flex-col gap-2.5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-900">
                                {{ row.name }}
                            </p>
                            <p class="mt-0.5 truncate text-xs text-slate-500">
                                <span class="font-mono">{{ row.code }}</span>
                                <span class="text-slate-300"> · </span>
                                {{ row.warehouse_name || "-" }}
                            </p>
                        </div>
                        <span
                            class="shrink-0 rounded border border-rose-200 bg-rose-50 px-2 py-0.5 text-sm font-bold tabular-nums text-rose-700"
                        >
                            {{ qty(row) }}
                            <span class="text-[11px] font-normal opacity-70">{{ row.unit_name }}</span>
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span class="truncate">{{ row.sub_type_name || "-" }}</span>
                        <span class="shrink-0 tabular-nums">
                            Dikeluarkan {{ formatDateParts(row.off_date).date }}
                        </span>
                    </div>
                </div>
            </template>
        </DataTable>
    </div>
</template>

<script setup lang="ts">
import { computed, h } from "vue";
import { router } from "@inertiajs/vue3";
import { createColumnHelper } from "@tanstack/vue-table";
import { NButton, NIcon, NInput } from "naive-ui";
import { Eye20Regular, Search20Regular } from "@vicons/fluent";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TitlePage from "@/Components/TitlePage.vue";
import RefreshButton from "@/Components/RefreshButton.vue";
import ExportExcelButton from "@/Components/ExportExcelButton.vue";
import DataTable from "@/Components/DataTable/DataTable.vue";
import type { DataTablePagination } from "@/Components/DataTable/types";
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

interface OffProduct {
    product_id: number;
    name: string;
    code: string;
    brand_name: string | null;
    sub_type_name: string | null;
    unit_name: string | null;
    warehouse_id: number;
    warehouse_name: string | null;
    off_qty: number | string;
    off_date: string;
}

const props = defineProps<{
    offProducts: DataTablePagination & { data: OffProduct[] };
    major?: string;
}>();

const { filters, loading, apply, hasActive } = useTableFilters(
    "products.indexOff",
    { name: null as string | null },
    { textKeys: ["name"] },
);

/** Jumlah ditampilkan positif apa pun tanda delta di log */
const qty = (row: OffProduct) => Math.abs(Number(row.off_qty) || 0);

function goDetail(row: OffProduct) {
    router.visit(route("products.show", row.product_id));
}

function handleExport() {
    window.open(route("products.exportOff", { major: props.major }), "_blank");
}

const col = createColumnHelper<OffProduct>();

const columns = computed(() => [
    col.display({
        id: "index",
        header: "#",
        meta: { width: "52px", align: "center" },
        cell: ({ row }) => rowNumberCell(props.offProducts, row.index),
    }),
    col.accessor("name", {
        header: "Barang",
        meta: { minWidth: "220px" },
        enableSorting: true,
        cell: ({ row }) => productCell(row.original.name, row.original.sub_type_name),
    }),
    col.accessor("code", {
        header: "Kode",
        meta: { width: "130px" },
        cell: ({ getValue }) => codeCell(getValue()),
    }),
    col.accessor("brand_name", {
        header: "Merk",
        meta: { minWidth: "110px" },
        cell: ({ getValue }) => textCell(getValue()),
    }),
    col.accessor("warehouse_name", {
        header: "Asal gudang",
        meta: { minWidth: "140px" },
        cell: ({ getValue }) => textCell(getValue()),
    }),
    col.accessor((r) => qty(r), {
        id: "off_qty",
        header: "Jumlah",
        meta: { width: "120px", align: "right" },
        enableSorting: true,
        cell: ({ row }) => qtyCell(qty(row.original), row.original.unit_name, "slate"),
    }),
    col.accessor("off_date", {
        header: "Dikeluarkan",
        meta: { width: "130px" },
        enableSorting: true,
        cell: ({ getValue }) => dateCell(getValue()),
    }),
    col.display({
        id: "action",
        header: "Aksi",
        meta: { width: "110px", align: "right", sticky: true },
        cell: ({ row }) =>
            h(
                NButton,
                {
                    size: "small",
                    type: "primary",
                    secondary: true,
                    onClick: (e: MouseEvent) => {
                        e.stopPropagation();
                        goDetail(row.original);
                    },
                },
                {
                    icon: () => h(NIcon, null, { default: () => h(Eye20Regular) }),
                    default: () => "Detail",
                },
            ),
    }),
]);
</script>
