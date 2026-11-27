<template>
    <div class="grid grid-cols-1 gap-5 pb-24 lg:grid-cols-3 lg:pb-0">
        <!-- ================= KIRI: FORM ================= -->
        <div class="flex flex-col gap-5 lg:col-span-2">
            <n-form
                ref="formRef"
                :model="form"
                :rules="rules"
                label-placement="top"
                require-mark-placement="right-hanging"
                :show-require-mark="true"
                class="flex flex-col gap-5"
            >
                <!-- 1. INFORMASI DASAR -->
                <FormSection
                    :step="1"
                    title="Informasi Dasar"
                    description="Identitas utama barang. Kolom bertanda * wajib diisi."
                >
                    <div class="grid grid-cols-1 gap-x-4 sm:grid-cols-2">
                        <n-form-item
                            label="Nama barang"
                            path="name"
                            class="sm:col-span-2"
                            v-bind="serverError('name')"
                        >
                            <n-input
                                :value="form.name"
                                placeholder="cth: TANG CRIMPING RJ45"
                                maxlength="150"
                                @update:value="(v) => (form.name = v.toUpperCase())"
                            />
                        </n-form-item>

                        <n-form-item label="Kode barang" path="code" v-bind="serverError('code')">
                            <div class="flex w-full flex-col gap-1">
                                <n-input
                                    :value="form.code"
                                    placeholder="cth: TJKT_TANG_01"
                                    maxlength="100"
                                    @update:value="
                                        (v) =>
                                            (form.code = v
                                                .replace(/\s+/g, '_')
                                                .toUpperCase())
                                    "
                                />
                                <span class="text-[11px] text-slate-400">
                                    Spasi otomatis diganti garis bawah ( _ ).
                                </span>
                            </div>
                        </n-form-item>

                        <n-form-item label="Merk" path="brand_name" v-bind="serverError('brand_name')">
                            <n-input
                                :value="form.brand_name"
                                placeholder="cth: TEKIRO (opsional)"
                                @update:value="(v) => (form.brand_name = v.toUpperCase())"
                            />
                        </n-form-item>

                        <!-- Tipe: kartu pilihan -->
                        <n-form-item
                            label="Tipe barang"
                            path="type"
                            class="sm:col-span-2"
                            v-bind="serverError('type')"
                        >
                            <div
                                class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2"
                                role="radiogroup"
                                aria-label="Tipe barang"
                            >
                                <button
                                    v-for="opt in TYPE_OPTIONS"
                                    :key="opt.value"
                                    type="button"
                                    role="radio"
                                    :aria-checked="form.type === opt.value"
                                    class="flex items-start gap-3 rounded border p-3 text-left transition-colors duration-150"
                                    :class="
                                        form.type === opt.value
                                            ? 'border-[#2A126C] bg-[#2A126C]/[0.04] ring-1 ring-[#2A126C]'
                                            : 'border-slate-300 hover:border-[#2A126C]/50'
                                    "
                                    @click="selectType(opt.value)"
                                >
                                    <span
                                        class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border"
                                        :class="
                                            form.type === opt.value
                                                ? 'border-[#2A126C]'
                                                : 'border-slate-300'
                                        "
                                    >
                                        <span
                                            v-if="form.type === opt.value"
                                            class="h-2 w-2 rounded-full bg-[#2A126C]"
                                        ></span>
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block text-sm font-semibold text-slate-900">
                                            {{ opt.label }}
                                        </span>
                                        <span class="mt-0.5 block text-xs leading-relaxed text-slate-500">
                                            {{ opt.hint }}
                                        </span>
                                    </span>
                                </button>
                            </div>
                        </n-form-item>

                        <n-form-item label="Kategori" path="sub_type_code" v-bind="serverError('sub_type_code')">
                            <n-select
                                v-model:value="form.sub_type_code"
                                :options="subTypeOptions"
                                placeholder="Pilih kategori"
                                filterable
                                clearable
                            />
                        </n-form-item>

                        <n-form-item label="Satuan" path="unit_code" v-bind="serverError('unit_code')">
                            <n-select
                                v-model:value="form.unit_code"
                                :options="unitOptions"
                                placeholder="Pilih satuan (pcs, unit, dll)"
                                filterable
                                clearable
                            />
                        </n-form-item>

                        <n-form-item label="Harga satuan" path="price" v-bind="serverError('price')">
                            <n-input
                                :value="priceDisplay"
                                placeholder="0"
                                inputmode="numeric"
                                @update:value="onPriceInput"
                            >
                                <template #prefix>
                                    <span class="text-xs font-semibold text-slate-400">Rp</span>
                                </template>
                            </n-input>
                        </n-form-item>

                        <n-form-item
                            label="Tanggal pelabelan"
                            path="labeling_date"
                            v-bind="serverError('labeling_date')"
                        >
                            <n-date-picker
                                v-model:formatted-value="form.labeling_date"
                                value-format="yyyy-MM-dd"
                                format="dd MMM yyyy"
                                type="date"
                                class="w-full"
                                placeholder="Pilih tanggal"
                            />
                        </n-form-item>
                    </div>
                </FormSection>
            </n-form>

            <!-- 2. GAMBAR -->
            <FormSection
                :step="2"
                title="Gambar Barang"
                description="Opsional. Format JPG/PNG, foto yang jelas memudahkan pencarian barang."
            >
                <input
                    ref="fileInput"
                    type="file"
                    accept="image/*"
                    class="hidden"
                    @change="onFilePicked"
                />

                <div
                    v-if="shownImage"
                    class="flex flex-col gap-4 sm:flex-row sm:items-center"
                >
                    <div
                        class="flex h-40 w-full shrink-0 items-center justify-center overflow-hidden rounded border border-slate-200 bg-slate-50 sm:w-40"
                    >
                        <img :src="shownImage" alt="Pratinjau gambar" class="max-h-full max-w-full object-contain" />
                    </div>
                    <div class="flex min-w-0 flex-col gap-2">
                        <p class="truncate text-sm font-medium text-slate-800">
                            {{ imageFile ? imageFile.name : "Gambar saat ini" }}
                        </p>
                        <p class="text-xs text-slate-500">
                            <template v-if="imageFile">
                                {{ formatSize(imageFile.size) }} · gambar baru, akan disimpan saat klik Simpan.
                            </template>
                            <template v-else>
                                Pilih gambar baru jika ingin mengganti.
                            </template>
                        </p>
                        <div class="flex gap-2">
                            <n-button size="small" @click="pickFile">
                                <template #icon>
                                    <n-icon :component="ArrowSync20Regular" />
                                </template>
                                Ganti gambar
                            </n-button>
                            <n-button
                                v-if="imageFile"
                                size="small"
                                type="error"
                                ghost
                                @click="clearImage"
                            >
                                {{ initialImage ? "Batalkan" : "Hapus" }}
                            </n-button>
                        </div>
                    </div>
                </div>

                <button
                    v-else
                    type="button"
                    class="flex w-full flex-col items-center justify-center gap-2 rounded border border-dashed px-4 py-8 text-center transition-colors duration-150"
                    :class="
                        dragOver
                            ? 'border-[#2A126C] bg-[#2A126C]/[0.04]'
                            : 'border-slate-300 bg-slate-50 hover:border-[#2A126C]/50'
                    "
                    @click="pickFile"
                    @dragover.prevent="dragOver = true"
                    @dragleave.prevent="dragOver = false"
                    @drop.prevent="onDrop"
                >
                    <n-icon :component="ImageAdd24Regular" size="32" class="text-slate-400" />
                    <span class="text-sm font-semibold text-slate-700">
                        Klik untuk pilih gambar
                    </span>
                    <span class="text-xs text-slate-400">atau tarik & lepas file ke sini</span>
                </button>
            </FormSection>

            <!-- 3. STOK -->
            <FormSection
                :step="3"
                title="Stok per Gudang"
                :description="
                    mode === 'edit'
                        ? 'Sesuaikan jumlah stok. Perubahan akan direkonsiliasi otomatis.'
                        : 'Tentukan di gudang mana barang disimpan dan berapa jumlahnya.'
                "
            >
                <template #aside>
                    <span
                        class="shrink-0 rounded border border-[#2A126C]/15 bg-[#2A126C]/[0.05] px-2 py-0.5 text-[11px] font-semibold tabular-nums text-[#2A126C]"
                    >
                        Total {{ totalQuantity }} {{ unitLabel || "unit" }}
                    </span>
                </template>

                <div class="flex flex-col gap-3">
                    <div
                        v-for="(row, i) in items"
                        :key="row.key"
                        class="rounded border p-3"
                        :class="
                            rowError(row, i) ? 'border-rose-300 bg-rose-50/40' : 'border-slate-200'
                        "
                    >
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400">
                                Lokasi {{ i + 1 }}
                            </span>
                            <button
                                v-if="!row.locked"
                                type="button"
                                class="inline-flex items-center gap-1 rounded px-1.5 py-0.5 text-xs font-medium text-slate-400 hover:bg-rose-50 hover:text-rose-600 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-slate-400"
                                :disabled="editableCount <= 1"
                                :title="editableCount <= 1 ? 'Minimal satu lokasi' : 'Hapus lokasi'"
                                @click="removeRow(i)"
                            >
                                <n-icon :component="Delete16Regular" />
                                Hapus
                            </button>
                        </div>

                        <div v-if="row.locked" class="flex items-center justify-between gap-3 text-sm">
                            <span class="text-slate-700">{{ row.warehouse_name }}</span>
                            <span class="text-xs text-slate-500">
                                {{ conditionLabel(row.status) }} ·
                                <b class="tabular-nums text-slate-800">{{ row.quantity }}</b>
                                {{ unitLabel }} (tidak bisa diubah di sini)
                            </span>
                        </div>

                        <div v-else class="grid grid-cols-1 gap-3 sm:grid-cols-12">
                            <label class="flex flex-col gap-1 sm:col-span-5">
                                <span class="text-xs font-medium text-slate-600">Gudang</span>
                                <n-select
                                    :value="row.warehouse_id"
                                    :options="warehouseOptions"
                                    placeholder="Pilih gudang"
                                    filterable
                                    :status="submitted && !row.warehouse_id ? 'error' : undefined"
                                    @update:value="(v, opt: any) => {
                                        row.warehouse_id = v;
                                        row.warehouse_name = opt?.label ?? null;
                                    }"
                                />
                            </label>

                            <div class="flex flex-col gap-1 sm:col-span-4">
                                <span class="text-xs font-medium text-slate-600">Kondisi</span>
                                <div
                                    class="grid h-[34px] grid-cols-2 overflow-hidden rounded border border-slate-300"
                                    role="radiogroup"
                                    :aria-label="`Kondisi lokasi ${i + 1}`"
                                >
                                    <button
                                        v-for="c in CONDITIONS"
                                        :key="c.value"
                                        type="button"
                                        role="radio"
                                        :aria-checked="row.status === c.value"
                                        class="text-xs font-semibold transition-colors duration-150 first:border-r first:border-slate-300"
                                        :class="row.status === c.value ? c.active : 'bg-white text-slate-500 hover:bg-slate-50'"
                                        @click="row.status = c.value"
                                    >
                                        {{ c.label }}
                                    </button>
                                </div>
                            </div>

                            <label class="flex flex-col gap-1 sm:col-span-3">
                                <span class="text-xs font-medium text-slate-600">Jumlah</span>
                                <n-input-number
                                    v-model:value="row.quantity"
                                    :min="0"
                                    placeholder="0"
                                    :show-button="false"
                                    :status="submitted && row.quantity == null ? 'error' : undefined"
                                >
                                    <template #suffix>
                                        <span class="text-xs text-slate-400">{{ unitLabel || "unit" }}</span>
                                    </template>
                                </n-input-number>
                            </label>
                        </div>

                        <p v-if="rowError(row, i)" class="mt-2 text-xs text-rose-600">
                            {{ rowError(row, i) }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="flex w-full items-center justify-center gap-1.5 rounded border border-dashed border-slate-300 py-2.5 text-sm font-semibold text-slate-600 transition-colors duration-150 hover:border-[#2A126C] hover:bg-[#2A126C]/[0.03] hover:text-[#2A126C]"
                        @click="addRow"
                    >
                        <n-icon :component="Add20Regular" />
                        Tambah lokasi gudang
                    </button>
                </div>
            </FormSection>
        </div>

        <!-- ================= KANAN: RINGKASAN ================= -->
        <aside class="hidden lg:block">
            <div class="sticky top-20 flex flex-col gap-4">
                <section class="rounded border border-slate-200 bg-white">
                    <header class="border-b border-slate-100 px-5 py-4">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">
                            Ringkasan
                        </p>
                        <p class="mt-1 truncate text-base font-bold text-slate-900">
                            {{ form.name || "Barang baru" }}
                        </p>
                        <p class="truncate font-mono text-[11px] text-slate-400">
                            {{ form.code || "KODE_BARANG" }}
                        </p>
                    </header>

                    <!-- Kelengkapan -->
                    <div class="border-b border-slate-100 px-5 py-4">
                        <div class="mb-2 flex items-center justify-between text-xs">
                            <span class="font-semibold text-slate-600">Kelengkapan data wajib</span>
                            <span class="tabular-nums text-slate-500">
                                {{ doneCount }}/{{ checklist.length }}
                            </span>
                        </div>
                        <div class="h-1.5 overflow-hidden rounded-full bg-slate-100">
                            <div
                                class="h-full rounded-full transition-all duration-300"
                                :class="doneCount === checklist.length ? 'bg-emerald-500' : 'bg-[#2A126C]'"
                                :style="{ width: `${(doneCount / checklist.length) * 100}%` }"
                            ></div>
                        </div>
                        <ul class="mt-3 grid grid-cols-2 gap-x-3 gap-y-1.5">
                            <li
                                v-for="c in checklist"
                                :key="c.label"
                                class="flex items-center gap-1.5 text-xs"
                                :class="c.done ? 'text-slate-700' : 'text-slate-400'"
                            >
                                <n-icon
                                    :component="c.done ? CheckmarkCircle16Filled : Circle16Regular"
                                    :class="c.done ? 'text-emerald-500' : 'text-slate-300'"
                                />
                                {{ c.label }}
                            </li>
                        </ul>
                    </div>

                    <dl class="divide-y divide-slate-100 px-5 text-sm">
                        <div v-for="s in summary" :key="s.label" class="flex justify-between gap-3 py-2.5">
                            <dt class="text-xs text-slate-500">{{ s.label }}</dt>
                            <dd class="truncate text-right font-medium tabular-nums text-slate-900">
                                {{ s.value }}
                            </dd>
                        </div>
                    </dl>

                    <div class="flex flex-col gap-2 border-t border-slate-100 p-5">
                        <n-button type="primary" size="large" block :loading="processing" @click="submit">
                            <template #icon>
                                <n-icon :component="Save20Regular" />
                            </template>
                            {{ submitLabel }}
                        </n-button>
                        <n-button block :disabled="processing" @click="$emit('cancel')">Batal</n-button>
                    </div>
                </section>
            </div>
        </aside>

        <!-- ================= ACTION BAR (mobile/tablet) ================= -->
        <div
            class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 px-4 py-3 backdrop-blur lg:hidden"
            style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom))"
        >
            <div class="mx-auto flex max-w-3xl items-center gap-3">
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">
                        Kelengkapan
                    </p>
                    <p class="text-sm font-bold tabular-nums text-slate-900">
                        {{ doneCount }}/{{ checklist.length }} data wajib
                    </p>
                </div>
                <n-button :disabled="processing" @click="$emit('cancel')">Batal</n-button>
                <n-button type="primary" :loading="processing" @click="submit">
                    {{ submitLabel }}
                </n-button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, reactive, ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import {
    NButton,
    NDatePicker,
    NForm,
    NFormItem,
    NIcon,
    NInput,
    NInputNumber,
    NSelect,
    useMessage,
    type FormInst,
    type FormRules,
} from "naive-ui";
import {
    Add20Regular,
    ArrowSync20Regular,
    CheckmarkCircle16Filled,
    Circle16Regular,
    Delete16Regular,
    ImageAdd24Regular,
    Save20Regular,
} from "@vicons/fluent";
import FormSection from "@/Components/FormSection.vue";
import type { ProductFormPayload } from "./product-form";

