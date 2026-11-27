<template>
    <n-modal
        v-model:show="show"
        preset="card"
        :bordered="false"
        :title="`Catat Pembayaran · ${invoice.number}`"
        class="!w-[calc(100vw-2rem)] max-w-md !rounded"
        :segmented="{ content: true, footer: true }"
        :mask-closable="!form.processing"
    >
        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between rounded border border-slate-200 bg-slate-50/60 px-3 py-2 text-sm">
                <span class="text-slate-600">Sisa tagihan</span>
                <span class="font-bold tabular-nums text-slate-900">{{ formatIDR(invoice.remaining) }}</span>
            </div>

            <label class="flex flex-col gap-1">
                <span class="text-xs font-medium text-slate-600">Jumlah dibayar</span>
                <div class="flex gap-2">
                    <n-input
                        :value="fmt(form.amount)"
                        inputmode="numeric"
                        class="flex-1"
                        :status="form.errors.amount ? 'error' : undefined"
                        @update:value="(v) => (form.amount = Number(v.replace(/\D/g, '')) || 0)"
                    >
                        <template #prefix><span class="text-xs text-slate-400">Rp</span></template>
                    </n-input>
                    <n-button @click="form.amount = invoice.remaining">Lunasi</n-button>
                </div>
                <span v-if="form.errors.amount" class="text-xs text-rose-600">{{ form.errors.amount }}</span>
                <span v-else-if="form.amount && form.amount < invoice.remaining" class="text-xs text-amber-600">
                    Pembayaran sebagian, sisa {{ formatIDR(invoice.remaining - form.amount) }}.
                </span>
            </label>

            <div class="flex flex-col gap-1">
                <span class="text-xs font-medium text-slate-600">Metode</span>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    <button
                        v-for="m in PAYMENT_METHODS"
                        :key="m.value"
                        type="button"
                        class="rounded border px-2 py-1.5 text-xs font-semibold transition-colors duration-150"
                        :class="
                            form.method === m.value
                                ? 'border-[#2A126C] bg-[#2A126C]/[0.05] text-[#2A126C]'
                                : 'border-slate-300 text-slate-600 hover:border-[#2A126C]/50'
                        "
                        @click="form.method = m.value"
                    >
                        {{ m.label }}
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <label class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-slate-600">Tanggal bayar</span>
                    <n-date-picker
                        v-model:formatted-value="form.paid_at"
                        type="datetime"
                        value-format="yyyy-MM-dd HH:mm:ss"
                        format="dd MMM yyyy HH:mm"
                        :is-date-disabled="(ts: number) => ts > Date.now()"
                        :status="form.errors.paid_at ? 'error' : undefined"
                    />
                </label>
                <label class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-slate-600">No. referensi</span>
                    <n-input v-model:value="form.reference" placeholder="cth: no. transfer" />
                </label>
            </div>
            <n-input v-model:value="form.notes" type="textarea" :autosize="{ minRows: 2 }" placeholder="Catatan (opsional)" />

            <p v-if="(form.errors as any).billing" class="text-xs text-rose-600">{{ (form.errors as any).billing }}</p>
        </div>

        <template #footer>
            <div class="flex justify-end gap-2">
                <n-button :disabled="form.processing" @click="show = false">Batal</n-button>
                <n-button type="primary" :loading="form.processing" :disabled="!form.amount" @click="submit">
                    Simpan Pembayaran
                </n-button>
            </div>
        </template>
    </n-modal>
</template>

<script setup lang="ts">
import { watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import { NButton, NDatePicker, NInput, NModal } from "naive-ui";
import { PAYMENT_METHODS, formatIDR } from "./platform-meta";

const props = defineProps<{ invoice: { id: number; number: string; remaining: number } }>();
const show = defineModel<boolean>("show", { default: false });

const pad = (n: number) => String(n).padStart(2, "0");
const nowString = () => {
    const d = new Date();
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:00`;
};

const form = useForm({
    amount: 0,
    method: "bank_transfer",
    paid_at: nowString(),
    reference: "",
    notes: "",
});

watch(show, (open) => {
    if (!open) return;
    form.clearErrors();
    form.amount = props.invoice.remaining;
    form.method = "bank_transfer";
    form.paid_at = nowString();
    form.reference = "";
    form.notes = "";
});

const fmt = (n: number) => (n ? new Intl.NumberFormat("id-ID").format(n) : "");

function submit() {
    form.post(route("platform.invoices.payments.store", props.invoice.id), {
        preserveScroll: true,
        onSuccess: () => (show.value = false),
    });
}
</script>
