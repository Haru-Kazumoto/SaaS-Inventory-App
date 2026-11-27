<template>
    <div class="flex flex-col gap-5">
        <TitlePage
            eyebrow="Pengajuan Barang"
            title="Ajukan Barang Baru"
            subTitle="Isi kebutuhan barang. Pengajuan akan ditinjau oleh superadmin."
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

        <div class="grid grid-cols-1 gap-5 pb-24 lg:grid-cols-3 lg:pb-0">
            <!-- ================= FORM ================= -->
            <div class="flex flex-col gap-5 lg:col-span-2">
                <n-form
                    ref="formRef"
                    :model="form"
                    :rules="rules"
                    label-placement="top"
                    require-mark-placement="right-hanging"
                >
                    <FormSection
                        :step="1"
                        title="Detail Barang"
                        description="Barang apa yang dibutuhkan dan berapa banyak."
                    >
                        <div class="grid grid-cols-1 gap-x-4 sm:grid-cols-2">
                            <n-form-item
                                v-if="units.length > 1"
                                :label="t('unit')"
                                class="sm:col-span-2"
                                v-bind="serverError('unit_id')"
                            >
                                <n-select
                                    v-model:value="form.unit_id"
                                    :options="units.map((u) => ({ label: u.name, value: u.id }))"
                                />
                            </n-form-item>
                            <n-form-item
                                label="Nama barang"
                                path="item_name"
                                class="sm:col-span-2"
                                v-bind="serverError('item_name')"
                            >
                                <n-input
                                    :value="form.item_name"
                                    placeholder="cth: KABEL UTP CAT6"
                                    maxlength="150"
                                    @update:value="(v) => (form.item_name = v.toUpperCase())"
                                />
                            </n-form-item>

                            <n-form-item label="Jumlah" path="quantity" v-bind="serverError('quantity')">
                                <n-input-number
                                    v-model:value="form.quantity"
                                    :min="1"
                                    placeholder="0"
                                    class="w-full"
                                />
                            </n-form-item>

                            <n-form-item label="Satuan" path="uom" v-bind="serverError('uom')">
                                <n-auto-complete
                                    :value="form.uom ?? undefined"
                                    :options="unitSuggestions"
                                    placeholder="cth: PCS, ROLL, BOX"
                                    clearable
                                    @update:value="(v) => (form.uom = v ? v.toUpperCase() : null)"
                                />
                            </n-form-item>

                            <n-form-item
                                label="Alasan pengajuan"
                                path="reason"
                                class="sm:col-span-2"
                                v-bind="serverError('reason')"
                            >
                                <n-input
                                    v-model:value="form.reason"
                                    type="textarea"
                                    placeholder="Jelaskan untuk apa barang ini dibutuhkan, mis. praktik kelas XI TJKT."
                                    :autosize="{ minRows: 3, maxRows: 6 }"
                                    maxlength="500"
                                    show-count
                                />
                            </n-form-item>
                        </div>
                    </FormSection>
                </n-form>

                <FormSection
                    :step="2"
                    title="Referensi (opsional)"
                    description="Bantu penyetuju menemukan barang yang tepat."
                >
                    <div class="flex flex-col gap-5">
                        <label class="flex flex-col gap-1.5">
                            <span class="text-sm text-slate-700">Link toko online</span>
                            <n-input
                                v-model:value="form.reference_url"
                                placeholder="https://..."
                                clearable
                                :status="linkInvalid ? 'warning' : undefined"
                            >
                                <template #prefix>
                                    <n-icon :component="Link20Regular" class="text-slate-400" />
                                </template>
                            </n-input>
                            <span v-if="linkInvalid" class="text-[11px] text-amber-600">
                                Link sebaiknya diawali http:// atau https://
                            </span>
                        </label>

                        <div class="flex flex-col gap-1.5">
                            <span class="text-sm text-slate-700">Gambar barang</span>
                            <input
                                ref="fileInput"
                                type="file"
                                accept="image/*"
                                class="hidden"
                                @change="onFilePicked"
                            />

                            <div
                                v-if="imagePreview"
                                class="flex flex-col gap-4 rounded border border-slate-200 p-3 sm:flex-row sm:items-center"
                            >
                                <div
                                    class="flex h-32 w-full shrink-0 items-center justify-center overflow-hidden rounded border border-slate-200 bg-slate-50 sm:w-32"
                                >
                                    <img :src="imagePreview" alt="Pratinjau" class="max-h-full max-w-full object-contain" />
                                </div>
                                <div class="flex min-w-0 flex-col gap-2">
                                    <p class="truncate text-sm font-medium text-slate-800">
                                        {{ form.image?.name }}
                                    </p>
                                    <div class="flex gap-2">
                                        <n-button size="small" @click="pickFile">Ganti</n-button>
                                        <n-button size="small" type="error" ghost @click="clearImage">
                                            Hapus
                                        </n-button>
                                    </div>
                                </div>
                            </div>

                            <button
                                v-else
                                type="button"
                                class="flex w-full flex-col items-center justify-center gap-2 rounded border border-dashed px-4 py-7 text-center transition-colors duration-150"
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
                                <n-icon :component="ImageAdd24Regular" size="28" class="text-slate-400" />
                                <span class="text-sm font-semibold text-slate-700">Klik untuk pilih gambar</span>
                                <span class="text-xs text-slate-400">atau tarik & lepas file ke sini</span>
                            </button>
                        </div>
                    </div>
                </FormSection>
            </div>

            <!-- ================= RINGKASAN (desktop) ================= -->
            <aside class="hidden lg:block">
                <section class="sticky top-20 rounded border border-slate-200 bg-white">
                    <header class="border-b border-slate-100 px-5 py-4">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">
                            Ringkasan
                        </p>
                        <p class="mt-1 truncate text-base font-bold text-slate-900">
                            {{ form.item_name || "Barang baru" }}
                        </p>
                        <p class="text-sm tabular-nums text-slate-500">
                            {{ form.quantity ?? 0 }} {{ form.uom || "unit" }}
                        </p>
                    </header>

                    <div class="flex gap-2.5 border-b border-slate-100 px-5 py-4 text-xs leading-relaxed text-slate-600">
                        <n-icon :component="Info16Regular" size="16" class="mt-0.5 shrink-0 text-[#2A126C]" />
                        <p>
                            Pengajuan masuk rekap pengadaan tahun
                            <b class="text-slate-900">{{ currentYear }}</b>. Setelah diproses
                            superadmin, pengajuan <b class="text-slate-900">tidak bisa diubah</b>.
                        </p>
                    </div>

                    <div class="flex flex-col gap-2 p-5">
                        <n-button type="primary" size="large" block :loading="processing" @click="submit">
                            <template #icon>
                                <n-icon :component="Send20Regular" />
                            </template>
                            Kirim Pengajuan
                        </n-button>
                        <n-button block :disabled="processing" @click="goBack">Batal</n-button>
                    </div>
                </section>
            </aside>
        </div>

        <!-- ================= ACTION BAR (mobile/tablet) ================= -->
        <div
            class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 px-4 py-3 backdrop-blur lg:hidden"
            style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom))"
        >
            <div class="mx-auto flex max-w-3xl items-center gap-3">
                <p class="min-w-0 flex-1 text-[11px] leading-snug text-slate-500">
                    Tidak bisa diubah setelah diproses superadmin.
                </p>
                <n-button :disabled="processing" @click="goBack">Batal</n-button>
                <n-button type="primary" :loading="processing" @click="submit">Kirim</n-button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, reactive, ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import {
    NAutoComplete,
    NButton,
    NForm,
    NFormItem,
    NIcon,
    NInput,
    NInputNumber,
    NSelect,
    useMessage,
    useNotification,
    type FormInst,
    type FormRules,
} from "naive-ui";
import {
    ArrowLeft20Regular,
    ImageAdd24Regular,
    Info16Regular,
    Link20Regular,
    Send20Regular,
} from "@vicons/fluent";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TitlePage from "@/Components/TitlePage.vue";
import FormSection from "@/Components/FormSection.vue";
import { useTerms } from "@/Composables/useTerms";

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps<{ units: Array<{ id: number; name: string }> }>();
const { t } = useTerms();