type Condition = "GOOD" | "DAMAGED";

const props = withDefaults(
    defineProps<{
        mode: "create" | "edit";
        units: Array<{ name: string; code: string }>;
        subTypes: Array<{ name: string; code: string }>;
        warehouses: Array<{ id: number; name: string }>;
        product?: Record<string, any> | null;
        quantities?: Array<{
            warehouse_id: number;
            warehouse_name: string;
            on_hand: number | string;
            condition: string | null;
        }>;
        processing?: boolean;
    }>(),
    { product: null, quantities: () => [], processing: false },
);

const emit = defineEmits<{
    (e: "submit", payload: ProductFormPayload): void;
    (e: "cancel"): void;
}>();

const message = useMessage();
const page = usePage();

/* ---------------- Opsi ---------------- */
const TYPE_OPTIONS = [
    {
        value: "DURABLE",
        label: "Tidak habis pakai",
        hint: "Alat/aset yang bisa dipinjam dan wajib dikembalikan. Contoh: tang, laptop.",
    },
    {
        value: "CONSUMABLE",
        label: "Habis pakai",
        hint: "Bahan yang terpakai dan tidak perlu dikembalikan. Contoh: kabel, konektor.",
    },
];

const CONDITIONS: Array<{ value: Condition; label: string; active: string }> = [
    { value: "GOOD", label: "Bagus", active: "bg-emerald-600 text-white" },
    { value: "DAMAGED", label: "Rusak", active: "bg-rose-600 text-white" },
];

