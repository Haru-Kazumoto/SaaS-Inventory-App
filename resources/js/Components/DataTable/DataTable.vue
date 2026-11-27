<template>
    <section class="rounded border border-slate-200 bg-white">
        <!-- TOOLBAR (filter, search, dsb) -->
        <div
            v-if="$slots.toolbar"
            class="flex flex-col gap-3 border-b border-slate-100 p-4 lg:flex-row lg:items-center lg:justify-between"
        >
            <slot name="toolbar" />
        </div>

        <n-spin :show="loading">
            <!-- DESKTOP: TABLE -->
            <div :class="$slots['mobile-row'] ? 'hidden lg:block' : ''">
                <div class="overflow-x-auto">
                    <table
                        class="w-full border-collapse text-left"
                        :style="minWidth ? { minWidth } : undefined"
                    >
                        <thead>
                            <tr
                                v-for="headerGroup in table.getHeaderGroups()"
                                :key="headerGroup.id"
                            >
                                <th
                                    v-for="header in headerGroup.headers"
                                    :key="header.id"
                                    :colspan="header.colSpan"
                                    :style="colStyle(header.column.columnDef.meta)"
                                    class="whitespace-nowrap border-b border-slate-100 bg-slate-50 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500 first:pl-5 last:pr-5"
                                    :class="[
                                        alignClass(header.column.columnDef.meta?.align),
                                        header.column.columnDef.meta?.sticky
                                            ? 'sticky right-0 z-[1] shadow-[-8px_0_8px_-8px_rgba(15,23,42,0.12)]'
                                            : '',
                                        header.column.columnDef.meta?.headerClass,
                                    ]"
                                >
                                    <template v-if="!header.isPlaceholder">
                                        <button
                                            v-if="header.column.getCanSort()"
                                            type="button"
                                            class="inline-flex items-center gap-1 uppercase hover:text-[#2A126C]"
                                            @click="header.column.getToggleSortingHandler()?.($event)"
                                        >
                                            <FlexRender
                                                :render="header.column.columnDef.header"
                                                :props="header.getContext()"
                                            />
                                            <n-icon
                                                :component="sortIcon(header.column.getIsSorted())"
                                                size="12"
                                                :class="header.column.getIsSorted() ? 'text-[#2A126C]' : 'text-slate-300'"
                                            />
                                        </button>
                                        <FlexRender
                                            v-else
                                            :render="header.column.columnDef.header"
                                            :props="header.getContext()"
                                        />
                                    </template>
                                </th>
                            </tr>
                        </thead>

                        <tbody v-if="rows.length" class="divide-y divide-slate-100">
                            <tr
                                v-for="row in rows"
                                :key="row.id"
                                class="group transition-colors duration-150 hover:bg-[#2A126C]/[0.03]"
                                :class="clickableRows ? 'cursor-pointer' : ''"
                                @click="clickableRows && emit('row-click', row.original)"
                            >
                                <td
                                    v-for="cell in row.getVisibleCells()"
                                    :key="cell.id"
                                    :style="colStyle(cell.column.columnDef.meta)"
                                    class="px-3 py-2.5 align-middle first:pl-5 last:pr-5"
                                    :class="[
                                        alignClass(cell.column.columnDef.meta?.align),
                                        cell.column.columnDef.meta?.sticky
                                            ? 'sticky right-0 bg-white shadow-[-8px_0_8px_-8px_rgba(15,23,42,0.12)] group-hover:bg-[#faf9fc]'
                                            : '',
                                        cell.column.columnDef.meta?.class,
                                    ]"
                                >
                                    <FlexRender
                                        :render="cell.column.columnDef.cell"
                                        :props="cell.getContext()"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="!rows.length" class="py-6">
                    <slot name="empty">
                        <Empty :title="emptyTitle" :description="emptyDescription" />
                    </slot>
                </div>
            </div>

            <!-- MOBILE / TABLET: CARD LIST -->
            <div v-if="$slots['mobile-row']" class="lg:hidden">
                <ul v-if="rows.length" class="divide-y divide-slate-100">
                    <li
                        v-for="row in rows"
                        :key="row.id"
                        class="p-4"
                        :class="clickableRows ? 'cursor-pointer active:bg-slate-50' : ''"
                        @click="clickableRows && emit('row-click', row.original)"
                    >
                        <slot
                            name="mobile-row"
                            :row="row.original"
                            :index="row.index"
                            :number="rowNumber(row.index)"
                        />
                    </li>
                </ul>
                <div v-else class="py-6">
                    <slot name="empty">
                        <Empty :title="emptyTitle" :description="emptyDescription" />
                    </slot>
                </div>
            </div>
        </n-spin>

        <!-- FOOTER / PAGINATION -->
        <div
            v-if="pagination"
            class="flex flex-col items-center justify-between gap-3 border-t border-slate-100 px-4 py-3 sm:flex-row sm:px-5"
        >
            <span class="text-xs tabular-nums text-slate-500">
                <template v-if="pagination.total">
                    Menampilkan
                    <strong class="text-slate-700">{{ rangeFrom }}–{{ rangeTo }}</strong>
                    dari
                    <strong class="text-slate-700">{{ pagination.total }}</strong>
                    {{ itemLabel }}
                </template>
                <template v-else>Tidak ada data</template>
            </span>

            <n-pagination
                v-if="pagination.last_page > 1"
                :page="pagination.current_page"
                :page-count="pagination.last_page"
                :page-slot="5"
                @update:page="(p: number) => emit('page-change', p)"
            />
        </div>
    </section>
