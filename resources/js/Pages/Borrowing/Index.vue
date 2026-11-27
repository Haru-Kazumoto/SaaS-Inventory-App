<template>
    <div class="flex flex-col gap-5">
        <TitlePage
            eyebrow="Peminjaman"
            title="Barang Keluar Masuk"
            subTitle="Kelola peminjaman barang dan proses pengembaliannya."
        >
            <template #action>
                <CreateButton
                    v-if="canCreate"
                    @createRoute="createRoute"
                    buttonText="Buat Peminjaman"
                    class="w-full sm:w-auto"
                />
            </template>
        </TitlePage>

        <section class="rounded border border-slate-200 bg-white">
            <!-- TOOLBAR -->
            <div
                class="flex flex-col gap-3 border-b border-slate-100 p-4 lg:flex-row lg:items-center lg:justify-between"
            >
                <!-- Status segmented -->
                <div
                    class="-mx-4 flex gap-1.5 overflow-x-auto px-4 pb-0.5 lg:mx-0 lg:px-0 no-scrollbar"
                    role="tablist"
                    aria-label="Filter status"
                >
                    <button
                        v-for="opt in BORROW_STATUS_FILTERS"
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

                <!-- Search -->
                <n-input
                    v-model:value="filters.borrower_name"
                    clearable
                    placeholder="Cari nama peminjam..."
                    class="w-full lg:!w-72"
                    @keyup.enter="onFilterCommit"
                    @clear="resetSearch"
                >
                    <template #prefix>
                        <n-icon
                            :component="Search20Regular"
                            class="text-slate-400"
                        />
                    </template>
                </n-input>
            </div>

            <n-spin :show="loading">
                <!-- DESKTOP: TABLE -->
                <div class="hidden lg:block">
                    <n-data-table
                        :bordered="false"
                        :columns="columns"
                        :data="transactions.data"
                        :row-props="rowProps"
                        :row-key="(row: Transaction) => row.id"
                        size="small"
                        :scroll-x="1040"
                        class="borrow-table"
                    >
                        <template #empty>
                            <Empty
                                title="Tidak ada peminjaman"
                                :description="emptyDescription"
                            />
                        </template>
                    </n-data-table>
                </div>

                <!-- MOBILE / TABLET: CARD LIST -->
                <div class="lg:hidden">
                    <ul
                        v-if="transactions.data.length"
                        class="divide-y divide-slate-100"
                    >
                        <li
                            v-for="row in transactions.data"
                            :key="row.id"
                            class="flex flex-col gap-3 p-4 active:bg-slate-50"
                            @click="goDetail(row)"
                        >
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded border border-slate-200 bg-slate-50 text-xs font-semibold text-slate-600"
                                >
                                    {{ getInitials(row.borrower_name) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div
                                        class="flex items-start justify-between gap-2"
                                    >
                                        <p
                                            class="truncate text-sm font-semibold text-slate-900"
                                        >
                                            {{ row.borrower_name }}
                                        </p>
                                        <BorrowStatusBadge
                                            :status="row.computed_status"
                                        />
                                    </div>
                                    <p
                                        class="mt-0.5 truncate text-xs text-slate-500"
                                    >
                                        {{ row.usage_location || "-" }}
                                        <span class="text-slate-300">·</span>
                                        {{ row.responsible_name || "-" }}
                                    </p>
                                </div>
                            </div>

                            <p
                                v-if="row.description"
                                class="line-clamp-2 text-xs leading-relaxed text-slate-600"
                            >
                                {{ row.description }}
                            </p>

                            <div
                                class="grid grid-cols-2 gap-2 rounded border border-slate-100 bg-slate-50/60 p-2.5"
                            >
                                <div>
                                    <p class="eyebrow">Jumlah</p>
                                    <p
                                        class="mt-0.5 text-sm font-semibold tabular-nums text-slate-900"
                                    >
                                        {{ row.products_count }}
                                        <span
                                            class="text-[11px] font-medium text-slate-400"
                                            >barang</span
                                        >
                                    </p>
                                </div>
                                <div>
                                    <p class="eyebrow">Waktu pinjam</p>
                                    <p
                                        class="mt-0.5 text-sm font-semibold tabular-nums text-slate-900"
                                    >
                                        {{
                                            formatDateParts(row.created_at).date
                                        }}
                                        <span
                                            class="text-[11px] font-medium text-slate-400"
                                        >
                                            {{
                                                formatDateParts(row.created_at)
                                                    .time
                                            }}
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2" @click.stop>
                                <n-button
                                    size="small"
                                    secondary
                                    class="flex-1"
                                    @click="goDetail(row)"
                                >
                                    Detail
                                </n-button>
                                <n-button
                                    v-if="!isCompleted(row)"
                                    size="small"
                                    type="primary"
                                    class="flex-1"
                                    @click="goReturn(row)"
                                >
                                    <template #icon>
                                        <n-icon
                                            :component="ArrowReply20Regular"
                                        />
                                    </template>
                                    Kembalikan
                                </n-button>
                            </div>
                        </li>
                    </ul>

                    <Empty
                        v-else
                        title="Tidak ada peminjaman"
                        :description="emptyDescription"
                        class="py-8"
                    />
                </div>
            </n-spin>

            <!-- FOOTER -->
            <div
                class="flex flex-col items-center justify-between gap-3 border-t border-slate-100 px-4 py-3 sm:flex-row"
            >
                <span class="text-xs text-slate-500 tabular-nums">
                    <template v-if="transactions.total">
                        Menampilkan
                        <strong class="text-slate-700"
                            >{{ transactions.from }}–{{
                                transactions.to
                            }}</strong
                        >
                        dari
                        <strong class="text-slate-700">{{
                            transactions.total
                        }}</strong>
                        peminjaman
                    </template>
                    <template v-else>Tidak ada data</template>
                </span>

                <n-pagination
                    v-if="transactions.last_page > 1"
                    :page="transactions.current_page"
                    :page-count="transactions.last_page"
                    :page-slot="5"
                    @update:page="onPageChange"
                />
            </div>
        </section>
    </div>
</template>

<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TitlePage from "@/Components/TitlePage.vue";
import CreateButton from "@/Components/CreateButton.vue";
import Empty from "@/Components/Empty.vue";
import BorrowStatusBadge from "@/Components/Feature/Borrowing/BorrowStatusBadge.vue";
import {
    BORROW_STATUS_FILTERS,
    formatDateParts,
    getInitials,
} from "@/Components/Feature/Borrowing/borrow-status";
import { router } from "@inertiajs/vue3";
import {
    NButton,
    NInput,
    NDataTable,
    NPagination,
    DataTableColumns,
    NIcon,
    NSpin,
} from "naive-ui";
import { PropType, computed, ref, h, reactive, watch } from "vue";
import { PaginationResponse } from "@/types/global-object";
import {
    ArrowReply20Regular,
    Search20Regular,
    Open48Filled,
} from "@vicons/fluent";

defineOptions({
    layout: AuthenticatedLayout,
});

interface Transaction {
    id: number;
    borrower_name: string;
    responsible_name: string | null;
    borrower_phone: string;
    usage_location: string | null;
    identity_number?: string | null;
    tx_number: string;
    unit_name?: string | null;
    due_at?: string | null;
    is_overdue?: boolean;
    can_operate?: boolean;
    description: string;
    created_at: string;
    creator_name: string;
    products_count: number;
    computed_status: string;
}

const props = defineProps({
    transactions: {
        type: Object as PropType<PaginationResponse<Transaction>>,
        required: true,
    },
    canCreate: { type: Boolean, default: false },
});

const urlParams = new URLSearchParams(window.location.search);
const filters = reactive<{
    borrower_name: string | null;
    status: string | null;
}>({
    borrower_name: urlParams.get("borrower_name") ?? null,
    status: urlParams.get("status") ?? null,
});
const loading = ref(false);
const page = ref(props.transactions.current_page || 1);

const emptyDescription = computed(() =>
    filters.borrower_name || filters.status
        ? "Tidak ada peminjaman yang cocok dengan filter saat ini."
        : "Peminjaman yang dibuat akan muncul di sini.",
);

/* ---------------- Navigasi ---------------- */
function createRoute() {
    router.visit(route("borrowing.create"));
}

function goDetail(row: Transaction) {
    loading.value = true;
    router.visit(route("borrowing.show", row.id), {
        onFinish: () => (loading.value = false),
    });
}

function goReturn(row: Transaction) {
    loading.value = true;
    router.visit(route("borrowing.return", row.id), {
        onFinish: () => (loading.value = false),
    });
}

function isCompleted(row: Transaction) {
    return row.computed_status === "COMPLETED";
}

function rowNumber(index: number) {
    return (
        (props.transactions.current_page - 1) * props.transactions.per_page +
        index +
        1
    );
}

/* ---------------- Filter ---------------- */
let searchTimer: ReturnType<typeof setTimeout> | undefined;
watch(
    () => filters.borrower_name,
    () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(onFilterCommit, 450);
    },
);

function setStatus(value: string | null) {
    if (filters.status === value) return;
    filters.status = value;
    onFilterCommit();
}

function resetSearch() {
    filters.borrower_name = null;
}

function onFilterCommit() {
    clearTimeout(searchTimer);
    page.value = 1;
    pushQuery();
}

function onPageChange(p: number) {
    page.value = p;
    pushQuery();
}

function pushQuery() {
    loading.value = true;

    const query: Record<string, string | number> = {};
    if (filters.borrower_name) query.borrower_name = filters.borrower_name;
    if (filters.status) query.status = filters.status;
    if (page.value > 1) query.page = page.value;

    router.get(route("borrowing.index"), query, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
        onFinish: () => (loading.value = false),
    });
}