const unitOptions = computed(() =>
    props.units.map((u) => ({ label: u.name, value: u.code })),
);
const subTypeOptions = computed(() =>
    props.subTypes.map((s) => ({ label: s.name, value: s.code })),
);
const warehouseOptions = computed(() =>
    props.warehouses.map((w) => ({ label: w.name, value: w.id })),
);

/* ---------------- State form ---------------- */
const p = props.product ?? {};
const today = new Date();
const pad = (n: number) => String(n).padStart(2, "0");
const todayStr = `${today.getFullYear()}-${pad(today.getMonth() + 1)}-${pad(today.getDate())}`;

const form = reactive({
    name: (p.name as string) ?? null,
    code: (p.code as string) ?? null,
    brand_name: (p.brand_name as string) ?? null,
    type: (p.type as string) ?? null,
    price: p.price != null && p.price !== "" ? Number(p.price) : (null as number | null),
    labeling_date: p.labeling_date
        ? String(p.labeling_date).slice(0, 10)
        : props.mode === "create"
          ? todayStr
          : null,
    sub_type_code: (p.sub_type_code as string) ?? null,
    unit_code: (p.unit_code as string) ?? null,
});

const rules: FormRules = {
    name: { required: true, message: "Nama barang wajib diisi", trigger: ["blur", "input"] },
    code: { required: true, message: "Kode barang wajib diisi", trigger: ["blur", "input"] },
    type: { required: true, key: "type", message: "Pilih tipe barang", trigger: ["change"] },
    sub_type_code: { required: true, message: "Pilih kategori", trigger: ["change", "blur"] },
    unit_code: { required: true, message: "Pilih satuan", trigger: ["change", "blur"] },
    price: {
        required: true,
        trigger: ["blur", "input"],
        validator: () =>
            form.price == null ? new Error("Harga wajib diisi (boleh 0)") : true,
    },
    labeling_date: {
        required: true,
        message: "Tanggal pelabelan wajib diisi",
        trigger: ["change", "blur"],
    },
};

