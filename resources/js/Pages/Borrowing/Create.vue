<template>
    <div class="flex flex-col gap-5">
        <TitlePage eyebrow="Peminjaman" title="Catat Peminjaman" subTitle="Isi data peminjam, lalu pilih barang yang dipinjam atau dipakai.">
            <template #action>
                <n-button class="w-full sm:w-auto" @click="goBack">
                    <template #icon><n-icon :component="ArrowLeft20Regular" /></template>
                    Kembali
                </n-button>
            </template>
        </TitlePage>

        <div class="grid grid-cols-1 gap-5 pb-24 lg:grid-cols-3 lg:pb-0">
            <div class="flex flex-col gap-5 lg:col-span-2">
                <!-- 1. PEMINJAM -->
                <FormSection :step="1" title="Peminjam" description="Siapa yang meminjam dan untuk apa.">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <label v-if="units.length > 1" class="flex flex-col gap-1 sm:col-span-2">
                            <span class="text-xs font-medium text-slate-600">{{ t("unit") }} *</span>
                            <n-select :value="form.unit_id" :options="unitOptions" @update:value="onUnitChange" />
                            <span class="text-[11px] text-slate-400">Barang yang bisa dipilih mengikuti {{ t("unit").toLowerCase() }} ini.</span>
                        </label>
                        <label class="flex flex-col gap-1">
                            <span class="text-xs font-medium text-slate-600">Nama peminjam *</span>
                            <n-input v-model:value="form.borrower_name" placeholder="Nama lengkap" :status="err('borrower_name') ? 'error' : undefined" />
                            <span v-if="err('borrower_name')" class="text-xs text-rose-600">{{ err("borrower_name") }}</span>
                        </label>
                        <label class="flex flex-col gap-1">
                            <span class="text-xs font-medium text-slate-600">{{ t("borrower_id_label") }}</span>
                            <n-input v-model:value="form.identity_number" placeholder="Opsional" />
                            <span class="text-[11px] text-slate-400">Diisi agar data peminjam tersimpan & bisa dipakai lagi.</span>
                        </label>
                        <label class="flex flex-col gap-1">
                            <span class="text-xs font-medium text-slate-600">Telepon</span>
                            <n-input
                                :value="form.phone"
                                placeholder="08xx"
                                inputmode="tel"
                                @update:value="(v) => (form.phone = v.replace(/[^\d+]/g, ''))"
                            />
                        </label>
                        <label class="flex flex-col gap-1">
                            <span class="text-xs font-medium text-slate-600">Penanggung jawab</span>
                            <n-input v-model:value="form.responsible_name" placeholder="cth: guru / atasan" />
                        </label>
                        <label class="flex flex-col gap-1">
                            <span class="text-xs font-medium text-slate-600">Lokasi pemakaian</span>
                            <n-input v-model:value="form.usage_location" placeholder="cth: Lab 2, Ruang Rapat" />
                        </label>
                        <label class="flex flex-col gap-1">
                            <span class="text-xs font-medium text-slate-600">Jatuh tempo pengembalian</span>
                            <n-date-picker
                                v-model:formatted-value="form.due_at"
                                value-format="yyyy-MM-dd"
                                format="dd MMM yyyy"
                                type="date"
                                clearable
                                placeholder="Opsional"
                                :is-date-disabled="(ts: number) => ts < startOfToday"
                            />
                        </label>
                        <label class="flex flex-col gap-1 sm:col-span-2">
                            <span class="text-xs font-medium text-slate-600">Keperluan</span>
                            <n-input
                                v-model:value="form.description"
                                type="textarea"
                                :autosize="{ minRows: 2, maxRows: 4 }"
                                maxlength="500"
                                show-count
                                placeholder="cth: praktik jaringan kelas XI"
                            />
                        </label>
                    </div>
                </FormSection>

                <!-- 2. BARANG -->
                <FormSection :step="2" title="Barang" description="Pilih barang, gudang asal, dan jumlahnya.">
                    <template #aside>
                        <span class="shrink-0 rounded border border-[#2A126C]/15 bg-[#2A126C]/[0.05] px-2 py-0.5 text-[11px] font-semibold tabular-nums text-[#2A126C]">
                            {{ form.lines.length }} barang
                        </span>
                    </template>

                    <!-- pemilih barang -->
                    <div class="rounded border border-slate-200 bg-slate-50/60 p-3">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-12">
                            <label class="flex flex-col gap-1 sm:col-span-6">
                                <span class="text-xs font-medium text-slate-600">Barang</span>
                                <n-select
                                    :value="picker.product_id"
                                    :options="productOptions"
                                    :render-label="renderProductLabel"
                                    filterable
                                    placeholder="Cari nama atau kode barang"
                                    @update:value="onPickProduct"
                                />
                            </label>
                            <label class="flex flex-col gap-1 sm:col-span-4">
                                <span class="text-xs font-medium text-slate-600">Gudang asal</span>
                                <n-select
                                    v-model:value="picker.warehouse_id"
                                    :options="stockOptions"
                                    :loading="loadingStock"
                                    :disabled="!picker.product_id"
                                    placeholder="Pilih gudang"
                                />
                            </label>
                            <label class="flex flex-col gap-1 sm:col-span-2">
                                <span class="text-xs font-medium text-slate-600">Jumlah</span>
                                <n-input-number v-model:value="picker.quantity" :min="1" :max="pickerAvailable || undefined" :show-button="false" placeholder="0" />
                            </label>
                        </div>
                        <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <span class="text-[11px] text-slate-500">
                                <template v-if="picker.warehouse_id">
                                    Tersedia <b class="tabular-nums text-slate-700">{{ pickerAvailable }}</b> {{ pickedProduct?.uom_name }} (kondisi bagus)
                                </template>
                                <template v-else-if="pickedProduct">
                                    {{ pickedProduct.type === "CONSUMABLE" ? "Habis pakai — tidak perlu dikembalikan." : "Tidak habis pakai — wajib dikembalikan." }}
                                </template>
                            </span>
                            <n-button type="primary" :disabled="!canAddPicker" @click="addLine">
                                <template #icon><n-icon :component="Add20Regular" /></template>
                                Tambahkan
                            </n-button>
                        </div>
                    </div>

                    <!-- daftar barang -->
                    <ul v-if="form.lines.length" class="mt-4 divide-y divide-slate-100 rounded border border-slate-200">
                        <li v-for="(line, i) in form.lines" :key="`${line.product_id}-${line.warehouse_id}`" class="flex items-center gap-3 px-3 py-2.5">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-900">{{ line.product_name }}</p>
                                <p class="truncate text-[11px] text-slate-500">
                                    <span class="font-mono">{{ line.product_code }}</span> · {{ line.warehouse_name }}
                                    <span v-if="line.type === 'CONSUMABLE'" class="ml-1 rounded bg-sky-50 px-1 text-sky-700">habis pakai</span>
                                </p>
                                <p v-if="err(`lines.${i}.quantity`)" class="text-xs text-rose-600">{{ err(`lines.${i}.quantity`) }}</p>
                            </div>
                            <n-input-number v-model:value="line.quantity" :min="1" :max="line.available" size="small" class="!w-24" :show-button="false" />
                            <span class="w-10 text-xs text-slate-400">{{ line.uom_name }}</span>
                            <n-button quaternary size="small" aria-label="Hapus" @click="form.lines.splice(i, 1)">
                                <template #icon><n-icon :component="Delete20Regular" /></template>
                            </n-button>
                        </li>
                    </ul>
                    <p v-else class="mt-4 rounded border border-dashed border-slate-300 py-6 text-center text-sm text-slate-400">
                        Belum ada barang. Pilih barang di atas lalu klik <b>Tambahkan</b>.
                    </p>
                    <p v-if="err('lines')" class="mt-2 text-xs text-rose-600">{{ err("lines") }}</p>
                </FormSection>
            </div>

            <!-- RINGKASAN -->
            <aside class="hidden lg:block">
                <section class="sticky top-20 rounded border border-slate-200 bg-white">
                    <header class="border-b border-slate-100 px-5 py-4">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">Ringkasan</p>
                        <p class="mt-1 truncate text-base font-bold text-slate-900">{{ form.borrower_name || "Peminjam" }}</p>
                        <p class="text-xs text-slate-500">{{ form.usage_location || "Lokasi belum diisi" }}</p>
                    </header>
                    <dl class="divide-y divide-slate-100 px-5 text-sm">
                        <div class="flex justify-between py-2.5"><dt class="text-xs text-slate-500">Jenis barang</dt><dd class="font-semibold tabular-nums">{{ form.lines.length }}</dd></div>
                        <div class="flex justify-between py-2.5"><dt class="text-xs text-slate-500">Total jumlah</dt><dd class="font-semibold tabular-nums">{{ totalQty }}</dd></div>
                        <div class="flex justify-between py-2.5"><dt class="text-xs text-slate-500">Wajib kembali</dt><dd class="font-semibold tabular-nums text-amber-700">{{ durableQty }}</dd></div>
                        <div class="flex justify-between py-2.5"><dt class="text-xs text-slate-500">Jatuh tempo</dt><dd class="font-medium">{{ form.due_at ? formatDateParts(form.due_at).date : "-" }}</dd></div>
                    </dl>
                    <div class="flex flex-col gap-2 border-t border-slate-100 p-5">
                        <n-button type="primary" size="large" block :loading="form.processing" :disabled="!canSubmit" @click="submit">
                            <template #icon><n-icon :component="Save20Regular" /></template>
                            Simpan Peminjaman
                        </n-button>
                        <n-button block :disabled="form.processing" @click="goBack">Batal</n-button>
                    </div>
                </section>
            </aside>
        </div>

        <div
            class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 px-4 py-3 backdrop-blur lg:hidden"
            style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom))"
        >
            <div class="mx-auto flex max-w-3xl items-center gap-3">
                <p class="min-w-0 flex-1 text-sm font-bold tabular-nums text-slate-900">
                    {{ form.lines.length }} barang <span class="text-xs font-normal text-slate-500">· {{ totalQty }} unit</span>
                </p>
                <n-button type="primary" :loading="form.processing" :disabled="!canSubmit" @click="submit">Simpan</n-button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, h, reactive, ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import axios from "axios";
