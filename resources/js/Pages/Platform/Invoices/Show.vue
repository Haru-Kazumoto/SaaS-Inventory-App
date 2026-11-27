<template>
    <div class="flex flex-col gap-5">
        <TitlePage eyebrow="Tagihan" :title="invoice.number" :subTitle="`${invoice.organization.name} · ${formatIDR(invoice.total)}`" class="no-print">
            <template #action>
                <div class="flex w-full flex-wrap gap-2 sm:w-auto">
                    <n-button class="flex-1 sm:flex-none" @click="router.visit(route('platform.invoices.index'))">
                        <template #icon><n-icon :component="ArrowLeft20Regular" /></template>
                        Kembali
                    </n-button>
                    <n-button class="flex-1 sm:flex-none" @click="print">
                        <template #icon><n-icon :component="Print20Regular" /></template>
                        Cetak / PDF
                    </n-button>
                    <n-button v-if="canPay" type="primary" class="flex-1 sm:flex-none" @click="showPay = true">
                        <template #icon><n-icon :component="Payment20Regular" /></template>
                        Catat Pembayaran
                    </n-button>
                </div>
            </template>
        </TitlePage>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <!-- Dokumen invoice -->
            <article class="print-area rounded border border-slate-200 bg-white p-6 sm:p-8 lg:col-span-2">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#2A126C]">Invoice</p>
                        <p class="mt-1 font-mono text-xl font-bold text-slate-900">{{ invoice.number }}</p>
                        <div class="mt-2"><StatusBadge kind="invoice" :status="invoice.display_status" /></div>
                    </div>
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-1 text-xs sm:text-right">
                        <dt class="text-slate-500">Tanggal terbit</dt><dd class="font-medium tabular-nums text-slate-900">{{ formatDay(invoice.issued_at) }}</dd>
                        <dt class="text-slate-500">Jatuh tempo</dt>
                        <dd class="font-medium tabular-nums" :class="invoice.display_status === 'overdue' ? 'text-rose-600' : 'text-slate-900'">{{ formatDay(invoice.due_at) }}</dd>
                        <template v-if="invoice.period_start">
                            <dt class="text-slate-500">Periode</dt>
                            <dd class="font-medium tabular-nums text-slate-900">{{ formatDay(invoice.period_start) }} – {{ formatDay(invoice.period_end) }}</dd>
                        </template>
                    </dl>
                </div>

                <div class="mt-6 rounded border border-slate-100 bg-slate-50/60 p-4">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">Ditagihkan kepada</p>
                    <p class="mt-1 text-sm font-bold text-slate-900">{{ invoice.organization.name }}</p>
                    <p class="text-xs text-slate-600">{{ invoice.organization.owner_name }}</p>
                    <p class="text-xs text-slate-600">{{ invoice.organization.owner_email }}</p>
                    <p class="text-xs text-slate-600">{{ invoice.organization.address }}</p>
                </div>

                <table class="mt-6 w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">
                            <th class="py-2">Deskripsi</th>
                            <th class="w-16 py-2 text-right">Qty</th>
                            <th class="w-32 py-2 text-right">Harga</th>
                            <th class="w-32 py-2 text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="it in invoice.items" :key="it.id">
                            <td class="py-3 pr-3 text-slate-800">{{ it.description }}</td>
                            <td class="py-3 text-right tabular-nums text-slate-600">{{ it.quantity }}</td>
                            <td class="py-3 text-right tabular-nums text-slate-600">{{ formatIDR(it.unit_price) }}</td>
                            <td class="py-3 text-right font-semibold tabular-nums text-slate-900">{{ formatIDR(it.amount) }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="mt-4 ml-auto flex max-w-xs flex-col gap-1 text-sm">
                    <div class="flex justify-between text-slate-600"><span>Subtotal</span><span class="tabular-nums">{{ formatIDR(invoice.subtotal) }}</span></div>
                    <div v-if="invoice.discount" class="flex justify-between text-slate-600"><span>Diskon</span><span class="tabular-nums">− {{ formatIDR(invoice.discount) }}</span></div>
                    <div v-if="invoice.tax" class="flex justify-between text-slate-600"><span>Pajak</span><span class="tabular-nums">{{ formatIDR(invoice.tax) }}</span></div>
                    <div class="flex justify-between border-t border-slate-200 pt-2 text-base font-bold text-slate-900">
                        <span>Total</span><span class="tabular-nums">{{ formatIDR(invoice.total) }}</span>
                    </div>
                    <div v-if="invoice.paid" class="flex justify-between text-emerald-700"><span>Dibayar</span><span class="tabular-nums">− {{ formatIDR(invoice.paid) }}</span></div>
                    <div v-if="invoice.paid && invoice.remaining" class="flex justify-between font-semibold text-amber-700">
                        <span>Sisa</span><span class="tabular-nums">{{ formatIDR(invoice.remaining) }}</span>
                    </div>
                </div>

                <p v-if="invoice.notes" class="mt-6 whitespace-pre-line border-t border-slate-100 pt-4 text-xs leading-relaxed text-slate-500">{{ invoice.notes }}</p>
            </article>

            <!-- Panel status & pembayaran -->
            <aside class="no-print flex flex-col gap-5">
                <section class="rounded border border-slate-200 bg-white">
                    <header class="border-b border-slate-100 px-5 py-4">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">Status pembayaran</p>
                        <p class="mt-1 text-2xl font-bold tabular-nums" :class="invoice.remaining ? 'text-slate-900' : 'text-emerald-700'">
                            {{ invoice.remaining ? formatIDR(invoice.remaining) : "Lunas" }}
                        </p>
                        <p v-if="invoice.remaining" class="text-xs text-slate-500">belum dibayar dari {{ formatIDR(invoice.total) }}</p>
                        <p v-else-if="invoice.paid_at" class="text-xs text-slate-500">dilunasi {{ formatDateTime(invoice.paid_at) }}</p>
                        <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-emerald-500" :style="{ width: `${paidPct}%` }"></div>
                        </div>
                    </header>
                    <div class="flex flex-col gap-2 p-5">
                        <n-button v-if="canPay" type="primary" block @click="showPay = true">
                            <template #icon><n-icon :component="Payment20Regular" /></template>
                            Catat Pembayaran
                        </n-button>
                        <n-button
                            block
                            @click="router.visit(route('platform.organizations.show', invoice.organization.id))"
                        >
                            Lihat organisasi
                        </n-button>
                        <n-button v-if="canVoid" block type="error" ghost @click="showVoid = true">Batalkan tagihan</n-button>
                    </div>
                </section>

                <section class="rounded border border-slate-200 bg-white">
                    <header class="border-b border-slate-100 px-5 py-4">
                        <h2 class="text-sm font-bold text-slate-900">Riwayat pembayaran</h2>
                    </header>
                    <p v-if="!invoice.payments.length" class="px-5 py-6 text-center text-xs text-slate-400">Belum ada pembayaran.</p>
                    <ul v-else class="divide-y divide-slate-100">
                        <li v-for="p in invoice.payments" :key="p.id" class="px-5 py-3 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-slate-800">{{ labelOf(PAYMENT_METHODS, p.method) }}</span>
                                <span class="text-sm font-bold tabular-nums text-emerald-700">+{{ formatIDR(p.amount) }}</span>
                            </div>
                            <p class="mt-0.5 text-slate-500">
                                {{ formatDateTime(p.paid_at) }}<template v-if="p.reference"> · Ref {{ p.reference }}</template>
                            </p>
                            <p v-if="p.recorded_by" class="text-slate-400">Dicatat oleh {{ p.recorded_by }}</p>
                            <p v-if="p.notes" class="mt-1 text-slate-600">{{ p.notes }}</p>
                        </li>
                    </ul>
                </section>
            </aside>
        </div>

        <RecordPaymentModal v-model:show="showPay" :invoice="invoice" />

        <n-modal
            v-model:show="showVoid"
            preset="card"
            :bordered="false"
            title="Batalkan tagihan?"
            class="!w-[calc(100vw-2rem)] max-w-md !rounded"
            :segmented="{ footer: true }"
        >
            <p class="mb-3 text-sm text-slate-600">
                Tagihan <b class="font-mono text-slate-900">{{ invoice.number }}</b> tidak bisa ditagih atau dibayar lagi setelah dibatalkan.
            </p>
            <n-input
                v-model:value="voidForm.reason"
                type="textarea"
                :autosize="{ minRows: 2 }"
                placeholder="Alasan pembatalan (wajib)"
                :status="voidForm.errors.reason ? 'error' : undefined"
            />
            <p v-if="voidForm.errors.reason || (voidForm.errors as any).billing" class="mt-1 text-xs text-rose-600">
                {{ voidForm.errors.reason || (voidForm.errors as any).billing }}
            </p>
            <template #footer>
                <div class="flex justify-end gap-2">
                    <n-button @click="showVoid = false">Batal</n-button>
                    <n-button type="error" :loading="voidForm.processing" @click="submitVoid">Ya, batalkan</n-button>
                </div>
            </template>
        </n-modal>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import { NButton, NIcon, NInput, NModal } from "naive-ui";
import { ArrowLeft20Regular, Payment20Regular, Print20Regular } from "@vicons/fluent";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TitlePage from "@/Components/TitlePage.vue";
import StatusBadge from "@/Components/Feature/Platform/StatusBadge.vue";
import RecordPaymentModal from "@/Components/Feature/Platform/RecordPaymentModal.vue";
import { PAYMENT_METHODS, formatDateTime, formatDay, formatIDR, labelOf } from "@/Components/Feature/Platform/platform-meta";
import { useFlash } from "@/Composables/useFlash";

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps<{
    invoice: {
        id: number;
        number: string;
        status: string;
        display_status: string;
        subtotal: number;
        discount: number;
        tax: number;
        total: number;
        paid: number;
        remaining: number;
        issued_at: string;
        due_at: string;
        paid_at?: string | null;
        period_start?: string | null;
        period_end?: string | null;
        notes?: string | null;
        organization: { id: number; name: string; owner_name?: string; owner_email?: string; address?: string };
        items: { id: number; description: string; quantity: number; unit_price: number; amount: number }[];
        payments: { id: number; amount: number; method: string; reference?: string; notes?: string; paid_at: string; recorded_by?: string }[];
    };
}>();

useFlash();

const showPay = ref(false);
const showVoid = ref(false);

const canPay = computed(() => props.invoice.status === "unpaid" && props.invoice.remaining > 0);
const canVoid = computed(() => props.invoice.status === "unpaid" && props.invoice.paid === 0);
const paidPct = computed(() => (props.invoice.total ? Math.min(100, (props.invoice.paid / props.invoice.total) * 100) : 100));

const voidForm = useForm({ reason: "" });
function submitVoid() {
    voidForm.patch(route("platform.invoices.void", props.invoice.id), {
        preserveScroll: true,
        onSuccess: () => (showVoid.value = false),
    });
}

function print() {
    window.print();
}
</script>

<style>
/* Cetak hanya dokumen invoice */
@media print {
    body * {
        visibility: hidden;
    }
    .print-area,
    .print-area * {
        visibility: visible;
    }
    .print-area {
        position: absolute;
        inset: 0 auto auto 0;
        width: 100%;
        border: none !important;
    }
}
</style>