const page = usePage();
const message = useMessage();
const notification = useNotification();
const currentYear = new Date().getFullYear();

const form = reactive({
    unit_id: (props.units[0]?.id ?? null) as number | null,
    item_name: null as string | null,
    quantity: null as number | null,
    uom: null as string | null,
    reference_url: null as string | null,
    reason: null as string | null,
    image: null as File | null,
});

const rules: FormRules = {
    item_name: { required: true, message: "Nama barang wajib diisi", trigger: ["blur", "input"] },
    quantity: {
        required: true,
        type: "number",
        trigger: ["blur", "change"],
        validator: () => (form.quantity && form.quantity > 0 ? true : new Error("Jumlah minimal 1")),
    },
    uom: { required: true, message: "Satuan wajib diisi", trigger: ["blur", "input"] },
    reason: {
        required: true,
        message: "Alasan pengajuan wajib diisi",
        trigger: ["blur", "input"],
    },
};

const UNITS = ["PCS", "UNIT", "BUAH", "BOX", "PACK", "ROLL", "METER", "SET", "LUSIN", "RIM"];
const unitSuggestions = computed(() => {
    const q = (form.uom || "").toUpperCase();
    return UNITS.filter((u) => u.includes(q)).map((u) => ({ label: u, value: u }));
});

const linkInvalid = computed(
    () => !!form.reference_url && !/^https?:\/\//i.test(form.reference_url.trim()),
);

function serverError(field: string) {
    const msg = (page.props.errors as Record<string, string> | undefined)?.[field];
    return msg ? { validationStatus: "error" as const, feedback: msg } : {};
}

/* ---------------- Gambar ---------------- */
const fileInput = ref<HTMLInputElement | null>(null);
const imagePreview = ref<string | null>(null);
const dragOver = ref(false);

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
    form.image = file;
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
    form.image = null;
}
onBeforeUnmount(() => {
    if (imagePreview.value) URL.revokeObjectURL(imagePreview.value);
});

/* ---------------- Submit ---------------- */
const formRef = ref<FormInst | null>(null);
const processing = ref(false);

function goBack() {
    router.visit(route("procurement.index"));
}

async function submit() {
    try {
        await formRef.value?.validate();
    } catch {
        message.error("Masih ada data wajib yang belum diisi.");
        return;
    }

    router.post(
        route("procurement.store"),
        {
            item_name: form.item_name,
            quantity: form.quantity,
            unit_id: form.unit_id,
            uom: form.uom,
            reference_url: form.reference_url?.trim() || null,
            reason: form.reason,
            image: form.image,
        } as any,
        {
            forceFormData: true,
            onStart: () => (processing.value = true),
            onFinish: () => (processing.value = false),
            onSuccess: () =>
                notification.success({
                    title: "Pengajuan berhasil dikirim",
                    duration: 3000,
                }),
            onError: () =>
                notification.error({
                    title: "Gagal mengirim pengajuan",
                    content: "Periksa kembali data yang diisi.",
                    duration: 5000,
                    closable: true,
                }),
        },
    );
}
</script>