import { NButton, NDatePicker, NIcon, NInput, NInputNumber, NSelect, useMessage, type SelectOption } from "naive-ui";
import { Add20Regular, ArrowLeft20Regular, Delete20Regular, Save20Regular } from "@vicons/fluent";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TitlePage from "@/Components/TitlePage.vue";
import FormSection from "@/Components/FormSection.vue";
import { formatDateParts } from "@/Components/Feature/Borrowing/borrow-status";
import { useTerms } from "@/Composables/useTerms";

defineOptions({ layout: AuthenticatedLayout });

interface ProductOption {
    id: number;
    unit_id: number;
    name: string;
    code: string;
    type: "DURABLE" | "CONSUMABLE";
    uom_name: string | null;
    category_name: string | null;
    available: number;
}

const props = defineProps<{
    units: Array<{ id: number; name: string }>;
    products: ProductOption[];
}>();

const { t } = useTerms();
const message = useMessage();
const startOfToday = new Date(new Date().setHours(0, 0, 0, 0)).getTime();

const form = useForm({
    unit_id: props.units[0]?.id ?? null,
    borrower_name: "",
    identity_number: "",
    phone: "",
    responsible_name: "",
    usage_location: "",
    due_at: null as string | null,
    description: "",
    lines: [] as Array<{
        product_id: number;
        warehouse_id: number;
        quantity: number;
        product_name: string;
        product_code: string;
        warehouse_name: string;
        uom_name: string | null;
        type: string;
        available: number;
    }>,
});