/* ---------------- Tabel ---------------- */
function rowProps(row: Transaction) {
    return {
        class: "cursor-pointer",
        onClick: () => goDetail(row),
    };
}

const muted = (text: string) =>
    h("span", { class: "text-[11px] text-slate-400" }, text);

function createColumns(): DataTableColumns<Transaction> {
    return [
        {
            title: "Peminjam",
            key: "borrower_name",
            minWidth: 220,
            render(row) {
                return h("div", { class: "flex items-center gap-3 py-1" }, [
                    h(
                        "div",
                        {
                            class: "flex h-9 w-9 shrink-0 items-center justify-center rounded border border-slate-200 bg-slate-50 text-[11px] font-semibold text-slate-600",
                        },
                        getInitials(row.borrower_name),
                    ),
                    h("div", { class: "flex min-w-0 flex-col" }, [
                        h(
                            "span",
                            {
                                class: "truncate text-[13px] font-semibold text-slate-900",
                            },
                            row.borrower_name,
                        ),
                        muted(row.borrower_phone || "-"),
                    ]),
                ]);
            },
        },
        {
            title: "Ruangan",
            key: "usage_location",
            minWidth: 170,
            render(row) {
                return h("div", { class: "flex flex-col" }, [
                    h(
                        "span",
                        { class: "text-xs font-medium text-slate-700" },
                        row.usage_location || "-",
                    ),
                    muted(`Penanggung jawab: ${row.responsible_name || "-"}`),
                ]);
            },
        },
        {
            title: "Keperluan",
            key: "description",
            minWidth: 200,
            render(row) {
                return h(
                    "span",
                    {
                        class: "line-clamp-2 text-xs leading-relaxed text-slate-600",
                        title: row.description || undefined,
                    },
                    row.description || "-",
                );
            },
        },
        {
            title: "Jumlah",
            key: "products_count",
            width: 96,
            align: "right",
            render(row) {
                return h("div", { class: "flex flex-col items-end" }, [
                    h(
                        "span",
                        {
                            class: "text-sm font-semibold tabular-nums text-slate-900",
                        },
                        row.products_count,
                    ),
                    muted("barang"),
                ]);
            },
        },
        {
            title: "Status",
            key: "computed_status",
            width: 150,
            render: (row) =>
                h(BorrowStatusBadge, { status: row.computed_status }),
        },
        {
            title: "Waktu pinjam",
            key: "created_at",
            width: 130,
            render(row) {
                const parts = formatDateParts(row.created_at);
                return h("div", { class: "flex flex-col tabular-nums" }, [
                    h("span", { class: "text-xs text-slate-700" }, parts.date),
                    muted(parts.time),
                ]);
            },
        },
        {
            title: "Aksi",
            key: "action",
            width: 200,
            align: "right",
            fixed: "right",
            render(row) {
                return h(
                    "div",
                    {
                        class: "flex items-center justify-end gap-2",
                        onClick: (e: MouseEvent) => e.stopPropagation(),
                    },
                    [
                        !isCompleted(row)
                            ? h(
                                  NButton,
                                  {
                                      size: "small",
                                      type: "primary",
                                      secondary: true,
                                      onClick: () => goReturn(row),
                                  },
                                  {
                                      default: () => "Kembalikan",
                                  },
                              )
                            : null,

                        h(
                            NButton,
                            {
                                size: "small",
                                quaternary: true,
                                round: true,
                                onClick: () => goDetail(row),
                            },
                            {
                                default: () => "Detail",
                                icon: () =>
                                    h(
                                        NIcon,
                                        { size: 16 },
                                        {
                                            default: () => h(Open48Filled),
                                        },
                                    ),
                            },
                        ),
                    ],
                );
            },
        },
    ];
}

const columns = computed(() => createColumns());
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

.borrow-table :deep(.n-data-table-th) {
    background-color: rgb(248 250 252);
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: rgb(100 116 139);
}

.borrow-table :deep(.n-data-table-td) {
    vertical-align: middle;
}

.borrow-table :deep(.n-data-table-tr:hover .n-data-table-td) {
    background-color: rgba(42, 18, 108, 0.03);
}
</style>
