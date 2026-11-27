<template>
    <div class="flex flex-col gap-5">
        <TitlePage
            eyebrow="Pergerakan Barang"
            title="Barang Dihapuskan"
            subTitle="Barang rusak, hilang, atau pensiun yang telah dikeluarkan dari gudang dan tidak lagi dihitung sebagai stok."
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
            :data="writeOffs.data"
            :columns="columns"
            :pagination="writeOffs"
            :loading="loading"
            :get-row-id="(row) => String(row.log_id)"
            clickable-rows
            item-label="penghapusan"
            min-width="1100px"
            empty-title="Belum ada barang dihapuskan"
            :empty-description="
                hasActive ? 'Tidak ada data yang cocok dengan pencarian.' : 'Barang yang dikeluarkan dari gudang akan tercatat di sini.'
            "
            @row-click="goDetail"
            @page-change="apply"
        >
            <template #toolbar>
                <div class="grid w-full grid-cols-1 gap-2 sm:grid-cols-2 lg:flex lg:items-center">
                    <n-input v-model:value="filters.product_name" clearable placeholder="Cari nama barang..." class="lg:!w-72">
                        <template #prefix><n-icon :component="Search20Regular" class="text-slate-400" /></template>
                    </n-input>
                    <n-select
                        v-if="unitOptions.length > 1"
                        v-model:value="filters.unit_id"
                        :options="unitOptions"
                        :placeholder="`Semua ${t('unit').toLowerCase()}`"
                        clearable
                        class="lg:!w-48"
                        @update:value="apply()"
                    />
                </div>
                <RefreshButton class="w-full sm:w-auto" :refresh-props="['writeOffs']" />
            </template>

            <template #mobile-row="{ row }">
                <div class="flex flex-col gap-2.5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ row.product_name }}</p>
                            <p class="mt-0.5 truncate text-xs text-slate-500">
                                <span class="font-mono">{{ row.code }}</span>
                                <span class="text-slate-300"> · </span>{{ row.warehouse_name || "-" }}
                            </p>
                        </div>
                        <span class="shrink-0 rounded border border-rose-200 bg-rose-50 px-2 py-0.5 text-sm font-bold tabular-nums text-rose-700">
                            {{ row.quantity }}
                            <span class="text-[11px] font-normal opacity-70">{{ row.uom_name }}</span>
                        </span>
                    </div>
                    <p v-if="row.reason" class="text-xs text-slate-600">Alasan: {{ row.reason }}</p>
                    <p class="text-[11px] tabular-nums text-slate-400">
                        Dihapuskan {{ formatDateParts(row.occurred_at).date }}
                        <template v-if="row.created_by_name"> oleh {{ row.created_by_name }}</template>
                    </p>
                </div>
            </template>
        </DataTable>
    </div>
</template>

<script setup lang="ts">
import { computed, h } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { createColumnHelper, type ColumnDef } from "@tanstack/vue-table";
import { NIcon, NInput, NSelect } from "naive-ui";
import { Search20Regular } from "@vicons/fluent";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TitlePage from "@/Components/TitlePage.vue";
import RefreshButton from "@/Components/RefreshButton.vue";
import ExportExcelButton from "@/Components/ExportExcelButton.vue";
import DataTable from "@/Components/DataTable/DataTable.vue";
import type { DataTablePagination } from "@/Components/DataTable/types";
import { codeCell, dateCell, productCell, qtyCell, textCell } from "@/Components/Feature/Lines/line-cells";
import { formatDateParts } from "@/Components/Feature/Borrowing/borrow-status";
import { useTableFilters } from "@/Composables/useTableFilters";
import { useTerms } from "@/Composables/useTerms";
import { useFlash } from "@/Composables/useFlash";

defineOptions({ layout: AuthenticatedLayout });

interface WriteOffRow {
    log_id: number;
    product_id: number;
    product_name: string;
    code: string;
    brand_name: string | null;
    category_name: string | null;
    unit_name: string;
    warehouse_name: string;
    quantity: number;
    uom_name: string | null;
    occurred_at: string;
    reason: string | null;
    created_by_name: string | null;
}

defineProps<{ writeOffs: DataTablePagination & { data: WriteOffRow[] } }>();

useFlash();
const { t } = useTerms();
const page = usePage();
const unitOptions = computed(() =>
    ((page.props as any).units ?? []).map((u: { id: number; name: string }) => ({ label: u.name, value: String(u.id) })),
);

const { filters, loading, apply, hasActive } = useTableFilters(
    "movements.write-offs",
    { product_name: null as string | null, unit_id: null as string | null },
    { textKeys: ["product_name"] },
);

function goDetail(row: WriteOffRow) {
    router.visit(route("products.show", row.product_id));
}

function handleExport() {
    window.open(route("movements.write-offs.export", filters.unit_id ? { unit_id: filters.unit_id } : {}), "_blank");
}

const col = createColumnHelper<WriteOffRow>();

const columns = computed(() => {
    const list: ColumnDef<WriteOffRow, any>[] = [
        col.accessor("product_name", {
            header: "Barang",
            meta: { minWidth: "220px" },
            enableSorting: true,
            cell: ({ row }) => productCell(row.original.product_name, row.original.category_name),
        }),
        col.accessor("code", { header: "Kode", meta: { width: "130px" }, cell: ({ getValue }) => codeCell(getValue()) }),
    ];

    if (unitOptions.value.length > 1) {
        list.push(col.accessor("unit_name", { header: t("unit"), meta: { width: "120px" }, cell: ({ getValue }) => textCell(getValue()) }));
    }

    list.push(
        col.accessor("warehouse_name", { header: "Asal gudang", meta: { minWidth: "140px" }, cell: ({ getValue }) => textCell(getValue()) }),
        col.accessor("quantity", {
            header: "Jumlah",
            meta: { width: "120px", align: "right" },
            enableSorting: true,
            cell: ({ row }) => qtyCell(row.original.quantity, row.original.uom_name, "slate"),
        }),
        col.accessor("reason", {
            header: "Alasan",
            meta: { minWidth: "200px" },
            cell: ({ row }) =>
                h("div", { class: "flex flex-col" }, [
                    h("span", { class: "line-clamp-2 text-xs text-slate-700" }, row.original.reason || "-"),
                    row.original.created_by_name ? h("span", { class: "text-[11px] text-slate-400" }, `oleh ${row.original.created_by_name}`) : null,
                ]),
        }),
        col.accessor("occurred_at", {
            header: "Dihapuskan",
            meta: { width: "130px" },
            enableSorting: true,
            cell: ({ getValue }) => dateCell(getValue()),
        }),
    );

    return list;
});
</script>
