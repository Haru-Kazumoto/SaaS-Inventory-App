<template>
    <div class="flex flex-col gap-5 pb-28 lg:pb-0">
        <TitlePage
            eyebrow="Peminjaman"
            title="Pengembalian Barang"
            subTitle="Isi jumlah barang yang dikembalikan beserta kondisinya."
        >
            <template #action>
                <n-button class="w-full sm:w-auto" @click="goBack">
                    <template #icon>
                        <n-icon :component="ArrowLeft20Regular" />
                    </template>
                    Kembali
                </n-button>
            </template>
        </TitlePage>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <!-- ================= KIRI: DAFTAR BARANG ================= -->
            <div class="flex flex-col gap-5 lg:col-span-2">
                <!-- Peminjam (ringkas) -->
                <section class="rounded border border-slate-200 bg-white p-4 sm:p-5">
                    <div class="flex items-start gap-4">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded bg-[#2A126C] text-sm font-bold text-white"
                        >
                            {{ getInitials(tx.borrower_name) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="eyebrow">Peminjam</p>
                            <p
                                class="truncate text-base font-bold tracking-tight text-slate-900"
                            >
                                {{ tx.borrower_name || "-" }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                {{ tx.usage_location || "-" }}
                                <span class="text-slate-300">·</span>
                                Penanggung jawab: {{ tx.responsible_name || "-" }}
                            </p>
                        </div>
                        <span
                            v-if="tx.tx_number"
                            class="hidden shrink-0 rounded border border-slate-200 bg-slate-50 px-2 py-0.5 font-mono text-[11px] text-slate-500 sm:inline"
                        >
                            {{ tx.tx_number }}
                        </span>
                    </div>

                    <dl
                        class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 sm:grid-cols-3"
                    >
                        <div>
                            <dt class="eyebrow">Tanggal pinjam</dt>
                            <dd class="mt-0.5 text-sm font-medium tabular-nums text-slate-800">
                                {{ borrowedAt.date }}
                                <span class="text-[11px] text-slate-400">{{
                                    borrowedAt.time
                                }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="eyebrow">Telepon</dt>
                            <dd class="mt-0.5 text-sm font-medium tabular-nums text-slate-800">
                                {{ formatPhone(tx.borrower_phone) }}
                            </dd>
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <dt class="eyebrow">Keperluan</dt>
                            <dd
                                class="mt-0.5 line-clamp-2 text-sm text-slate-700"
                                :title="tx.description || undefined"
                            >
                                {{ tx.description || "-" }}
                            </dd>
                        </div>
                    </dl>
                </section>

                <!-- Barang yang harus dikembalikan -->
                <section class="rounded border border-slate-200 bg-white">
                    <header
                        class="flex flex-col gap-3 border-b border-slate-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5"
                    >
                        <div class="flex items-center gap-2.5">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded bg-[#2A126C]/[0.06] text-[#2A126C]"
                            >
                                <n-icon :component="BoxMultiple20Regular" size="18" />
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900">
                                    Barang yang Dipinjam
                                </h2>
                                <p class="text-[11px] text-slate-500">
                                    {{ pendingRows.length }} barang belum
                                    kembali sepenuhnya
                                </p>
                            </div>
                        </div>

                        <div v-if="pendingRows.length" class="flex gap-2">
                            <n-button
                                size="small"
                                class="flex-1 sm:flex-none"
                                :disabled="totalReturnQty === 0"
                                @click="resetAll"
                            >
                                Reset
                            </n-button>
                            <n-button
                                size="small"
                                type="primary"
                                ghost
                                class="flex-1 sm:flex-none"
                                @click="fillAll"
                            >
                                <template #icon>
                                    <n-icon :component="CheckmarkCircle20Regular" />
                                </template>
                                Kembalikan semua
                            </n-button>
                        </div>
                    </header>

                    <ul v-if="durableRows.length" class="divide-y divide-slate-100">
                        <li
                            v-for="row in durableRows"
                            :key="row.id"
                            class="relative px-4 py-4 transition-colors duration-150 sm:px-5"
                            :class="[
                                row.outstanding === 0 ? 'bg-slate-50/60' : '',
                                (row.return_qty ?? 0) > 0
                                    ? 'bg-[#2A126C]/[0.025]'
                                    : '',
                            ]"
                        >
                            <span
                                v-if="(row.return_qty ?? 0) > 0"
                                class="absolute inset-y-0 left-0 w-[3px] bg-[#2A126C]"
                                aria-hidden="true"
                            ></span>

                            <div class="flex flex-col gap-4 md:flex-row md:items-start">
                                <!-- Info barang -->
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p
                                                class="truncate text-sm font-semibold text-slate-900"
                                            >
                                                {{ row.product_name }}
                                            </p>
                                            <p class="truncate text-[11px] text-slate-400">
                                                <span class="font-mono">{{
                                                    row.product_code
                                                }}</span>
                                                <span class="text-slate-300"> · </span>
                                                {{ row.warehouse_name || "-" }}
                                            </p>
                                        </div>
                                        <span
                                            v-if="row.outstanding === 0"
                                            class="inline-flex shrink-0 items-center gap-1 rounded border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700"
                                        >
                                            <n-icon :component="Checkmark12Filled" />
                                            Sudah kembali
                                        </span>
                                    </div>

                                    <!-- Progres -->
                                    <div class="mt-3">
                                        <div
                                            class="flex h-1.5 overflow-hidden rounded-full bg-slate-100"
                                        >
                                            <div
                                                class="bg-emerald-500 transition-all duration-200"
                                                :style="{
                                                    width: pct(row.returned_qty, row.quantity),
                                                }"
                                            ></div>
                                            <div
                                                class="bg-[#2A126C]/60 transition-all duration-200"
                                                :style="{
                                                    width: pct(row.return_qty ?? 0, row.quantity),
                                                }"
                                            ></div>
                                        </div>
                                        <div
                                            class="mt-1.5 flex flex-wrap gap-x-4 gap-y-1 text-[11px] tabular-nums text-slate-500"
                                        >
                                            <span>
                                                Dipinjam
                                                <b class="text-slate-700">{{ row.quantity }}</b>
                                            </span>
                                            <span>
                                                Sudah kembali
                                                <b class="text-emerald-700">{{ row.returned_qty }}</b>
                                            </span>
                                            <span>
                                                Sisa
                                                <b class="text-amber-700">{{ row.outstanding }}</b>
                                                {{ row.uom_name ?? "" }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Input -->
                                <div
                                    v-if="row.outstanding > 0"
                                    class="grid grid-cols-2 gap-3 md:w-[300px] md:shrink-0"
                                >
                                    <div class="flex flex-col gap-1">
                                        <label
                                            :for="`qty-${row.id}`"
                                            class="eyebrow"
                                            >Jumlah kembali</label
                                        >
                                        <n-input-number
                                            :input-props="{ id: `qty-${row.id}` }"
                                            :value="row.return_qty"
                                            :min="0"
                                            :max="row.outstanding"
                                            placeholder="0"
                                            button-placement="both"
                                            @update:value="(v) => (row.return_qty = v)"
                                        />
                                        <button
                                            type="button"
                                            class="self-start text-[11px] font-semibold text-[#2A126C] hover:underline"
                                            @click="row.return_qty = row.outstanding"
                                        >
                                            Semua ({{ row.outstanding }})
                                        </button>
                                    </div>

                                    <div class="flex flex-col gap-1">
                                        <span class="eyebrow">Kondisi</span>
                                        <div
                                            class="grid grid-cols-2 overflow-hidden rounded border border-slate-300"
                                            role="radiogroup"
                                            :aria-label="`Kondisi ${row.product_name}`"
                                        >
                                            <button
                                                v-for="opt in conditionChoices"
                                                :key="opt.value"
                                                type="button"
                                                role="radio"
                                                :aria-checked="row.to_condition === opt.value"
                                                class="h-[34px] text-xs font-semibold transition-colors duration-150 first:border-r first:border-slate-300"
                                                :class="
                                                    row.to_condition === opt.value
                                                        ? opt.active
                                                        : 'bg-white text-slate-500 hover:bg-slate-50'
                                                "
                                                @click="row.to_condition = opt.value"
                                            >
                                                {{ opt.short }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>

                    <div
                        v-else
                        class="flex flex-col items-center justify-center px-4 py-12 text-center text-slate-400"
                    >
                        <n-icon :component="BoxMultiple20Regular" size="32" class="opacity-40" />
                        <p class="mt-2 text-sm">
                            Tidak ada barang yang perlu dikembalikan.
                        </p>
                    </div>
                </section>

                <!-- Habis pakai -->
                <section
                    v-if="consumableRows.length"
                    class="rounded border border-slate-200 bg-white"
                >
                    <button
                        type="button"
                        class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left sm:px-5"
                        :aria-expanded="showConsumables"
                        @click="showConsumables = !showConsumables"
                    >
                        <div>
                            <p class="text-sm font-semibold text-slate-800">
                                Barang habis pakai
                                <span class="font-normal tabular-nums text-slate-400"
                                    >({{ consumableRows.length }})</span
                                >
                            </p>
                            <p class="text-[11px] text-slate-500">
                                Tidak perlu dikembalikan.
                            </p>
                        </div>
                        <n-icon
                            :component="ChevronDown20Regular"
                            class="text-slate-400 transition-transform duration-150"
                            :class="showConsumables ? 'rotate-180' : ''"
                        />
                    </button>
                    <ul
                        v-show="showConsumables"
                        class="divide-y divide-slate-100 border-t border-slate-100"
                    >
                        <li
                            v-for="row in consumableRows"
                            :key="row.id"
                            class="flex items-center gap-3 px-4 py-3 sm:px-5"
                        >
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm text-slate-700">
                                    {{ row.product_name }}
                                </p>
                                <p class="truncate font-mono text-[11px] text-slate-400">
                                    {{ row.product_code }}
                                </p>
                            </div>
                            <span class="shrink-0 text-sm font-semibold tabular-nums text-slate-700">
                                {{ row.quantity }}
                                <span class="text-[11px] font-normal text-slate-400">{{
                                    row.uom_name ?? ""
                                }}</span>
                            </span>
                        </li>
                    </ul>
                </section>
            </div>

            <!-- ================= KANAN: RINGKASAN (desktop) ================= -->
            <aside class="hidden lg:block">
                <section
                    class="sticky top-20 rounded border border-slate-200 bg-white"
                >
                    <header class="border-b border-slate-100 px-5 py-4">
                        <p class="eyebrow">Ringkasan</p>
                        <h2 class="text-sm font-bold text-slate-900">
                            Pengembalian
                        </h2>
                    </header>

                    <dl class="divide-y divide-slate-100">
                        <div
                            v-for="s in summaryStats"
                            :key="s.label"
                            class="flex items-center justify-between px-5 py-3"
                        >
                            <dt class="text-xs text-slate-500">{{ s.label }}</dt>
                            <dd
                                class="text-sm font-semibold tabular-nums"
                                :class="s.class"
                            >
                                {{ s.value }}
                            </dd>
                        </div>
                    </dl>

                    <div class="border-t border-slate-100 px-5 py-4">
                        <div class="flex items-baseline justify-between">
                            <span class="text-xs font-semibold text-slate-600"
                                >Akan dikembalikan</span
                            >
                            <span class="text-2xl font-bold tabular-nums text-[#2A126C]">
                                {{ totalReturnQty }}
                                <span class="text-xs font-medium text-slate-400">unit</span>
                            </span>
                        </div>
                        <p
                            v-if="damagedQty > 0"
                            class="mt-1 text-right text-[11px] text-rose-600"
                        >
                            {{ damagedQty }} unit dalam kondisi rusak
                        </p>

                        <n-button
                            type="primary"
                            block
                            size="large"
                            class="mt-4"
                            :disabled="!canSubmit"
                            :loading="submitting"
                            @click="askConfirm"
                        >
                            Simpan Pengembalian
                        </n-button>
                        <p
                            v-if="!canSubmit"
                            class="mt-2 text-center text-[11px] text-slate-400"
                        >
                            Isi jumlah pada minimal satu barang.
                        </p>
                    </div>
                </section>
            </aside>
        </div>

        <!-- ================= ACTION BAR (mobile / tablet) ================= -->
        <div
            class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 px-4 py-3 backdrop-blur lg:hidden"
            style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom))"
        >
            <div class="mx-auto flex max-w-3xl items-center gap-3">
                <div class="min-w-0 flex-1">
                    <p class="eyebrow">Akan dikembalikan</p>
                    <p class="text-lg font-bold leading-tight tabular-nums text-[#2A126C]">
                        {{ totalReturnQty }}
                        <span class="text-xs font-medium text-slate-400">
                            / {{ totalOutstanding }} unit
                        </span>
                    </p>
                </div>
                <n-button
                    type="primary"
                    size="large"
                    :disabled="!canSubmit"
                    :loading="submitting"
                    @click="askConfirm"
                >
                    Simpan
                </n-button>
            </div>
        </div>

        <!-- ================= KONFIRMASI ================= -->
        <n-modal
            v-model:show="showConfirm"
            preset="card"
            :bordered="false"
            title="Konfirmasi Pengembalian"
            class="!w-[calc(100vw-2rem)] max-w-md !rounded"
            :segmented="{ content: true, footer: true }"
        >
            <p class="text-sm text-slate-600">
                <b class="text-slate-900">{{ tx.borrower_name }}</b> akan
                mengembalikan
                <b class="tabular-nums text-slate-900">{{ totalReturnQty }} unit</b>
                dari {{ selectedRows.length }} barang:
            </p>
            <ul
                class="mt-3 max-h-60 divide-y divide-slate-100 overflow-y-auto rounded border border-slate-200"
            >
                <li
                    v-for="r in selectedRows"
                    :key="r.id"
                    class="flex items-center justify-between gap-3 px-3 py-2"
                >
                    <span class="min-w-0 truncate text-xs text-slate-700">{{
                        r.product_name
                    }}</span>
                    <span class="flex shrink-0 items-center gap-2">
                        <span
                            class="rounded px-1.5 py-0.5 text-[10px] font-semibold"
                            :class="
                                r.to_condition === 'DAMAGED'
                                    ? 'bg-rose-50 text-rose-700'
                                    : 'bg-emerald-50 text-emerald-700'
                            "
                        >
                            {{ r.to_condition === "DAMAGED" ? "Rusak" : "Bagus" }}
                        </span>
                        <span class="text-xs font-semibold tabular-nums text-slate-900">
                            {{ r.return_qty }} {{ r.uom_name ?? "" }}
                        </span>
                    </span>
                </li>
            </ul>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <n-button @click="showConfirm = false">Batal</n-button>
                    <n-button type="primary" :loading="submitting" @click="submit">
                        Ya, simpan
                    </n-button>
                </div>
            </template>
        </n-modal>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, PropType } from "vue";
import { router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TitlePage from "@/Components/TitlePage.vue";
import {
    formatDateParts,
    formatPhone,
    getInitials,
} from "@/Components/Feature/Borrowing/borrow-status";
import {
    ArrowLeft20Regular,
    BoxMultiple20Regular,
    Checkmark12Filled,
    CheckmarkCircle20Regular,
    ChevronDown20Regular,
} from "@vicons/fluent";
import { NButton, NIcon, NInputNumber, NModal, useMessage } from "naive-ui";

defineOptions({ layout: AuthenticatedLayout });

interface TxHeader {
    id: number;
    tx_number?: string | null;
    borrower_name?: string | null;
    borrower_phone?: string | null;
    responsible_name?: string | null;
    usage_location?: string | null;
    occurred_at?: string | null;
    created_at?: string | null;
    description?: string | null;
}

type Condition = "GOOD" | "DAMAGED";

/** Baris pinjaman (id = id baris pinjam) beserta sisa yang bisa dikembalikan. */
interface LineEnriched {
    id: number;
    product_id: number;
    product_code: string;
    product_name: string;
    product_type: string;
    is_consumable: boolean;
    warehouse_id: number;
    warehouse_name: string;
    uom_name?: string | null;
    quantity: number;
    returned_qty: number;
    outstanding: number;
}

const props = defineProps({
    transaction: { type: Object as PropType<TxHeader>, required: true },
    lines: { type: Array as PropType<LineEnriched[]>, required: true },
    /** gudang tujuan pengembalian (milik unit + bersama) */
    warehouses: {
        type: Array as PropType<Array<{ label: string; value: number }>>,
        required: true,
    },
});

const message = useMessage();
const tx = computed(() => props.transaction);
const borrowedAt = computed(() =>
    formatDateParts(tx.value.occurred_at || tx.value.created_at),
);

/* ---------------- State baris ---------------- */
type RowState = LineEnriched & {
    return_qty: number | null;
    to_condition: Condition;
    to_warehouse_id: number | null;
};

const rows = ref<RowState[]>(
    props.lines.map((l) => ({
        ...l,
        return_qty: null,
        to_condition: "GOOD",
        to_warehouse_id: l.warehouse_id, // default kembali ke gudang asal
    })),
);

const isConsumable = (r: LineEnriched) =>
    Boolean(r.is_consumable) || r.product_type === "CONSUMABLE";

const durableRows = computed(() =>
    // yang masih outstanding di atas, yang sudah lunas di bawah
    rows.value
        .filter((r) => !isConsumable(r))
        .sort((a, b) => Number(b.outstanding > 0) - Number(a.outstanding > 0)),
);
const consumableRows = computed(() => rows.value.filter(isConsumable));
const pendingRows = computed(() =>
    durableRows.value.filter((r) => r.outstanding > 0),
);
const selectedRows = computed(() =>
    pendingRows.value.filter((r) => (r.return_qty ?? 0) > 0),
);

const showConsumables = ref(false);

const conditionChoices: Array<{
    value: Condition;
    short: string;
    active: string;
}> = [
    { value: "GOOD", short: "Bagus", active: "bg-emerald-600 text-white" },
    { value: "DAMAGED", short: "Rusak", active: "bg-rose-600 text-white" },
];

/* ---------------- Ringkasan ---------------- */
const sum = (list: RowState[], pick: (r: RowState) => number) =>
    list.reduce((s, r) => s + (Number(pick(r)) || 0), 0);

const totalBorrowed = computed(() => sum(durableRows.value, (r) => r.quantity));
const totalReturned = computed(() => sum(durableRows.value, (r) => r.returned_qty));
const totalOutstanding = computed(() => sum(durableRows.value, (r) => r.outstanding));
const totalReturnQty = computed(() =>
    sum(pendingRows.value, (r) => r.return_qty ?? 0),
);
const damagedQty = computed(() =>
    sum(
        pendingRows.value.filter((r) => r.to_condition === "DAMAGED"),
        (r) => r.return_qty ?? 0,
    ),
);
const canSubmit = computed(() => totalReturnQty.value > 0);

const summaryStats = computed(() => [
    { label: "Total dipinjam", value: totalBorrowed.value, class: "text-slate-900" },
    { label: "Sudah kembali", value: totalReturned.value, class: "text-emerald-700" },
    { label: "Sisa belum kembali", value: totalOutstanding.value, class: "text-amber-700" },
]);

function pct(part: number, total: number) {
    if (!total) return "0%";
    return `${Math.min(100, (Number(part) / total) * 100)}%`;
}

function fillAll() {
    pendingRows.value.forEach((r) => (r.return_qty = r.outstanding));
}

function resetAll() {
    pendingRows.value.forEach((r) => {
        r.return_qty = null;
        r.to_condition = "GOOD";
    });
}

/* ---------------- Submit ---------------- */
const showConfirm = ref(false);
const submitting = ref(false);

function goBack() {
    router.visit(route("borrowing.show", tx.value.id));
}


function askConfirm() {
    if (!canSubmit.value) {
        message.error("Isi jumlah pengembalian minimal pada satu barang.");
        return;
    }
    const over = selectedRows.value.find(
        (r) => (r.return_qty ?? 0) > r.outstanding,
    );
    if (over) {
        message.error(`Jumlah kembali melebihi sisa pada ${over.product_name}.`);
        return;
    }
    showConfirm.value = true;
}

function submit() {
    const lines = selectedRows.value.map((r) => ({
        borrow_line_id: r.id,
        quantity: Number(r.return_qty),
        condition: r.to_condition,
        warehouse_id: r.to_warehouse_id,
    }));

    submitting.value = true;
    router.post(
        route("borrowing.return.store", tx.value.id),
        { lines },
        {
            preserveScroll: true,
            onSuccess: () => (showConfirm.value = false),
            onError: (errors) => {
                const first = Object.values(errors)[0];
                message.error(typeof first === "string" ? first : "Gagal menyimpan pengembalian");
            },
            onFinish: () => (submitting.value = false),
        },
    );
}
</script>

<style scoped>
.eyebrow {
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    color: rgb(148 163 184);
}
</style>