const formRef = ref<FormInst | null>(null);

function selectType(value: string) {
    form.type = value;
    formRef.value?.validate(undefined, (rule) => rule?.key === "type").catch(() => {});
}

/* Error dari server (Laravel) ditampilkan di bawah field */
function serverError(field: string) {
    const msg = (page.props.errors as Record<string, string> | undefined)?.[field];
    return msg ? { validationStatus: "error" as const, feedback: msg } : {};
}

/* ---------------- Harga ---------------- */
const priceDisplay = computed(() =>
    form.price == null ? "" : new Intl.NumberFormat("id-ID").format(form.price),
);
function onPriceInput(v: string) {
    const digits = v.replace(/\D/g, "");
    form.price = digits ? Number(digits) : null;
}

/* ---------------- Gambar ---------------- */
const initialImage = (p.image_product as string) || null;
const imageFile = ref<File | null>(null);
const imagePreview = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
const dragOver = ref(false);

const shownImage = computed(() => imagePreview.value ?? initialImage);

function pickFile() {
    fileInput.value?.click();
}
function setFile(file?: File | null) {
    if (!file) return;
    if (!file.type.startsWith("image/")) {
        message.error("File harus berupa gambar.");
        return;
    }
    if (imagePreview.value) URL.revokeObjectURL(imagePreview.value);
    imageFile.value = file;
    imagePreview.value = URL.createObjectURL(file);
}
function onFilePicked(e: Event) {
    const input = e.target as HTMLInputElement;
    setFile(input.files?.[0]);
    input.value = "";
}
function onDrop(e: DragEvent) {
    dragOver.value = false;
    setFile(e.dataTransfer?.files?.[0]);
}
function clearImage() {
    if (imagePreview.value) URL.revokeObjectURL(imagePreview.value);
    imagePreview.value = null;
    imageFile.value = null;
}
onBeforeUnmount(() => {
    if (imagePreview.value) URL.revokeObjectURL(imagePreview.value);
});
function formatSize(bytes: number) {
    return bytes > 1024 * 1024
        ? `${(bytes / 1024 / 1024).toFixed(1)} MB`
        : `${Math.round(bytes / 1024)} KB`;
}

