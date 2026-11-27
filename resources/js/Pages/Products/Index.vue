<template>
    <div class="flex flex-col gap-5">
        <TitlePage
            :eyebrow="major ? `Gudang ${major.toUpperCase()}` : 'Manajemen Barang'"
            title="Daftar Semua Barang"
            subTitle="Kelola, cari, dan pantau seluruh aset barang dalam gudang."
        >
            <template #action>
                <div class="flex w-full items-center gap-4 sm:w-auto">
                    <div class="hidden flex-col text-right sm:flex">
                        <span
                            class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400"
                            >Total Aset</span
                        >
                        <span class="text-sm font-bold tabular-nums text-slate-800"
                            >{{ products.total }} barang</span
                        >
                    </div>
                    <ExportExcelButton
                        v-if="!createable"
                        class="w-full sm:w-auto"
                        :caption="`Daftar barang${major ? ' ' + major.toUpperCase() : ''} · .xlsx`"
                        title="Unduh daftar barang dalam format Excel"
                        @click="handleExport"
                    />
                    <CreateButton
                        v-if="createable"
                        class="w-full sm:w-auto"
                        buttonText="Tambah Barang"
                        @createRoute="goCreate"
                    />
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
            min-width="1080px"
            empty-title="Tidak ada barang"
            :empty-description="emptyDescription"
            @row-click="goDetail"
            @page-change="onPageChange"
        >
            <!-- TOOLBAR -->
            <template #toolbar>
                <div
                    class="no-scrollbar -mx-4 flex gap-1.5 overflow-x-auto px-4 pb-0.5 lg:mx-0 lg:px-0"
                    role="tablist"
                    aria-label="Filter tipe barang"
                >
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
                        @click="setType(opt.value)"
                    >
                        {{ opt.label }}
                    </button>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <n-input
                        v-model:value="filters.name"
                        clearable
                        placeholder="Cari nama barang..."
                        class="w-full lg:!w-64"
                        @keyup.enter="commit"
                    >
                        <template #prefix>
                            <n-icon :component="Search20Regular" class="text-slate-400" />
                        </template>
                    </n-input>
                    <n-input
                        v-model:value="filters.code"
                        clearable
                        placeholder="Kode barang"
                        class="w-full lg:!w-40"
                        @keyup.enter="commit"
                    >
                        <template #prefix>
                            <n-icon :component="Tag20Regular" class="text-slate-400" />
                        </template>
                    </n-input>
                    <div class="flex shrink-0 gap-2">
                        <RefreshButton
                            class="flex-1 sm:flex-none"
                            :refresh-props="['products', 'createable']"
                        />
                    </div>
                </div>
            </template>

            <!-- MOBILE CARD -->
            <template #mobile-row="{ row }">
                <div class="flex flex-col gap-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-900">
                                {{ row.name }}
                            </p>
                            <p class="mt-0.5 truncate text-xs text-slate-500">
                                <span class="font-mono">{{ row.code }}</span>
                                <span class="text-slate-300"> · </span>
                                {{ row.sub_type_name || "-" }}
                            </p>
                        </div>
                        <TypeBadge :type="row.type" />
                    </div>

                    <div
                        class="flex items-center justify-between rounded border border-slate-100 bg-slate-50/60 px-3 py-2"
                    >
                        <div>
                            <p class="eyebrow">Stok gudang</p>
                            <p class="text-sm font-bold tabular-nums text-slate-900">
                                {{ formatQty(row.quantity_on_hand) }}
                                <span class="text-[11px] font-normal text-slate-400">{{
                                    row.unit_name ?? ""
                                }}</span>
                            </p>
                        </div>
                        <StockBadge :qty="row.quantity_on_hand" />
                    </div>

                    <div class="grid gap-2" :class="createable ? 'grid-cols-3' : 'grid-cols-1'" @click.stop>
                        <n-button type="primary" secondary @click="goDetail(row)">
                            <template #icon>
                                <n-icon :component="Eye20Regular" />
                            </template>
                            Detail
                        </n-button>
                        <template v-if="createable">
                            <n-button @click="goEdit(row)">
                                <template #icon>
                                    <n-icon :component="Edit20Regular" />
                                </template>
                                Edit
                            </n-button>
                            <n-button type="error" ghost @click="handleDelete(row)">
                                <template #icon>
                                    <n-icon :component="Delete20Regular" />
                                </template>
                                Hapus
                            </n-button>
                        </template>
                    </div>
                </div>
            </template>
        </DataTable>
    </div>
