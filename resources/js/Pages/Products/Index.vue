<template>
    <div class="flex flex-col gap-5">
        <TitlePage eyebrow="Barang" title="Daftar Barang" subTitle="Kelola, cari, dan pantau seluruh barang beserta stoknya.">
            <template #action>
                <div class="flex w-full flex-wrap items-center gap-3 sm:w-auto">
                    <div class="hidden flex-col text-right sm:flex">
                        <span class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">Total</span>
                        <span class="text-sm font-bold tabular-nums text-slate-800">{{ products.total }} barang</span>
                    </div>
                    <ExportExcelButton
                        class="flex-1 sm:flex-none"
                        caption="Laporan bulanan · .xlsx"
                        title="Unduh laporan bulanan barang habis pakai"
                        @click="showExport = true"
                    />
                    <n-button v-if="canCreate" type="primary" class="flex-1 sm:flex-none" @click="goCreate">
                        <template #icon><n-icon :component="Add20Regular" /></template>
                        Tambah Barang
                    </n-button>
                </div>
            </template>
        </TitlePage>

        <DataTable
            :data="products.data"
            :columns="columns"
            :pagination="products"
            :loading="loading"
            :get-row-id="(row) => String(row.id)"
            clickable-rows
            item-label="barang"
            min-width="1100px"
            empty-title="Tidak ada barang"
            :empty-description="hasActive ? 'Tidak ada barang yang cocok dengan filter.' : 'Barang yang ditambahkan akan muncul di sini.'"
            @row-click="goDetail"
            @page-change="apply"
        >
            <template #toolbar>
                <div class="no-scrollbar -mx-4 flex gap-1.5 overflow-x-auto px-4 pb-0.5 lg:mx-0 lg:px-0" role="tablist">
                    <button
                        v-for="opt in TYPE_FILTERS"
                        :key="opt.label"
                        type="button"
                        role="tab"
                        :aria-selected="filters.type === opt.value"
                        class="shrink-0 rounded border px-3 py-1.5 text-xs font-semibold transition-colors duration-150"
                        :class="
                            filters.type === opt.value
                                ? 'border-[#2A126C] bg-[#2A126C] text-white'
                                : 'border-slate-200 bg-white text-slate-600 hover:border-[#2A126C]/40 hover:text-[#2A126C]'
                        "
                        @click="setFilter('type', opt.value)"
                    >
                        {{ opt.label }}
                    </button>
                </div>

                <div class="grid w-full grid-cols-1 gap-2 sm:grid-cols-3 lg:flex lg:w-auto lg:items-center">
                    <n-select
                        v-if="unitOptions.length > 1"
                        v-model:value="filters.unit_id"
                        :options="unitOptions"
                        :placeholder="`Semua ${t('unit').toLowerCase()}`"
                        clearable
                        class="lg:!w-44"
                        @update:value="apply()"
                    />
                    <n-input v-model:value="filters.name" clearable placeholder="Cari nama barang..." class="lg:!w-56">
                        <template #prefix><n-icon :component="Search20Regular" class="text-slate-400" /></template>
                    </n-input>
                    <n-input v-model:value="filters.code" clearable placeholder="Kode barang" class="lg:!w-36">
                        <template #prefix><n-icon :component="Tag20Regular" class="text-slate-400" /></template>
                    </n-input>
                    <RefreshButton :refresh-props="['products']" />
                </div>
            </template>

            <template #mobile-row="{ row }">
                <div class="flex flex-col gap-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ row.name }}</p>
                            <p class="mt-0.5 truncate text-xs text-slate-500">
                                <span class="font-mono">{{ row.code }}</span>
                                <span class="text-slate-300"> · </span>{{ row.category_name || "-" }}
                                <template v-if="unitOptions.length > 1"><span class="text-slate-300"> · </span>{{ row.unit_name }}</template>
                            </p>
                        </div>
                        <TypeBadge :type="row.type" />
                    </div>
                    <div class="flex items-center justify-between rounded border border-slate-100 bg-slate-50/60 px-3 py-2">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">Stok</p>
                            <p class="text-sm font-bold tabular-nums text-slate-900">
                                {{ formatQty(row.quantity_on_hand) }}
                                <span class="text-[11px] font-normal text-slate-400">{{ row.uom_name ?? "" }}</span>
                            </p>
                        </div>
                        <StockBadge :qty="row.quantity_on_hand" />
                    </div>
                    <div class="grid gap-2" :class="row.can_manage ? 'grid-cols-3' : 'grid-cols-1'" @click.stop>
                        <n-button type="primary" secondary @click="goDetail(row)">
                            <template #icon><n-icon :component="Eye20Regular" /></template>
                            Detail
                        </n-button>
                        <template v-if="row.can_manage">
                            <n-button @click="goEdit(row)">
                                <template #icon><n-icon :component="Edit20Regular" /></template>
                                Edit
                            </n-button>
                            <n-button type="error" ghost @click="askDelete(row)">
                                <template #icon><n-icon :component="Delete20Regular" /></template>
                                Hapus
                            </n-button>
                        </template>
                    </div>
                </div>
            </template>
        </DataTable>

        <!-- Export laporan bulanan -->
        <n-modal
            v-model:show="showExport"
            preset="card"
            :bordered="false"
            title="Laporan Bulanan Barang"
            class="!w-[calc(100vw-2rem)] max-w-md !rounded"
            :segmented="{ content: true, footer: true }"
        >
            <p class="mb-3 text-sm text-slate-600">
                Saldo awal, barang masuk & keluar per hari, dan saldo akhir <b>barang habis pakai</b>, satu sheet per kategori.
            </p>
            <div class="flex flex-col gap-3">
                <label class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-slate-600">Bulan</span>
                    <n-date-picker v-model:value="exportMonth" type="month" format="MMMM yyyy" :is-date-disabled="(ts: number) => ts > Date.now()" />
                </label>
                <label v-if="unitOptions.length > 1" class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-slate-600">{{ t("unit") }}</span>
                    <n-select v-model:value="exportUnit" :options="unitOptions" :placeholder="`Semua ${t('unit').toLowerCase()}`" clearable />
                </label>
            </div>
            <template #footer>
                <div class="flex justify-end gap-2">
                    <n-button @click="showExport = false">Batal</n-button>
                    <n-button type="primary" @click="downloadExport">
                        <template #icon><n-icon :component="ArrowDownload20Regular" /></template>
                        Download
                    </n-button>
                </div>
            </template>
        </n-modal>

        <!-- Konfirmasi hapus -->
        <n-modal
            v-model:show="showDelete"
            preset="card"
            :bordered="false"
            title="Hapus barang?"
            class="!w-[calc(100vw-2rem)] max-w-md !rounded"
            :segmented="{ footer: true }"
        >
            <p class="text-sm text-slate-600">
                <b class="text-slate-900">{{ selected?.name }}</b> akan disembunyikan dari daftar. Riwayat stok dan peminjamannya tetap tersimpan.
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
import { computed, h, ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { createColumnHelper, type ColumnDef } from "@tanstack/vue-table";
import { NButton, NDatePicker, NIcon, NInput, NModal, NSelect } from "naive-ui";
import {
    Add20Regular,
    ArrowDownload20Regular,
    Delete20Regular,
    Edit20Regular,
    Eye20Regular,
    Search20Regular,
    Tag20Regular,
} from "@vicons/fluent";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TitlePage from "@/Components/TitlePage.vue";
import ExportExcelButton from "@/Components/ExportExcelButton.vue";
import RefreshButton from "@/Components/RefreshButton.vue";
import DataTable from "@/Components/DataTable/DataTable.vue";
import type { DataTablePagination } from "@/Components/DataTable/types";
import StockBadge from "@/Components/Feature/Products/StockBadge.vue";
import TypeBadge from "@/Components/Feature/Products/TypeBadge.vue";
import { useTableFilters } from "@/Composables/useTableFilters";
import { useTerms } from "@/Composables/useTerms";
import { useFlash } from "@/Composables/useFlash";

defineOptions({ layout: AuthenticatedLayout });

interface ProductRow {
    id: number;
    name: string;
    code: string;
    brand_name?: string | null;
    type: string;
    unit_id: number;
    unit_name?: string | null;
    category_name?: string | null;
    uom_name?: string | null;
    quantity_on_hand: number;
    can_manage: boolean;
}

defineProps<{
    products: DataTablePagination & { data: ProductRow[] };
    canCreate: boolean;
}>();

useFlash();
const { t } = useTerms();
const page = usePage();
const unitOptions = computed(() =>
    ((page.props as any).units ?? []).map((u: { id: number; name: string }) => ({ label: u.name, value: String(u.id) })),
);

/* ---------------- Filter ---------------- */
const TYPE_FILTERS = [
    { label: "Semua", value: null },
    { label: "Tidak habis pakai", value: "DURABLE" },
    { label: "Habis pakai", value: "CONSUMABLE" },
] as const;

const { filters, loading, apply, hasActive } = useTableFilters(
    "products.index",
    { name: null as string | null, code: null as string | null, type: null as string | null, unit_id: null as string | null },
    { textKeys: ["name", "code"] },
);

function setFilter(key: "type", value: string | null) {
    if (filters[key] === value) return;
    filters[key] = value;
    apply();
}

/* ---------------- Aksi ---------------- */
function goCreate() {
    router.visit(route("products.create"));
}
function goDetail(row: ProductRow) {
    router.visit(route("products.show", row.id));
}
function goEdit(row: ProductRow) {
    router.visit(route("products.edit", row.id));
}

const showDelete = ref(false);
const deleting = ref(false);
const selected = ref<ProductRow | null>(null);

function askDelete(row: ProductRow) {
    selected.value = row;
    showDelete.value = true;
}
function confirmDelete() {
    if (!selected.value) return;
    router.delete(route("products.destroy", selected.value.id), {
        preserveScroll: true,
        onStart: () => (deleting.value = true),
        onFinish: () => (deleting.value = false),
        onSuccess: () => (showDelete.value = false),
    });
}

/* ---------------- Export ---------------- */
const showExport = ref(false);
const exportMonth = ref<number>(Date.now());
const exportUnit = ref<string | null>(null);

function downloadExport() {
    const d = new Date(exportMonth.value);
    const params: Record<string, string | number> = { year: d.getFullYear(), month: d.getMonth() + 1 };
    if (exportUnit.value) params.unit_id = exportUnit.value;
    window.open(route("products.export", params), "_blank");
    showExport.value = false;
}

/* ---------------- Kolom ---------------- */
const formatQty = (qty: number | string) => (Number(qty) || 0).toLocaleString("id-ID");
const muted = (text?: string | null) => h("span", { class: "text-[11px] text-slate-400" }, text || "-");

const actionButton = (icon: any, label: string, onClick: () => void, variant: "primary" | "default" | "danger" = "default") =>
    h(
        NButton,
        {
            size: "small",
            onClick,
            ...(variant === "primary" ? { type: "primary", secondary: true } : {}),
            ...(variant === "danger" ? { type: "error", ghost: true } : {}),
        },
        { icon: () => h(NIcon, null, { default: () => h(icon) }), default: () => label },
    );

const col = createColumnHelper<ProductRow>();

const columns = computed(() => {
    const list: ColumnDef<ProductRow, any>[] = [
        col.accessor("name", {
            header: "Barang",
            meta: { minWidth: "240px" },
            enableSorting: true,
            cell: ({ row }) =>
                h("div", { class: "flex min-w-0 flex-col" }, [
                    h("span", { class: "truncate text-[13px] font-semibold text-slate-900" }, row.original.name),
                    muted(row.original.category_name),
                ]),
        }),
        col.accessor("code", {
            header: "Kode",
            meta: { width: "130px" },
            cell: ({ getValue }) =>
                h("span", { class: "whitespace-nowrap rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 font-mono text-[11px] text-slate-600" }, getValue()),
        }),
        col.accessor("type", {
            header: "Tipe",
            meta: { width: "150px" },
            cell: ({ getValue }) => h(TypeBadge, { type: getValue() }),
        }),
    ];

    if (unitOptions.value.length > 1) {
        list.push(
            col.accessor("unit_name", {
                header: t("unit"),
                meta: { width: "120px" },
                enableSorting: true,
                cell: ({ getValue }) => h("span", { class: "text-xs font-medium text-slate-700" }, getValue() || "-"),
            }),
        );
    }

    list.push(
        col.accessor("brand_name", {
            header: "Merk",
            meta: { minWidth: "110px" },
            cell: ({ getValue }) => h("span", { class: "text-xs text-slate-600" }, getValue() || "-"),
        }),
        col.accessor((r) => Number(r.quantity_on_hand) || 0, {
            id: "quantity_on_hand",
            header: "Stok",
            meta: { width: "190px", align: "right" },
            enableSorting: true,
            cell: ({ row }) =>
                h("div", { class: "flex items-center justify-end gap-3" }, [
                    h("span", { class: "whitespace-nowrap" }, [
                        h("span", { class: "text-sm font-bold tabular-nums text-slate-900" }, formatQty(row.original.quantity_on_hand)),
                        h("span", { class: "ml-1 text-[11px] text-slate-400" }, row.original.uom_name ?? ""),
                    ]),
                    h(StockBadge, { qty: row.original.quantity_on_hand }),
                ]),
        }),
        col.display({
            id: "action",
            header: "Aksi",
            meta: { width: "270px", align: "right", sticky: true },
            cell: ({ row }) =>
                h("div", { class: "flex items-center justify-end gap-1.5", onClick: (e: MouseEvent) => e.stopPropagation() }, [
                    actionButton(Eye20Regular, "Detail", () => goDetail(row.original), "primary"),
                    row.original.can_manage ? actionButton(Edit20Regular, "Edit", () => goEdit(row.original)) : null,
                    row.original.can_manage ? actionButton(Delete20Regular, "Hapus", () => askDelete(row.original), "danger") : null,
                ]),
        }),
    );

    return list;
});
</script>

<style scoped>
.no-scrollbar { scrollbar-width: none; }
.no-scrollbar::-webkit-scrollbar { display: none; }
</style>
