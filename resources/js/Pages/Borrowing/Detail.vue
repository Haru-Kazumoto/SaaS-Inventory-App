<template>
    <div class="flex flex-col gap-5">
        <TitlePage
            eyebrow="Peminjaman"
            title="Detail Peminjaman"
            subTitle="Informasi lengkap peminjam dan daftar barang yang dipinjam."
        >
            <template #action>
                <div class="flex w-full gap-2 sm:w-auto">
                    <n-button class="flex-1 sm:flex-none" @click="goBack">
                        <template #icon>
                            <n-icon :component="ArrowLeft20Regular" />
                        </template>
                        Kembali
                    </n-button>
                    <n-button
                        v-if="!isCompleted"
                        type="primary"
                        class="flex-1 sm:flex-none"
                        @click="goReturn"
                    >
                        <template #icon>
                            <n-icon :component="ArrowReply20Regular" />
                        </template>
                        Proses Pengembalian
                    </n-button>
                </div>
            </template>
        </TitlePage>

        <!-- RINGKASAN -->
        <section class="rounded border border-slate-200 bg-white">
            <div
                class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex min-w-0 items-center gap-4">
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded bg-[#2A126C] text-sm font-bold text-white"
                    >
                        {{ getInitials(tx.borrower_name) }}
                    </div>
                    <div class="min-w-0">
                        <p class="eyebrow">Peminjam</p>
                        <p
                            class="truncate text-lg font-bold tracking-tight text-slate-900"
                        >
                            {{ tx.borrower_name || "-" }}
                        </p>
                        <p
                            v-if="tx.tx_number"
                            class="truncate font-mono text-[11px] text-slate-400"
                        >
                            {{ tx.tx_number }}
                        </p>
                    </div>
                </div>
                <BorrowStatusBadge :status="tx.status" class="self-start sm:self-center" />
            </div>

            <dl
                class="grid grid-cols-2 border-t border-slate-100 sm:grid-cols-4"
            >
                <div
                    v-for="(stat, i) in stats"
                    :key="stat.label"
                    class="flex flex-col gap-1 p-4 sm:px-5"
                    :class="[
                        i % 2 === 1 ? 'border-l border-slate-100' : '',
                        i >= 2
                            ? 'border-t border-slate-100 sm:border-t-0 sm:border-l'
                            : '',
                    ]"
                >
                    <dt class="eyebrow">{{ stat.label }}</dt>
                    <dd
                        class="text-base font-semibold tabular-nums text-slate-900"
                    >
                        {{ stat.value }}
                        <span
                            v-if="stat.hint"
                            class="text-[11px] font-medium text-slate-400"
                            >{{ stat.hint }}</span
                        >
                    </dd>
                </div>
            </dl>
        </section>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <!-- DAFTAR BARANG -->
            <section
                class="rounded border border-slate-200 bg-white lg:col-span-2 lg:self-start"
            >
                <header
                    class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4"
                >
                    <div class="flex items-center gap-2.5">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded bg-[#2A126C]/[0.06] text-[#2A126C]"
                        >
                            <n-icon :component="Cube20Regular" size="18" />
                        </div>
                        <h2 class="text-sm font-bold text-slate-900">
                            Barang yang Dipinjam
                        </h2>
                    </div>
                    <span
                        class="rounded border border-[#2A126C]/15 bg-[#2A126C]/[0.05] px-2 py-0.5 text-[11px] font-semibold tabular-nums text-[#2A126C]"
                    >
                        {{ lines.length }} jenis
                    </span>
                </header>

                <template v-if="lines.length">
                    <!-- Desktop / tablet table -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr
                                    class="bg-slate-50 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500"
                                >
                                    <th class="w-12 px-5 py-2.5 text-center">#</th>
                                    <th class="px-3 py-2.5">Barang</th>
                                    <th class="px-3 py-2.5">Gudang</th>
                                    <th class="px-5 py-2.5 text-right">Qty</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr
                                    v-for="(row, idx) in lines"
                                    :key="row.id ?? idx"
                                    class="hover:bg-slate-50/60"
                                >
                                    <td
                                        class="px-5 py-3 text-center text-xs tabular-nums text-slate-400"
                                    >
                                        {{ idx + 1 }}
                                    </td>
                                    <td class="px-3 py-3">
                                        <p
                                            class="text-[13px] font-semibold text-slate-900"
                                        >
                                            {{ row.product_name }}
                                        </p>
                                        <p
                                            class="font-mono text-[11px] text-slate-400"
                                        >
                                            {{ row.product_code }}
                                        </p>
                                    </td>
                                    <td class="px-3 py-3 text-xs text-slate-600">
                                        {{ row.warehouse_name || "-" }}
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <span
                                            class="text-sm font-semibold tabular-nums text-slate-900"
                                            >{{ row.quantity }}</span
                                        >
                                        <span
                                            class="ml-1 text-[11px] text-slate-400"
                                            >{{ row.unit_name ?? "" }}</span
                                        >
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-slate-200">
                                    <td
                                        colspan="3"
                                        class="px-5 py-3 text-xs font-semibold text-slate-500"
                                    >
                                        Total kuantitas
                                    </td>
                                    <td
                                        class="px-5 py-3 text-right text-sm font-bold tabular-nums text-slate-900"
                                    >
                                        {{ totalQty }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Mobile list -->
                    <ul class="divide-y divide-slate-100 sm:hidden">
                        <li
                            v-for="(row, idx) in lines"
                            :key="row.id ?? idx"
                            class="flex items-center gap-3 px-4 py-3"
                        >
                            <div class="min-w-0 flex-1">
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
                            <div
                                class="shrink-0 rounded border border-slate-200 bg-slate-50 px-2 py-1 text-right"
                            >
                                <span
                                    class="text-sm font-semibold tabular-nums text-slate-900"
                                    >{{ row.quantity }}</span
                                >
                                <span class="ml-1 text-[11px] text-slate-400">{{
                                    row.unit_name ?? ""
                                }}</span>
                            </div>
                        </li>
                        <li
                            class="flex items-center justify-between px-4 py-3 text-xs"
                        >
                            <span class="font-semibold text-slate-500"
                                >Total kuantitas</span
                            >
                            <span
                                class="text-sm font-bold tabular-nums text-slate-900"
                                >{{ totalQty }}</span
                            >
                        </li>
                    </ul>
                </template>

                <div
                    v-else
                    class="flex flex-col items-center justify-center py-12 text-slate-400"
                >
                    <n-icon :component="Cube20Regular" size="32" class="opacity-40" />
                    <p class="mt-2 text-sm">
                        Tidak ada barang yang tercatat pada peminjaman ini.
                    </p>
                </div>
            </section>

            <!-- SIDEBAR -->
            <div class="flex flex-col gap-5">
                <section class="rounded border border-slate-200 bg-white">
                    <header
                        class="flex items-center gap-2.5 border-b border-slate-100 px-5 py-4"
                    >
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded bg-[#2A126C]/[0.06] text-[#2A126C]"
                        >
                            <n-icon :component="Person20Regular" size="18" />
                        </div>
                        <h2 class="text-sm font-bold text-slate-900">
                            Informasi Peminjam
                        </h2>
                    </header>

                    <dl class="divide-y divide-slate-100">
                        <div
                            v-for="item in borrowerInfo"
                            :key="item.label"
                            class="flex flex-col gap-0.5 px-5 py-3"
                        >
                            <dt class="eyebrow">{{ item.label }}</dt>
                            <dd
                                class="break-words text-sm text-slate-800"
                                :class="item.mono ? 'tabular-nums' : ''"
                            >
                                <a
                                    v-if="item.href"
                                    :href="item.href"
                                    class="font-medium text-[#2A126C] hover:underline"
                                    >{{ item.value }}</a
                                >
                                <template v-else>{{ item.value }}</template>
                            </dd>
                        </div>
                    </dl>
                </section>

                <section class="rounded border border-slate-200 bg-white">
                    <header
                        class="flex items-center gap-2.5 border-b border-slate-100 px-5 py-4"
                    >
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded bg-[#2A126C]/[0.06] text-[#2A126C]"
                        >
                            <n-icon
                                :component="DocumentBulletList20Regular"
                                size="18"
                            />
                        </div>
                        <h2 class="text-sm font-bold text-slate-900">
                            Keperluan & Catatan
                        </h2>
                    </header>

                    <div class="flex flex-col gap-3 px-5 py-4">
                        <p
                            class="whitespace-pre-line text-sm leading-relaxed"
                            :class="
                                tx.description
                                    ? 'text-slate-700'
                                    : 'italic text-slate-400'
                            "
                        >
                            {{ tx.description || "Tidak ada keterangan." }}
                        </p>
                        <p
                            class="border-t border-slate-100 pt-3 text-xs text-slate-500"
                        >
                            Dicatat oleh
                            <span class="font-semibold text-slate-700">{{
                                tx.created_by_name || "-"
                            }}</span>
                            <template v-if="tx.created_by_role">
                                ({{ tx.created_by_role }})</template
                            >
                            pada
                            <span class="tabular-nums">{{ createdAtLabel }}</span>.
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, PropType } from "vue";
import { router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TitlePage from "@/Components/TitlePage.vue";
import BorrowStatusBadge from "@/Components/Feature/Borrowing/BorrowStatusBadge.vue";
import {
    formatDateParts,
    formatPhone,
    getInitials,
} from "@/Components/Feature/Borrowing/borrow-status";
import { NButton, NIcon } from "naive-ui";
import {
    ArrowLeft20Regular,
    ArrowReply20Regular,
    Cube20Regular,
    DocumentBulletList20Regular,
    Person20Regular,
} from "@vicons/fluent";

defineOptions({ layout: AuthenticatedLayout });

interface TxHeader {
    id: number | string;
    tx_number?: string | null;
    tx_type: string;
    status: string;
    borrower_id?: number | null;
    borrower_name?: string | null;
    borrower_phone?: string | null;
    borrower_room?: string | null;
    borrower_supervisor?: string | null;
    description?: string | null;
    occurred_at?: string | null;
    created_at: string;
    returned_at?: string | null;
    due_date?: string | null;
    created_by?: number | null;
    created_by_name?: string | null;
    created_by_role?: string | null;
}

interface BorrowItem {
    id?: number | string;
    product_id: number;
    product_name: string;
    product_code: string;
    quantity: number;
    unit_name?: string | null;
    warehouse_id?: number | null;
    warehouse_name?: string | null;
    condition_from?: string | null;
    condition_to?: string | null;
    line_no?: number | null;
}

const props = defineProps({
    transaction: { type: Object as PropType<TxHeader>, required: true },
    lines: { type: Array as PropType<BorrowItem[]>, required: true },
    summary: {
        type: Object as PropType<{ total_lines: number; total_qty: number }>,
        required: false,
    },
});

const tx = computed(() => props.transaction);
const lines = computed(() => props.lines ?? []);

const isCompleted = computed(() =>
    ["COMPLETED", "RETURNED"].includes((tx.value.status || "").toUpperCase()),
);

const totalQty = computed(
    () =>
        props.summary?.total_qty ??
        lines.value.reduce((sum, l) => sum + Number(l.quantity || 0), 0),
);

const borrowedAt = computed(() =>
    formatDateParts(tx.value.occurred_at || tx.value.created_at),
);

const createdAtLabel = computed(() => {
    const p = formatDateParts(tx.value.created_at);
    return `${p.date} ${p.time}`.trim();
});

const stats = computed(() => [
    { label: "Tanggal pinjam", value: borrowedAt.value.date, hint: "" },
    { label: "Jam", value: borrowedAt.value.time || "-", hint: "WIB" },
    { label: "Jenis barang", value: lines.value.length, hint: "jenis" },
    { label: "Total kuantitas", value: totalQty.value, hint: "unit" },
]);

const borrowerInfo = computed(() => {
    const phoneDigits = (tx.value.borrower_phone || "").replace(/\D/g, "");
    return [
        { label: "Nama", value: tx.value.borrower_name || "-" },
        {
            label: "Nomor telepon",
            value: formatPhone(tx.value.borrower_phone),
            href: phoneDigits ? `tel:${phoneDigits}` : undefined,
            mono: true,
        },
        { label: "Ruangan", value: tx.value.borrower_room || "-" },
        {
            label: "Pengawas ruangan",
            value: tx.value.borrower_supervisor || "-",
        },
    ];
});

function goBack() {
    router.visit(route("borrowing.index"));
}

function goReturn() {
    router.visit(route("borrowing.returning", tx.value.id));
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