</template>

<script setup lang="ts">
import { computed, h, reactive, ref, watch, PropType } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { createColumnHelper } from "@tanstack/vue-table";
import { NButton, NIcon, NInput, useMessage } from "naive-ui";
import {
    Delete20Regular,
    Edit20Regular,
    Eye20Regular,
    Search20Regular,
    Tag20Regular,
} from "@vicons/fluent";
import Swal from "sweetalert2";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TitlePage from "@/Components/TitlePage.vue";
import CreateButton from "@/Components/CreateButton.vue";
import ExportExcelButton from "@/Components/ExportExcelButton.vue";
import RefreshButton from "@/Components/RefreshButton.vue";
import DataTable from "@/Components/DataTable/DataTable.vue";
import type { DataTablePagination } from "@/Components/DataTable/types";
import StockBadge from "@/Components/Feature/Products/StockBadge.vue";
import TypeBadge from "@/Components/Feature/Products/TypeBadge.vue";

defineOptions({ layout: AuthenticatedLayout });

interface ProductRow {
    id: number;
    name: string;
    code: string;
    brand_name?: string | null;
    type?: string | null;
    sub_type_name?: string | null;
    unit_name?: string | null;
    quantity_on_hand: number | string;
}

const props = defineProps({
    products: {
        type: Object as PropType<DataTablePagination & { data: ProductRow[] }>,
        required: true,
    },
    createable: { type: Boolean, required: true },
    major: { type: String, required: false },
});

const message = useMessage();
const pageProps = usePage().props as Record<string, any>;
const loading = ref(false);

/* ---------------- Filter ---------------- */
const TYPE_FILTERS = [
    { label: "Semua", value: null },
    { label: "Tidak habis pakai", value: "DURABLE" },
    { label: "Habis pakai", value: "CONSUMABLE" },
] as const;

const urlParams = new URLSearchParams(window.location.search);
const filters = reactive<{
    name: string | null;
    code: string | null;
    type: string | null;
}>({
    name: urlParams.get("name"),
    code: urlParams.get("code"),
    type: urlParams.get("type"),
});

const emptyDescription = computed(() =>
    filters.name || filters.code || filters.type
        ? "Tidak ada barang yang cocok dengan filter saat ini."
        : "Barang yang ditambahkan akan muncul di sini.",
);

let timer: ReturnType<typeof setTimeout> | undefined;
watch(
    () => [filters.name, filters.code],
    () => {
        clearTimeout(timer);
        timer = setTimeout(commit, 450);
    },
);

function setType(value: string | null) {
    if (filters.type === value) return;
    filters.type = value;
    commit();
}

function commit() {
    clearTimeout(timer);
    pushQuery(1);
}

function onPageChange(page: number) {
    pushQuery(page);
}

function pushQuery(page = 1) {
    const query: Record<string, string | number> = {};
    if (filters.name) query.name = filters.name;
    if (filters.code) query.code = filters.code;
    if (filters.type) query.type = filters.type;
    if (page > 1) query.page = page;

    const url = props.createable
        ? route("products.index")
        : route("products.major", pageProps.major);

    loading.value = true;
    router.get(url, query, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
        onFinish: () => (loading.value = false),
    });
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

function handleExport() {
    window.open(route("products.export", pageProps.major), "_blank");
}

function handleDelete(row: ProductRow) {
    Swal.fire({
        titleText: `Hapus ${row.name}?`,
        text: "Barang akan dihapus namun dapat dikembalikan sewaktu-waktu.",
        showCancelButton: true,
        cancelButtonText: "Batalkan",
        confirmButtonText: "Hapus",
        confirmButtonColor: "#e11d48",
        icon: "question",
    }).then((result) => {
        if (!result.isConfirmed) return;
        router.delete(route("products.destroy", row.id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: (page: any) => {
                router.reload({ only: ["products"] });
                if (page.props?.flash?.success) {
                    message.success(page.props.flash.success);
                }
            },
        });
    });
}