/* ---------------- Stok ---------------- */
type Row = {
    key: number;
    status: string | null;
    warehouse_id: number | null;
    warehouse_name: string | null;
    quantity: number | null;
    /** baris dengan kondisi selain GOOD/DAMAGED (mis. dipinjam) tidak bisa diubah */
    locked: boolean;
};

let keySeq = 0;
const newRow = (): Row => ({
    key: ++keySeq,
    status: "GOOD",
    warehouse_id: props.warehouses.length === 1 ? props.warehouses[0].id : null,
    warehouse_name: props.warehouses.length === 1 ? props.warehouses[0].name : null,
    quantity: null,
    locked: false,
});

const items = ref<Row[]>(
    props.quantities.length
        ? props.quantities.map((q) => ({
              key: ++keySeq,
              status: q.condition,
              warehouse_id: q.warehouse_id,
              warehouse_name: q.warehouse_name,
              quantity: Number(q.on_hand),
              locked: !!q.condition && !["GOOD", "DAMAGED"].includes(q.condition),
          }))
        : [newRow()],
);

const editableCount = computed(() => items.value.filter((r) => !r.locked).length);
const submitted = ref(false);

function addRow() {
    items.value.push(newRow());
}
function removeRow(i: number) {
    if (editableCount.value <= 1) return;
    items.value.splice(i, 1);
}

