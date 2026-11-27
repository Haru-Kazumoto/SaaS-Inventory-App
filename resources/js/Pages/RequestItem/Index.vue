<template>
    <div class="flex flex-col gap-5">
        <TitlePage
            eyebrow="Pengajuan Barang"
            :title="`Pengajuan Barang ${filters.period}`"
            :subTitle="
                isSuper
                    ? 'Tinjau dan proses pengajuan barang dari setiap jurusan.'
                    : 'Ajukan kebutuhan barang dan pantau status persetujuannya.'
            "
        >
            <template #action>
                <div class="flex w-full gap-2 sm:w-auto">
                    <ExportExcelButton
                        class="flex-1 sm:flex-none"
                        :caption="`Rekap ${filters.period} · .xlsx`"
                        :title="`Unduh rekap pengajuan ${filters.period} dalam format Excel`"
                        @click="showExport = true"
                    />
                    <n-button
                        v-if="!isSuper"
                        type="primary"
                        class="flex-1 sm:flex-none !h-auto self-stretch"
                        @click="goCreate"
                    >
                        <template #icon>
                            <n-icon :component="Add20Regular" />
                        </template>
                        Ajukan Barang
                    </n-button>
                </div>
            </template>
        </TitlePage>

        <!-- RINGKASAN -->
        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
            <StockStatCard
                :icon="DocumentBulletList20Regular"
                label="Total diajukan"
                :count="count.request"
                unit="pengajuan"
                color="blue"
            />
            <StockStatCard
                :icon="Clock20Regular"
                label="Menunggu"
                :count="count.pending"
                unit="pengajuan"
                color="amber"
            />
            <StockStatCard
                :icon="CheckmarkCircle20Regular"
                label="Disetujui"
                :count="count.approved"
                unit="pengajuan"
                color="green"
            />
            <StockStatCard
                :icon="DismissCircle20Regular"
                label="Ditolak"
                :count="count.rejected"
                unit="pengajuan"
                color="red"
            />
        </div>

        <!-- BULK ACTION (superadmin) -->
        <Transition
            enter-active-class="transition duration-150"
            leave-active-class="transition duration-150"
            enter-from-class="opacity-0 -translate-y-1"
            leave-to-class="opacity-0 -translate-y-1"
        >
            <div
                v-if="isSuper && selected.size"
                class="sticky top-16 z-20 flex flex-col gap-3 rounded border border-[#2A126C]/20 bg-[#2A126C] px-4 py-3 text-white sm:flex-row sm:items-center sm:justify-between"
            >
                <p class="text-sm">
                    <b class="tabular-nums">{{ selected.size }}</b> pengajuan dipilih
                    <button
                        type="button"
                        class="ml-2 text-xs text-white/70 underline hover:text-white"
                        @click="selected.clear()"
                    >
                        Batalkan pilihan
                    </button>
                </p>
                <div class="flex gap-2">
                    <button
                        type="button"
                        class="flex-1 rounded border border-rose-300/60 bg-rose-500/20 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-500/30 sm:flex-none"
                        @click="processSelected('REJECTED')"
                    >
                        Tolak
                    </button>
                    <button
                        type="button"
                        class="flex-1 rounded border border-[#F5D90A] bg-[#F5D90A] px-3 py-1.5 text-xs font-semibold text-[#2A126C] hover:bg-[#e8cc09] sm:flex-none"
                        @click="processSelected('APPROVED')"
                    >
                        Setujui
                    </button>
                </div>
            </div>
        </Transition>

        <DataTable
            :data="request_items.data"
            :columns="columns"
            :pagination="request_items"
            :loading="loading"
            :get-row-id="(row) => String(row.id)"
            item-label="pengajuan"
            min-width="1000px"
            empty-title="Belum ada pengajuan"
            :empty-description="
                hasActiveFilter
                    ? 'Tidak ada pengajuan yang cocok dengan filter saat ini.'
                    : `Belum ada pengajuan barang di tahun ${filters.period}.`
            "
            @page-change="apply"
        >
            <template #toolbar>
                <div
                    class="no-scrollbar -mx-4 flex gap-1.5 overflow-x-auto px-4 pb-0.5 lg:mx-0 lg:px-0"
                    role="tablist"
                    aria-label="Filter status"
                >
                    <button
                        v-for="opt in STATUS_FILTERS"
                        :key="opt.label"
                        type="button"
                        role="tab"
                        :aria-selected="query.status === opt.value"
                        class="shrink-0 rounded border px-3 py-1.5 text-xs font-semibold transition-colors duration-150"
                        :class="
                            query.status === opt.value
                                ? 'border-[#2A126C] bg-[#2A126C] text-white'
                                : 'border-slate-200 bg-white text-slate-600 hover:border-[#2A126C]/40 hover:text-[#2A126C]'
                        "
                        @click="setStatus(opt.value)"
                    >
                        {{ opt.label }}
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-2 sm:grid-cols-3 lg:flex lg:items-center">
                    <n-input
                        v-model:value="query.item_name"
                        clearable
                        placeholder="Cari nama barang..."
                        class="lg:!w-56"
                    >
                        <template #prefix>
                            <n-icon :component="Search20Regular" class="text-slate-400" />
                        </template>
                    </n-input>
                    <n-date-picker
                        v-model:value="periodTs"
                        type="year"
                        placeholder="Tahun"
                        class="lg:!w-32"
                    />
                    <n-select
                        v-if="isSuper"
                        v-model:value="query.major_request"
                        :options="majorOptions"
                        placeholder="Semua jurusan"
                        clearable
                        filterable
                        class="lg:!w-44"
                        @update:value="apply()"
                    />
                </div>
            </template>

            <template #mobile-row="{ row }">
                <div class="flex gap-3">
                    <n-checkbox
                        v-if="isSuper"
                        class="mt-0.5"
                        :checked="selected.has(row.id)"
                        :disabled="row.request_status !== 'PENDING'"
                        @update:checked="(v) => toggle(row.id, v)"
                    />
                    <div class="flex min-w-0 flex-1 flex-col gap-2">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900">
                                    {{ row.brand_name }}
                                </p>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    <b class="tabular-nums text-slate-700">{{ row.quantity }}</b>
                                    {{ row.unit }}
                                    <template v-if="isSuper">
                                        <span class="text-slate-300"> · </span>{{ row.name }}
                                    </template>
                                </p>
                            </div>
                            <RequestStatusBadge :status="row.request_status" />
                        </div>
                        <p
                            v-if="row.reason_request"
                            class="line-clamp-2 text-xs leading-relaxed text-slate-600"
                        >
                            {{ row.reason_request }}
                        </p>
                        <p class="text-[11px] tabular-nums text-slate-400">
                            Diajukan {{ formatDateParts(row.created_at).date }}
                            <template v-if="row.proceed_at">
                                · Diproses {{ formatDateParts(row.proceed_at).date }}
                            </template>
                        </p>
                    </div>
                </div>
            </template>
        </DataTable>

        <!-- EXPORT -->
        <n-modal
            v-model:show="showExport"
            preset="card"
            :bordered="false"
            :title="`Export Pengajuan ${filters.period}`"
            class="!w-[calc(100vw-2rem)] max-w-md !rounded"
            :segmented="{ content: true, footer: true }"
            @after-leave="exportStatuses = []"
        >
            <p class="text-sm text-slate-600">
                Pengajuan tahun <b class="text-slate-900">{{ filters.period }}</b> akan
                diunduh sebagai file <b class="text-slate-900">.xlsx</b>. Pilih status
                yang ingin diexport:
            </p>

            <div class="mt-4 flex flex-col gap-2">
                <label
                    v-for="opt in EXPORT_OPTIONS"
                    :key="opt.value"
                    class="flex cursor-pointer items-center gap-3 rounded border px-3 py-2.5 transition-colors duration-150"
                    :class="
                        exportStatuses.includes(opt.value)
                            ? 'border-[#2A126C] bg-[#2A126C]/[0.04]'
                            : 'border-slate-200 hover:border-slate-300'
                    "
                >
                    <n-checkbox
                        :checked="exportStatuses.includes(opt.value)"
                        @update:checked="(v) => toggleExport(opt.value, v)"
                    />
                    <span class="text-sm font-medium text-slate-800">{{ opt.label }}</span>
                </label>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <n-button @click="showExport = false">Batal</n-button>
                    <n-button
                        type="primary"
                        :disabled="!exportStatuses.length"
                        @click="handleExport"
                    >
                        <template #icon>
                            <n-icon :component="ArrowDownload20Regular" />
                        </template>
                        Download
                    </n-button>
                </div>
            </template>
        </n-modal>
    </div>