/* ---------------- Kolom ---------------- */
const formatQty = (qty: number | string) =>
    (Number(qty) || 0).toLocaleString("id-ID");

const muted = (text?: string | null) =>
    h("span", { class: "text-[11px] text-slate-400" }, text || "-");

const actionButton = (
    icon: any,
    label: string,
    onClick: () => void,
    variant: "primary" | "default" | "danger" = "default",
) =>
    h(
        NButton,
        {
            size: "small",
            onClick,
            ...(variant === "primary" ? { type: "primary", secondary: true } : {}),
            ...(variant === "danger" ? { type: "error", ghost: true } : {}),
        },
        {
            icon: () => h(NIcon, null, { default: () => h(icon) }),
            default: () => label,
        },
    );

const col = createColumnHelper<ProductRow>();

const columns = computed(() => [
    col.display({
        id: "index",
        header: "#",
        meta: { width: "52px", align: "center" },
        cell: ({ row }) =>
            h(
                "span",
                { class: "text-xs tabular-nums text-slate-400" },
                (props.products.current_page - 1) * props.products.per_page +
                    row.index +
                    1,
            ),
    }),
    col.accessor("name", {
        header: "Barang",
        meta: { minWidth: "240px" },
        enableSorting: true,
        cell: ({ row }) =>
            h("div", { class: "flex min-w-0 flex-col" }, [
                h(
                    "span",
                    { class: "truncate text-[13px] font-semibold text-slate-900" },
                    row.original.name,
                ),
                muted(row.original.sub_type_name),
            ]),
    }),
    col.accessor("code", {
        header: "Kode",
        meta: { width: "130px" },
        cell: ({ getValue }) =>
            h(
                "span",
                {
                    class: "whitespace-nowrap rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 font-mono text-[11px] text-slate-600",
                },
                getValue(),
            ),
    }),
    col.accessor("type", {
        header: "Tipe",
        meta: { width: "160px" },
        cell: ({ getValue }) => h(TypeBadge, { type: getValue() }),
    }),
    col.accessor("brand_name", {
        header: "Merk",
        meta: { minWidth: "120px" },
        cell: ({ getValue }) =>
            h("span", { class: "text-xs text-slate-600" }, getValue() || "-"),
    }),
    col.accessor((row) => Number(row.quantity_on_hand) || 0, {
        id: "quantity_on_hand",
        header: "Stok Gudang",
        meta: { width: "200px", align: "right" },
        enableSorting: true,
        cell: ({ row }) =>
            h("div", { class: "flex items-center justify-end gap-3" }, [
                h("span", { class: "whitespace-nowrap" }, [
                    h(
                        "span",
                        { class: "text-sm font-bold tabular-nums text-slate-900" },
                        formatQty(row.original.quantity_on_hand),
                    ),
                    h(
                        "span",
                        { class: "ml-1 text-[11px] text-slate-400" },
                        row.original.unit_name ?? "",
                    ),
                ]),
                h(StockBadge, { qty: row.original.quantity_on_hand }),
            ]),
    }),
    col.display({
        id: "action",
        header: "Aksi",
        meta: {
            width: props.createable ? "270px" : "110px",
            align: "right",
            sticky: true,
        },
        cell: ({ row }) =>
            h(
                "div",
                {
                    class: "flex items-center justify-end gap-1.5",
                    onClick: (e: MouseEvent) => e.stopPropagation(),
                },
                [
                    actionButton(Eye20Regular, "Detail", () => goDetail(row.original), "primary"),
                    props.createable
                        ? actionButton(Edit20Regular, "Edit", () => goEdit(row.original))
                        : null,
                    props.createable
                        ? actionButton(Delete20Regular, "Hapus", () => handleDelete(row.original), "danger")
                        : null,
                ],
            ),
    }),
]);
</script>

<style scoped>
.eyebrow {
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    color: rgb(148 163 184);
}

.no-scrollbar {
    scrollbar-width: none;
}
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
</style>