function rowError(row: Row, i: number): string | null {
    if (row.locked) return null;
    const dup = items.value.findIndex(
        (r, j) =>
            j !== i &&
            r.warehouse_id != null &&
            r.warehouse_id === row.warehouse_id &&
            r.status === row.status,
    );
    if (dup !== -1 && dup < i)
        return `Gudang & kondisi ini sudah ada di Lokasi ${dup + 1}. Gabungkan jumlahnya di sana.`;
    if (!submitted.value) return null;
    if (!row.warehouse_id) return "Pilih gudang terlebih dahulu.";
    if (row.quantity == null) return "Isi jumlah stok (boleh 0).";
    return null;
}

const totalQuantity = computed(() =>
    items.value.reduce((s, r) => s + (Number(r.quantity) || 0), 0),
);

/* ---------------- Ringkasan ---------------- */
const labelOf = (opts: { label: string; value: unknown }[], v: unknown) =>
    opts.find((o) => o.value === v)?.label ?? null;

const unitLabel = computed(() => labelOf(unitOptions.value, form.unit_code));

const checklist = computed(() => [
    { label: "Nama", done: !!form.name },
    { label: "Kode", done: !!form.code },
    { label: "Tipe", done: !!form.type },
    { label: "Kategori", done: !!form.sub_type_code },
    { label: "Satuan", done: !!form.unit_code },
    { label: "Harga", done: form.price != null },
    { label: "Tgl. pelabelan", done: !!form.labeling_date },
    {
        label: "Stok gudang",
        done: items.value.every((r) => r.locked || (r.warehouse_id && r.quantity != null)),
    },
]);
const doneCount = computed(() => checklist.value.filter((c) => c.done).length);

