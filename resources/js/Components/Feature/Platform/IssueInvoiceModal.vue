<template>
    <n-modal
        v-model:show="show"
        preset="card"
        :bordered="false"
        title="Buat Tagihan"
        class="!w-[calc(100vw-2rem)] max-w-2xl !rounded"
        :segmented="{ content: true, footer: true }"
        :mask-closable="!form.processing"
    >
        <div class="flex flex-col gap-4">
            <label v-if="!organizationId" class="flex flex-col gap-1">
                <span class="text-xs font-medium text-slate-600">Organisasi</span>
                <n-select
                    v-model:value="form.organization_id"
                    :options="organizations"
                    placeholder="Pilih organisasi"
                    filterable
                    :status="form.errors.organization_id ? 'error' : undefined"
                />
                <span v-if="form.errors.organization_id" class="text-xs text-rose-600">{{ form.errors.organization_id }}</span>
            </label>

            <!-- item -->
            <div class="flex flex-col gap-2">
                <div class="hidden grid-cols-12 gap-2 px-1 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-400 sm:grid">
                    <span class="col-span-6">Deskripsi</span>
                    <span class="col-span-2 text-right">Jumlah</span>
                    <span class="col-span-3 text-right">Harga</span>
                </div>
                <div
                    v-for="(item, i) in form.items"
                    :key="i"
                    class="grid grid-cols-12 items-start gap-2 rounded border border-slate-200 p-2 sm:border-0 sm:p-0"
                >
                    <n-input v-model:value="item.description" placeholder="cth: Langganan paket Basic Jan 2027" class="col-span-12 sm:col-span-6" />
                    <n-input-number v-model:value="item.quantity" :min="1" :show-button="false" class="col-span-4 sm:col-span-2" />
                    <n-input
                        :value="fmt(item.unit_price)"
                        placeholder="0"
                        inputmode="numeric"
                        class="col-span-6 sm:col-span-3"
                        @update:value="(v) => (item.unit_price = toNumber(v))"
                    >
                        <template #prefix><span class="text-xs text-slate-400">Rp</span></template>
                    </n-input>
                    <n-button
                        quaternary
                        class="col-span-2 sm:col-span-1"
                        :disabled="form.items.length === 1"
                        aria-label="Hapus item"
                        @click="form.items.splice(i, 1)"
                    >
                        <template #icon><n-icon :component="Delete20Regular" /></template>
                    </n-button>
                </div>
                <button
                    type="button"
                    class="flex items-center justify-center gap-1 rounded border border-dashed border-slate-300 py-2 text-xs font-semibold text-slate-600 hover:border-[#2A126C] hover:text-[#2A126C]"
                    @click="form.items.push({ description: '', quantity: 1, unit_price: 0 })"
                >
                    <n-icon :component="Add20Regular" /> Tambah item
                </button>
                <p v-if="itemError" class="text-xs text-rose-600">{{ itemError }}</p>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <label class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-slate-600">Diskon</span>
                    <n-input :value="fmt(form.discount)" inputmode="numeric" @update:value="(v) => (form.discount = toNumber(v))">
                        <template #prefix><span class="text-xs text-slate-400">Rp</span></template>
                    </n-input>
                </label>
                <label class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-slate-600">Pajak</span>
                    <n-input :value="fmt(form.tax)" inputmode="numeric" @update:value="(v) => (form.tax = toNumber(v))">
                        <template #prefix><span class="text-xs text-slate-400">Rp</span></template>
                    </n-input>
                </label>
                <label class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-slate-600">Jatuh tempo</span>
                    <n-input-number v-model:value="form.due_in_days" :min="0" :max="90">
                        <template #suffix><span class="text-xs text-slate-400">hari</span></template>
                    </n-input-number>
                </label>
            </div>

            <n-input v-model:value="form.notes" type="textarea" :autosize="{ minRows: 2 }" placeholder="Catatan untuk pelanggan (opsional)" />

            <div class="rounded border border-slate-200 bg-slate-50/60 p-3 text-sm">
                <div class="flex justify-between text-slate-600"><span>Subtotal</span><span class="tabular-nums">{{ formatIDR(subtotal) }}</span></div>
                <div v-if="form.discount" class="flex justify-between text-slate-600"><span>Diskon</span><span class="tabular-nums">− {{ formatIDR(form.discount) }}</span></div>
                <div v-if="form.tax" class="flex justify-between text-slate-600"><span>Pajak</span><span class="tabular-nums">{{ formatIDR(form.tax) }}</span></div>
                <div class="mt-1 flex justify-between border-t border-slate-200 pt-1 font-bold text-slate-900">
                    <span>Total</span><span class="tabular-nums">{{ formatIDR(total) }}</span>
                </div>
            </div>
            <p v-if="(form.errors as any).billing" class="text-xs text-rose-600">{{ (form.errors as any).billing }}</p>
        </div>

        <template #footer>
            <div class="flex justify-end gap-2">
                <n-button :disabled="form.processing" @click="show = false">Batal</n-button>
                <n-button type="primary" :loading="form.processing" @click="submit">Terbitkan Tagihan</n-button>
            </div>
        </template>
    </n-modal>
</template>

<script setup lang="ts">
import { computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import { NButton, NIcon, NInput, NInputNumber, NModal, NSelect } from "naive-ui";
import { Add20Regular, Delete20Regular } from "@vicons/fluent";
import { formatIDR } from "./platform-meta";

const props = defineProps<{
    /** isi bila organisasi sudah pasti (mis. dari halaman detail organisasi) */
    organizationId?: number | null;
    organizations?: { label: string; value: number }[];
}>();

const show = defineModel<boolean>("show", { default: false });

const form = useForm({
    organization_id: null as number | null,
    items: [{ description: "", quantity: 1, unit_price: 0 }],
    discount: 0,
    tax: 0,
    due_in_days: 7,
    notes: "",
});

watch(show, (open) => {
    if (!open) return;
    form.reset();
    form.clearErrors();
    form.organization_id = props.organizationId ?? null;
});

const fmt = (n: number) => (n ? new Intl.NumberFormat("id-ID").format(n) : "");
const toNumber = (v: string) => Number(v.replace(/\D/g, "")) || 0;

const subtotal = computed(() => form.items.reduce((s, i) => s + (i.quantity || 0) * (i.unit_price || 0), 0));
const total = computed(() => Math.max(0, subtotal.value - form.discount + form.tax));

const itemError = computed(() => {
    const e = form.errors as Record<string, string>;
    const key = Object.keys(e).find((k) => k.startsWith("items"));
    return key ? e[key] : null;
});

function submit() {
    form.post(route("platform.invoices.store"), {
        preserveScroll: true,
        onSuccess: () => (show.value = false),
    });
}
</script>