</template>

<script setup lang="ts" generic="TData">
import { computed, ref } from "vue";
import {
    FlexRender,
    getCoreRowModel,
    getSortedRowModel,
    useVueTable,
    type ColumnDef,
    type ColumnMeta,
    type SortingState,
} from "@tanstack/vue-table";
import { NIcon, NPagination, NSpin } from "naive-ui";
import {
    ArrowSort16Regular,
    ArrowSortDown16Regular,
    ArrowSortUp16Regular,
} from "@vicons/fluent";
import Empty from "@/Components/Empty.vue";
import type { DataTablePagination } from "./types";
import "./types";

const props = withDefaults(
    defineProps<{
        /** Data baris yang ditampilkan */
        data: TData[];
        /** Definisi kolom TanStack */
        columns: ColumnDef<TData, any>[];
        /** Fungsi id unik baris (default: index) */
        getRowId?: (row: TData, index: number) => string;
        /** Pagination server-side (Laravel paginator). Kosongkan untuk tanpa footer. */
        pagination?: DataTablePagination | null;
        loading?: boolean;
        /** Emit `row-click` saat baris diklik */
        clickableRows?: boolean;
        /** Label item di footer, mis. "barang" */
        itemLabel?: string;
        /** Lebar minimum tabel sebelum muncul scroll horizontal */
        minWidth?: string;
        emptyTitle?: string;
        emptyDescription?: string;
    }>(),
    {
        pagination: null,
        loading: false,
        clickableRows: false,
        itemLabel: "data",
        minWidth: undefined,
        getRowId: undefined,
        emptyTitle: "Belum ada data",
        emptyDescription: "Data akan muncul di sini.",
    },
);

const emit = defineEmits<{
    (e: "row-click", row: TData): void;
    (e: "page-change", page: number): void;
}>();

defineSlots<{
    toolbar?: () => any;
    empty?: () => any;
    "mobile-row"?: (props: { row: TData; index: number; number: number }) => any;
}>();

const sorting = ref<SortingState>([]);

const table = useVueTable<TData>({
    get data() {
        return props.data;
    },
    get columns() {
        return props.columns;
    },
    getRowId: props.getRowId,
    defaultColumn: { enableSorting: false },
    state: {
        get sorting() {
            return sorting.value;
        },
    },
    onSortingChange: (updater) => {
        sorting.value =
            typeof updater === "function" ? updater(sorting.value) : updater;
    },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    manualPagination: true,
});

const rows = computed(() => table.getRowModel().rows);

/** Nomor urut global (memperhitungkan halaman) */
function rowNumber(index: number) {
    const p = props.pagination;
    if (!p) return index + 1;
    return (p.current_page - 1) * p.per_page + index + 1;
}

const rangeFrom = computed(
    () => props.pagination?.from ?? rowNumber(0),
);
const rangeTo = computed(
    () => props.pagination?.to ?? rowNumber(props.data.length - 1),
);

function alignClass(align?: "left" | "center" | "right") {
    if (align === "right") return "text-right";
    if (align === "center") return "text-center";
    return "text-left";
}

function colStyle(meta?: ColumnMeta<TData, unknown>) {
    if (!meta) return undefined;
    return {
        width: meta.width,
        minWidth: meta.minWidth ?? meta.width,
    };
}

function sortIcon(state: false | "asc" | "desc") {
    if (state === "asc") return ArrowSortUp16Regular;
    if (state === "desc") return ArrowSortDown16Regular;
    return ArrowSort16Regular;
}

defineExpose({ table, rowNumber });
</script>