const summary = computed(() => [
    {
        label: "Tipe",
        value: TYPE_OPTIONS.find((t) => t.value === form.type)?.label ?? "-",
    },
    { label: "Kategori", value: labelOf(subTypeOptions.value, form.sub_type_code) ?? "-" },
    {
        label: "Harga satuan",
        value: form.price == null ? "-" : `Rp ${priceDisplay.value}`,
    },
    {
        label: "Total stok",
        value: `${totalQuantity.value} ${unitLabel.value ?? "unit"}`,
    },
    {
        label: "Lokasi gudang",
        value: new Set(items.value.map((r) => r.warehouse_id).filter(Boolean)).size,
    },
]);

const submitLabel = computed(() =>
    props.mode === "edit" ? "Simpan Perubahan" : "Simpan Barang",
);

/* ---------------- Submit ---------------- */
async function submit() {
    submitted.value = true;

    let formValid = true;
    try {
        await formRef.value?.validate();
    } catch {
        formValid = false;
    }

    const stockInvalid = items.value.some((r, i) => rowError(r, i));

    if (!formValid || stockInvalid) {
        message.error(
            !formValid
                ? "Masih ada data wajib yang belum diisi."
                : "Periksa kembali bagian stok per gudang.",
        );
        // gulir ke error pertama
        requestAnimationFrame(() => {
            document
                .querySelector(".n-form-item-feedback--error, .border-rose-300")
                ?.scrollIntoView({ behavior: "smooth", block: "center" });
        });
        return;
    }

    emit("submit", {
        name: form.name,
        code: form.code,
        brand_name: form.brand_name ?? "",
        type: form.type ?? "",
        price: form.price,
        labeling_date: form.labeling_date,
        warehouse_code: null,
        sub_type_code: form.sub_type_code,
        unit_code: form.unit_code,
        image_product: imageFile.value,
        quantities: items.value.map(({ status, warehouse_id, warehouse_name, quantity }) => ({
            status,
            warehouse_id,
            warehouse_name,
            quantity,
        })),
    });
}

function conditionLabel(c: string | null) {
    if (c === "GOOD") return "Bagus";
    if (c === "DAMAGED") return "Rusak";
    if (c === "OUT") return "Dipinjam";
    return c ?? "-";
}
</script>