const err = (key: string) => (form.errors as Record<string, string>)[key];
const unitOptions = computed(() => props.units.map((u) => ({ label: u.name, value: u.id })));

function onUnitChange(v: number) {
    if (form.lines.length && v !== form.unit_id) {
        message.warning("Daftar barang dikosongkan karena unit diganti.");
        form.lines = [];
    }
    form.unit_id = v;
    resetPicker();
}

/* ---------------- Pemilih barang ---------------- */
const picker = reactive({ product_id: null as number | null, warehouse_id: null as number | null, quantity: 1 as number | null });
const stock = ref<Array<{ id: number; name: string; good_quantity: number }>>([]);
const loadingStock = ref(false);

const productOptions = computed(() =>
    props.products
        .filter((p) => p.unit_id === form.unit_id)
        .map((p) => ({ label: `${p.name} ${p.code}`, value: p.id, disabled: p.available <= 0, product: p })),
);
const pickedProduct = computed(() => props.products.find((p) => p.id === picker.product_id));

const renderProductLabel = (option: SelectOption) => {
    const p = (option as any).product as ProductOption;
    return h("div", { class: "flex w-full items-center justify-between gap-3 py-0.5" }, [
        h("div", { class: "min-w-0" }, [
            h("div", { class: "truncate text-sm" }, p.name),
            h("div", { class: "truncate font-mono text-[11px] text-slate-400" }, `${p.code}${p.category_name ? " · " + p.category_name : ""}`),
        ]),
        h("span", { class: `shrink-0 text-xs tabular-nums ${p.available > 0 ? "text-slate-600" : "text-rose-500"}` }, p.available > 0 ? `${p.available} ${p.uom_name ?? ""}` : "habis"),
    ]);
};