</template>

<script setup lang="ts">
import { computed, h, reactive, ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { createColumnHelper, type ColumnDef } from "@tanstack/vue-table";
import {
    NButton,
    NCheckbox,
    NDatePicker,
    NIcon,
    NInput,
    NModal,
    NSelect,
} from "naive-ui";
import {
    Add20Regular,
    ArrowDownload20Regular,
    CheckmarkCircle20Regular,
    Clock20Regular,
    DismissCircle20Regular,
    DocumentBulletList20Regular,
    Search20Regular,
} from "@vicons/fluent";
import Swal from "sweetalert2";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TitlePage from "@/Components/TitlePage.vue";
import ExportExcelButton from "@/Components/ExportExcelButton.vue";
import DataTable from "@/Components/DataTable/DataTable.vue";
import type { DataTablePagination } from "@/Components/DataTable/types";
import StockStatCard from "@/Components/Feature/Products/StockStatCard.vue";
import RequestStatusBadge from "@/Components/Feature/RequestItem/RequestStatusBadge.vue";
import { formatDateParts } from "@/Components/Feature/Borrowing/borrow-status";
import { useTableFilters } from "@/Composables/useTableFilters";

defineOptions({ layout: AuthenticatedLayout });

interface RequestRow {
    id: number;
    brand_name: string;
    quantity: number;
    unit: string;
    request_status: "PENDING" | "APPROVED" | "REJECTED";
    reason_request?: string | null;
    proceed_at?: string | null;
    created_at: string;
    name?: string | null; // nama jurusan
}

const props = defineProps<{
    request_items: DataTablePagination & { data: RequestRow[] };
    count: { request: number; pending: number; approved: number; rejected: number };
    filters: {
        period: number;
        item_name?: string | null;
        status?: string | null;
        majors?: Array<{ name: string; code: string }>;
    };
}>();

const page = usePage();
const user = (page.props as any).auth.user;
const isSuper = user.role.code === "SUPER";

/* ---------------- Filter ---------------- */
const STATUS_FILTERS = [
    { label: "Semua", value: null },
    { label: "Menunggu", value: "PENDING" },
    { label: "Disetujui", value: "APPROVED" },
    { label: "Ditolak", value: "REJECTED" },
] as const;

const {
    filters: query,
    loading,
    apply,
} = useTableFilters(
    "request-item.index",
    {
        item_name: null as string | null,
        status: null as string | null,
        major_request: null as string | null,
        period: String(props.filters.period) as string | null,
    },
    { textKeys: ["item_name"] },
);

const hasActiveFilter = computed(
    () => !!(query.item_name || query.status || query.major_request),
);

const periodTs = computed<number | null>({
    get: () => (query.period ? new Date(Number(query.period), 0, 1).getTime() : null),
    set: (ts) => {
        query.period = ts ? String(new Date(ts).getFullYear()) : null;
        apply();
    },
});

function setStatus(value: string | null) {
    if (query.status === value) return;
    query.status = value;
    apply();
}

const majorOptions = computed(() =>
    (props.filters.majors ?? []).map((m) => ({ label: m.name, value: m.code })),
);

function goCreate() {
    router.visit(route("request-item.create"));
}

/* ---------------- Seleksi & proses massal (superadmin) ---------------- */
const selected = reactive(new Set<number>());

const pendingOnPage = computed(() =>
    props.request_items.data.filter((r) => r.request_status === "PENDING"),
);

function toggle(id: number, checked: boolean) {
    checked ? selected.add(id) : selected.delete(id);
}

function toggleAll(checked: boolean) {
    pendingOnPage.value.forEach((r) => toggle(r.id, checked));
}

function processSelected(type: "APPROVED" | "REJECTED") {
    const approve = type === "APPROVED";
    Swal.fire({
        title: `${approve ? "Setujui" : "Tolak"} ${selected.size} pengajuan?`,
        text: "Status tidak bisa diubah lagi dan akan masuk rekap tahunan.",
        icon: "question",
        showCancelButton: true,
        cancelButtonText: "Batal",
        confirmButtonText: approve ? "Ya, setujui" : "Ya, tolak",
        confirmButtonColor: approve ? "#2A126C" : "#e11d48",
        showLoaderOnConfirm: true,
        preConfirm: () =>
            new Promise<void>((resolve, reject) => {
                router.post(
                    route("request-item.bulk.assign"),
                    { ids: [...selected], assign_type: type },
                    {
                        preserveScroll: true,
                        onSuccess: () => resolve(),
                        onError: () => reject(),
                    },
                );
            }).catch(() => Swal.showValidationMessage("Gagal memproses data di server.")),
    }).then((result) => {
        if (!result.isConfirmed) return;
        selected.clear();
        Swal.fire({
            title: "Berhasil diproses",
            text: "Pengajuan telah diperbarui dan masuk rekap tahunan.",
            icon: "success",
            timer: 2000,
            showConfirmButton: false,
        });
    });
}

/* ---------------- Export ---------------- */
const EXPORT_OPTIONS = [
    { label: "Disetujui", value: "approved" },
    { label: "Ditolak", value: "rejected" },
    { label: "Semua status", value: "all" },
];
const showExport = ref(false);
const exportStatuses = ref<string[]>([]);

function toggleExport(value: string, checked: boolean) {
    if (!checked) {
        exportStatuses.value = exportStatuses.value.filter((v) => v !== value);
    } else if (value === "all") {
        exportStatuses.value = ["all"];
    } else {
        exportStatuses.value = [...exportStatuses.value.filter((v) => v !== "all"), value];
    }
}

function handleExport() {
    const qs = new URLSearchParams();
    exportStatuses.value.forEach((t) => qs.append("export_type[]", t));
    if (props.filters.period) qs.append("year", String(props.filters.period));
    if (user.major) qs.append("major", String(user.major.id));

    window.open(`${route("request-item.export")}?${qs.toString()}`, "_blank");
    showExport.value = false;
}

/* ---------------- Kolom ---------------- */
const muted = (text?: string | null) =>
    h("span", { class: "text-[11px] text-slate-400" }, text || "-");

const dateCell = (value?: string | null) => {
    if (!value) return h("span", { class: "text-xs text-slate-300" }, "Belum diproses");
    const p = formatDateParts(value);
    return h("div", { class: "flex flex-col tabular-nums" }, [
        h("span", { class: "text-xs text-slate-700" }, p.date),
        muted(p.time),
    ]);
};

const col = createColumnHelper<RequestRow>();

const columns = computed(() => {
    const list: ColumnDef<RequestRow, any>[] = [];

    if (isSuper) {
        list.push(
            col.display({
                id: "select",
                meta: { width: "44px", align: "center" },
                header: () =>
                    h(NCheckbox, {
                        checked:
                            pendingOnPage.value.length > 0 &&
                            pendingOnPage.value.every((r) => selected.has(r.id)),
                        indeterminate:
                            pendingOnPage.value.some((r) => selected.has(r.id)) &&
                            !pendingOnPage.value.every((r) => selected.has(r.id)),
                        disabled: !pendingOnPage.value.length,
                        "aria-label": "Pilih semua yang menunggu",
                        onUpdateChecked: toggleAll,
                    }),
                cell: ({ row }) =>
                    h(NCheckbox, {
                        checked: selected.has(row.original.id),
                        disabled: row.original.request_status !== "PENDING",
                        "aria-label": `Pilih ${row.original.brand_name}`,
                        onUpdateChecked: (v: boolean) => toggle(row.original.id, v),
                    }),
            }),
        );
    }

    list.push(
        col.display({
            id: "index",
            header: "#",
            meta: { width: "48px", align: "center" },
            cell: ({ row }) =>
                h(
                    "span",
                    { class: "text-xs tabular-nums text-slate-400" },
                    (props.request_items.current_page - 1) * props.request_items.per_page +
                        row.index +
                        1,
                ),
        }),
        col.accessor("brand_name", {
            header: "Barang",
            meta: { minWidth: "200px" },
            enableSorting: true,
            cell: ({ row }) =>
                h("div", { class: "flex min-w-0 flex-col" }, [
                    h(
                        "span",
                        { class: "truncate text-[13px] font-semibold text-slate-900" },
                        row.original.brand_name,
                    ),
                    muted(`Diajukan ${formatDateParts(row.original.created_at).date}`),
                ]),
        }),
        col.accessor((r) => Number(r.quantity) || 0, {
            id: "quantity",
            header: "Jumlah",
            meta: { width: "110px", align: "right" },
            enableSorting: true,
            cell: ({ row }) =>
                h("span", { class: "whitespace-nowrap" }, [
                    h(
                        "span",
                        { class: "text-sm font-bold tabular-nums text-slate-900" },
                        row.original.quantity,
                    ),
                    h("span", { class: "ml-1 text-[11px] text-slate-400" }, row.original.unit),
                ]),
        }),
        col.accessor("reason_request", {
            header: "Alasan",
            meta: { minWidth: "240px" },
            cell: ({ getValue }) =>
                h(
                    "span",
                    {
                        class: "line-clamp-2 text-xs leading-relaxed text-slate-600",
                        title: getValue() || undefined,
                    },
                    getValue() || "-",
                ),
        }),
    );

    if (isSuper) {
        list.push(
            col.accessor("name", {
                header: "Jurusan",
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
        col.accessor("request_status", {
            header: "Status",
            meta: { width: "130px" },
            cell: ({ getValue }) => h(RequestStatusBadge, { status: getValue() }),
        }),
        col.accessor("proceed_at", {
            header: "Diproses",
            meta: { width: "130px" },
            enableSorting: true,
            cell: ({ getValue }) => dateCell(getValue()),
        }),
    );

    return list;
});
</script>

<style scoped>
.no-scrollbar {
    scrollbar-width: none;
}
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
</style>