const stockOptions = computed(() =>
    stock.value.map((w) => ({ label: `${w.name} (${w.good_quantity})`, value: w.id, disabled: w.good_quantity <= 0 })),
);

const alreadyAdded = (productId: number, warehouseId: number) =>
    form.lines.find((l) => l.product_id === productId && l.warehouse_id === warehouseId)?.quantity ?? 0;

const pickerAvailable = computed(() => {
    const w = stock.value.find((s) => s.id === picker.warehouse_id);
    if (!w || !picker.product_id) return 0;
    return Math.max(0, w.good_quantity - alreadyAdded(picker.product_id, w.id));
});

const canAddPicker = computed(() => !!picker.product_id && !!picker.warehouse_id && !!picker.quantity && picker.quantity <= pickerAvailable.value);

async function onPickProduct(id: number) {
    picker.product_id = id;
    picker.warehouse_id = null;
    picker.quantity = 1;
    stock.value = [];
    loadingStock.value = true;
    try {
        const { data } = await axios.get(route("products.stock", id));
        stock.value = data;
        const firstWithStock = data.find((w: { good_quantity: number }) => w.good_quantity > 0);
        if (firstWithStock) picker.warehouse_id = firstWithStock.id;
    } catch {
        message.error("Gagal memuat stok barang.");
    } finally {
        loadingStock.value = false;
    }
}

function addLine() {
    const p = pickedProduct.value;
    const w = stock.value.find((s) => s.id === picker.warehouse_id);
    if (!p || !w || !picker.quantity) return;

    const existing = form.lines.find((l) => l.product_id === p.id && l.warehouse_id === w.id);
    if (existing) {
        existing.quantity += picker.quantity;
    } else {
        form.lines.push({
            product_id: p.id,
            warehouse_id: w.id,
            quantity: picker.quantity,
            product_name: p.name,
            product_code: p.code,
            warehouse_name: w.name,
            uom_name: p.uom_name,
            type: p.type,
            available: w.good_quantity,
        });
    }
    resetPicker();
}

function resetPicker() {
    picker.product_id = null;
    picker.warehouse_id = null;
    picker.quantity = 1;
    stock.value = [];
}

/* ---------------- Ringkasan & simpan ---------------- */
const totalQty = computed(() => form.lines.reduce((s, l) => s + (l.quantity || 0), 0));
const durableQty = computed(() => form.lines.filter((l) => l.type === "DURABLE").reduce((s, l) => s + (l.quantity || 0), 0));
const canSubmit = computed(() => !!form.borrower_name.trim() && form.lines.length > 0 && !!form.unit_id);

function goBack() {
    router.visit(route("borrowing.index"));
}

function submit() {
    form.transform((d) => ({
        ...d,
        lines: d.lines.map((l) => ({ product_id: l.product_id, warehouse_id: l.warehouse_id, quantity: l.quantity })),
    })).post(route("borrowing.store"), {
        preserveScroll: true,
        onError: () => message.error("Periksa kembali data peminjaman."),
    });
}
</script>
