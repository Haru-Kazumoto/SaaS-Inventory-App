<template>
    <div class="flex flex-col gap-3">
        <TitlePage
            title="Peminjaman Baru"
            subTitle="Pembuatan peminjaman baru"
        />
        <Back route="borrowing.index" />

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-3">
            <!-- KIRI (3 kolom) -->
            <div class="lg:col-span-3 space-y-3">
                <!-- Informasi Peminjam -->
                <n-card
                    :bordered="false"
                    class="rounded-2xl border border-gray-300 shadow"
                >
                    <div class="flex flex-col gap-5">
                        <div class="flex flex-col">
                            <div class="flex items-center gap-2">
                                <n-icon
                                    color="#003092"
                                    :component="Person16Regular"
                                    size="22"
                                />
                                <span class="text-lg font-semibold">
                                    Informasi Peminjam
                                </span>
                            </div>
                            <span class="text-sm text-gray-500">
                                Masukkan data diri peminjam barang
                            </span>
                        </div>

                        <n-form size="large" :model="borrower">
                            <n-grid
                                :cols="2"
                                x-gap="12"
                                y-gap="0"
                                responsive="screen"
                            >
                                <n-gi>
                                    <n-form-item label="Nama Lengkap">
                                        <n-input
                                            v-model:value="
                                                borrower.borrower_name
                                            "
                                            placeholder="Masukkan nama lengkap"
                                            @input="
                                                (value) =>
                                                    (borrower.borrower_name =
                                                        value.toUpperCase())
                                            "
                                        />
                                    </n-form-item>
                                </n-gi>

                                <n-gi>
                                    <n-form-item label="Nomor Telepon">
                                        <n-input
                                            v-model:value="
                                                borrower.borrower_phone
                                            "
                                            placeholder="08xxxxxxxxxx"
                                            @input="
                                                (value) =>
                                                    (borrower.borrower_phone =
                                                        value.replace(
                                                            /\D/g,
                                                            '',
                                                        ))
                                            "
                                        />
                                    </n-form-item>
                                </n-gi>

                                <n-gi>
                                    <n-form-item label="Ruangan">
                                        <n-input
                                            v-model:value="
                                                borrower.borrower_room
                                            "
                                            placeholder="Ruangan A"
                                            @input="
                                                (value) =>
                                                    (borrower.borrower_room =
                                                        value.toUpperCase())
                                            "
                                        />
                                    </n-form-item>
                                </n-gi>

                                <n-gi>
                                    <n-form-item label="Pengawas Ruangan">
                                        <n-input
                                            v-model:value="
                                                borrower.borrower_supervisor
                                            "
                                            placeholder="Masukan pengawas ruangan"
                                            @input="
                                                (value) =>
                                                    (borrower.borrower_supervisor =
                                                        value.toUpperCase())
                                            "
                                        />
                                    </n-form-item>
                                </n-gi>
                            </n-grid>

                            <n-form-item label="Deskripsi">
                                <n-input
                                    type="textarea"
                                    v-model:value="borrower.description"
                                    placeholder="Informasi deskripsi"
                                />
                            </n-form-item>
                        </n-form>
                    </div>
                </n-card>

                <!-- Daftar Barang -->
                <n-spin :show="retrieveLoading">
                    <template #description>Mengambil data stok</template>
                    <n-card
                        :bordered="false"
                        class="rounded-2xl border border-gray-300 shadow"
                    >
                        <div class="flex flex-col gap-5">
                            <div class="flex flex-col">
                                <div class="flex items-center gap-2">
                                    <n-icon
                                        color="#003092"
                                        :component="Cube16Regular"
                                        size="22"
                                    />
                                    <span class="text-lg font-semibold">
                                        Daftar Barang
                                    </span>
                                </div>
                                <span class="text-sm text-gray-500">
                                    Tambahkan barang yang ingin dipinjam
                                </span>
                            </div>

                            <n-alert
                                v-if="
                                    productWarehouseInformation.available_quantity !== null
                                "
                                type="info"
                                title="Informasi stok"
                            >
                                {{ productWarehouseInformation.warehouse_name }}
                                SIAP PAKAI
                                {{
                                    productWarehouseInformation.available_quantity
                                }}
                                {{ productWarehouseInformation.unit }}
                            </n-alert>

                            <n-form size="large" :model="itemForm">
                                <n-grid
                                    :cols="2"
                                    x-gap="12"
                                    y-gap="0"
                                    responsive="screen"
                                >
                                    <n-gi>
                                        <n-form-item
                                            label="Nama Barang"
                                            :require-mark-placement="'right-hanging'"
                                            required
                                        >
                                            <n-select
                                                :options="productOptions"
                                                v-model:value="
                                                    itemForm.product_id
                                                "
                                                filterable
                                                class="no-focus-select"
                                                placeholder="Contoh: Laptop"
                                                @update:value="
                                                    (val, option: any) =>
                                                        updatedProductChoosen(
                                                            val as number,
                                                            option?.label,
                                                        )
                                                "
                                            />
                                        </n-form-item>
                                    </n-gi>

                                    <n-gi>
                                        <n-form-item
                                            label="Gudang"
                                            :require-mark-placement="'right-hanging'"
                                            required
                                        >
                                            <n-select
                                                :options="warehouseOptions"
                                                v-model:value="
                                                    itemForm.warehouse_id
                                                "
                                                class="w-full"
                                                @update:value="
                                                    (val, option: any) =>
                                                        updatedWarehouseChoosen(
                                                            option.label,
                                                            option.good_quantity,
                                                            option.product_unit,
                                                        )
                                                "
                                            />
                                        </n-form-item>
                                    </n-gi>
                                    <n-gi>
                                        <n-form-item
                                            label="Jumlah"
                                            :require-mark-placement="'right-hanging'"
                                            required
                                        >
                                            <n-input-number
                                                v-model:value="
                                                    itemForm.quantity
                                                "
                                                placeholder="12"
                                                :min="1"
                                                class="w-full"
                                            />
                                        </n-form-item>
                                    </n-gi>
                                </n-grid>
                            </n-form>

                            <n-button
                                type="info"
                                size="large"
                                class="w-full"
                                :disabled="!canAddItem"
                                @click="addItem"
                            >
                                <template #icon>
                                    <n-icon :component="Add24Regular" />
                                </template>
                                Tambahkan Barang
                            </n-button>
                        </div>
                    </n-card>
                </n-spin>
            </div>

            <!-- KANAN (2 kolom) -->
            <div class="lg:col-span-2 space-y-3">
                <!-- Ringkasan -->
                <n-card
                    :bordered="false"
                    class="rounded-2xl border border-gray-300 shadow"
                    content-style="padding: 16px 16px;"
                >
                    <template #header>
                        <div class="flex items-center gap-2">
                            <n-icon
                                color="#003092"
                                :component="DocumentBulletList24Regular"
                                size="22"
                            />
                            <span class="text-lg font-semibold">
                                Ringkasan Peminjam
                            </span>
                        </div>
                    </template>

                    <n-descriptions label-placement="top" :column="2">
                        <n-descriptions-item label="Nama">
                            {{ borrower.borrower_name || "-" }}
                        </n-descriptions-item>

                        <n-descriptions-item label="Nomor Telepon">
                            {{ formattedPhone }}
                        </n-descriptions-item>

                        <n-descriptions-item label="Ruangan">
                            {{ borrower.borrower_room || "-" }}
                        </n-descriptions-item>

                        <n-descriptions-item label="Pengawas Ruangan">
                            {{ borrower.borrower_supervisor || "-" }}
                        </n-descriptions-item>

                        <n-descriptions-item label="Deskripsi">
                            {{ borrower.description || "-" }}
                        </n-descriptions-item>
                    </n-descriptions>
                </n-card>

                <!-- Barang yang Dipinjam -->
                <n-card
                    :bordered="false"
                    class="rounded-2xl border border-gray-300 shadow"
                >
                    <template #header>
                        <div class="flex items-center justify-between w-full">
                            <span class="text-lg font-semibold">
                                Barang yang Dipinjam
                            </span>
                            <n-tag type="info" round size="small">
                                {{ items.length }} item
                            </n-tag>
                        </div>
                    </template>

                    <div
                        v-if="items.length === 0"
                        class="text-center text-gray-400 py-8"
                    >
                        Belum ada barang yang ditambahkan
                    </div>

                    <div v-else class="space-y-2">
                        <div
                            v-for="(it, idx) in items"
                            :key="it.index"
                            class="flex items-start justify-between p-3 rounded-xl border border-gray-200"
                        >
                            <div class="flex-1 min-w-0">
                                <div class="font-medium truncate">
                                    {{ it.product_name }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    Qty: {{ it.quantity }}
                                    <span v-if="it.unit">
                                        {{ it.unit }}
                                    </span>
                                </div>
                            </div>

                            <n-button
                                text
                                type="error"
                                @click="removeItem(idx)"
                            >
                                Hapus
                            </n-button>
                        </div>
                    </div>

                    <div class="mt-4 space-y-2">
                        <n-button
                            type="primary"
                            block
                            size="large"
                            @click="openPreview"
                        >
                            Simpan Peminjaman
                        </n-button>

                        <n-button
                            block
                            size="large"
                            :disabled="isSubmitting"
                            @click="resetAll"
                        >
                            Reset Form
                        </n-button>
                    </div>
                </n-card>
            </div>
        </div>

        <!-- MODAL PREVIEW PEMINJAMAN -->
        <n-modal
            v-model:show="showPreview"
            preset="card"
            :mask-closable="false"
            class="max-w-3xl w-full"
        >
            <template #header>
                <div class="flex items-center gap-2">
                    <span class="text-base font-semibold text-slate-800">
                        Konfirmasi Peminjaman
                    </span>
                </div>
            </template>

            <div class="space-y-4">
                <!-- Ringkasan Peminjam -->
                <div>
                    <h3 class="text-sm font-semibold text-slate-700 mb-2">
                        Informasi Peminjam
                    </h3>
                    <n-descriptions
                        label-placement="top"
                        :column="2"
                        size="small"
                    >
                        <n-descriptions-item label="Nama Lengkap">
                            {{ borrower.borrower_name || "-" }}
                        </n-descriptions-item>
                        <n-descriptions-item label="Nomor Telepon">
                            {{ formattedPhone }}
                        </n-descriptions-item>
                        <n-descriptions-item label="Ruangan">
                            {{ borrower.borrower_room || "-" }}
                        </n-descriptions-item>
                        <n-descriptions-item label="Pengawas Ruangan">
                            {{ borrower.borrower_supervisor || "-" }}
                        </n-descriptions-item>
                        <n-descriptions-item label="Deskripsi">
                            {{ borrower.description || "-" }}
                        </n-descriptions-item>
                        <n-descriptions-item label="Total Barang">
                            {{ items.length }} item
                        </n-descriptions-item>
                    </n-descriptions>
                </div>

                <!-- Ringkasan Barang -->
                <div>
                    <h3 class="text-sm font-semibold text-slate-700 mb-2">
                        Barang yang Dipinjam
                    </h3>

                    <div
                        v-if="items.length === 0"
                        class="text-xs text-slate-400 py-4 text-center border rounded-lg"
                    >
                        Belum ada barang yang ditambahkan.
                    </div>

                    <div v-else class="space-y-2 max-h-64 overflow-y-auto pr-1">
                        <div
                            v-for="it in items"
                            :key="it.index"
                            class="flex items-start justify-between p-2.5 rounded-lg border border-slate-200 bg-slate-50/60"
                        >
                            <div class="flex-1 min-w-0">
                                <div
                                    class="text-sm font-medium text-slate-800 truncate"
                                >
                                    {{ it.product_name }}
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    Qty: {{ it.quantity }}
                                    <span v-if="it.unit"> {{ it.unit }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <n-button
                        size="large"
                        @click="showPreview = false"
                        :disabled="isSubmitting"
                    >
                        Kembali
                    </n-button>
                    <n-button
                        type="primary"
                        size="large"
                        :loading="isSubmitting"
                        @click="confirmSubmit"
                    >
                        Konfirmasi & Simpan
                    </n-button>
                </div>
            </template>
        </n-modal>
    </div>
</template>

<style scoped>

:deep(.n-base-selection) {
    box-shadow: none !important;
}

:deep(.n-base-selection:focus-within) {
    box-shadow: none !important;
    outline: none !important;
}

:deep(.n-base-selection-input__content) {
    outline: none !important;
    box-shadow: none !important;
    border: none !important;
}

:deep(input) {
    outline: none !important;
    box-shadow: none !important;
}
</style>

<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TitlePage from "@/Components/TitlePage.vue";
import Back from "@/Components/Back.vue";
import {
    NCard,
    NInput,
    NInputNumber,
    NButton,
    NIcon,
    NGrid,
    NGi,
    NForm,
    NFormItem,
    NSpin,
    NTag,
    NSelect,
    NDescriptions,
    NDescriptionsItem,
    NAlert,
    useMessage,
    NModal,
    useNotification,
} from "naive-ui";
import {
    Person16Regular,
    Cube16Regular,
    Add24Regular,
    DocumentBulletList24Regular,
} from "@vicons/fluent";
import { PropType, reactive, ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";

defineOptions({ layout: AuthenticatedLayout });

const message = useMessage();
const notification = useNotification();

interface Warehouse {
    id: number;
    name: string;
    code: string;
    good_quantity: number;
    product_unit: string;
}

interface Product {
    id: number;
    name: string;
    code: string;
}

interface ProductWarehouseInformation {
    warehouse_name: string | null;
    available_quantity: number | null;
    unit: string | null;
}

type ItemRow = {
    index: number;
    product_id: number | null;
    product_name: string | null;
    quantity: number | null;
    warehouse_id: number | null;
    unit: string | null;
};

const props = defineProps({
    products: { type: Array as PropType<Product[]>, required: false },
});

const warehouses = ref<Warehouse[]>([]);
const showPreview = ref(false);
const borrower = reactive({
    borrower_name: "",
    borrower_phone: "",
    borrower_room: "",
    borrower_supervisor: "",
    description: "",
});

const itemForm = reactive<ItemRow>({
    product_id: null,
    product_name: null,
    quantity: null,
    warehouse_id: null,
    unit: null,
});

const productWarehouseInformation = ref<ProductWarehouseInformation>({
    warehouse_name: null,
    available_quantity: null,
    unit: null,
});

const retrieveLoading = ref(false);
const isSubmitting = ref(false);

const items = ref<ItemRow[]>([]);

async function updatedProductChoosen(
    productId: number | null,
    productName?: string,
) {
    if (!productId) return;

    retrieveLoading.value = true;
    itemForm.product_id = productId;
    itemForm.product_name = productName ?? null;
    itemForm.warehouse_id = null;
    itemForm.unit = null;

    try {
        const response = await axios.get(
            route("warehouse.index-json", productId),
        );
        warehouses.value = response.data;
    } finally {
        retrieveLoading.value = false;
    }
}

const selectedWarehouse = computed(() => {
    if (!itemForm.warehouse_id) return null;
    return warehouses.value.find((w) => w.id === itemForm.warehouse_id) ?? null;
});

function updatedWarehouseChoosen(
    warehouse_name: string,
    available_quantity: number,
    unit: string,
) {
    productWarehouseInformation.value.warehouse_name = warehouse_name;
    productWarehouseInformation.value.available_quantity = available_quantity;
    productWarehouseInformation.value.unit = unit;

    // simpan unit juga ke itemForm supaya ikut ke dalam items
    itemForm.unit = unit;
}

function openPreview() {
    // kalau mau, bisa cek canSubmit di sini
    if (!canSubmit.value) {
        message.error("Lengkapi data peminjam dan tambahkan minimal 1 barang.");
        return;
    }
    showPreview.value = true;
}

function confirmSubmit() {
    handleSubmit();
}

function addItem() {
    if (
        !itemForm.product_id ||
        !itemForm.product_name ||
        !itemForm.quantity ||
        !itemForm.warehouse_id
    ) {
        return;
    }

    const wh = selectedWarehouse.value;
    if (wh && Number(itemForm.quantity) > Number(wh.good_quantity)) {
        message.warning(
            `Qty ${itemForm.quantity} melebihi stok siap pakai (${wh.good_quantity}).`,
        );
        return;
    }

    items.value.push({ ...itemForm });

    message.success("Barang ditambahkan");

    resetItemForm();
    resetWarehouseInformation();
}

function resetWarehouseInformation() {
    productWarehouseInformation.value.warehouse_name = null;
    productWarehouseInformation.value.available_quantity = null;
    productWarehouseInformation.value.unit = null;
}

function resetItemForm() {
    itemForm.product_id = null;
    itemForm.product_name = null;
    itemForm.quantity = null;
    itemForm.warehouse_id = null;
    itemForm.unit = null;
}

function removeItem(idx: number) {
    items.value.splice(idx, 1);
}

function resetAll() {
    borrower.borrower_name = "";
    borrower.borrower_phone = "";
    borrower.borrower_room = "";
    borrower.borrower_supervisor = "";
    borrower.description = "";

    items.value = [];
    resetItemForm();

    productWarehouseInformation.value.warehouse_name = null;
    productWarehouseInformation.value.available_quantity = null;
    productWarehouseInformation.value.unit = null;
}

/**
 * handleSubmit:
 * mengirim data peminjam + daftar barang ke route borrowing.store
 */
function handleSubmit() {
    // if (!canSubmit.value) return;

    const payload = {
        ...borrower,
        lines: items.value,
    };

    isSubmitting.value = true;

    router.post(route("borrowing.store"), payload, {
        onSuccess: (page) =>
            notification.success({
                title: page.props.flash.success,
                duration: 2000,
                closable: false,
            }),
        onError: (errors) => {
            const messages = Object.values(errors);
            notification.error({
                title: "Peminjaman gagal",
                content: messages.join(" ") || "Terjadi kesalahan",
                duration: 4000,
                closable: false,
            });
        },
        onFinish: (page) => {
            isSubmitting.value = false;
        },
    });
}

function formatDate(ts: number | null) {
    if (!ts) return "-";
    const d = new Date(ts);
    return d.toLocaleDateString();
}

const formattedPhone = computed(() => {
    if (!borrower.borrower_phone) return "-";
    return borrower.borrower_phone
        .replace(/\D/g, "")
        .replace(/(.{4})/g, "$1 ")
        .trim();
});

const productOptions = computed(() =>
    (props.products ?? []).map((data) => ({
        label: `${data.name} (${data.code})`,
        value: data.id
    })),
);

const warehouseOptions = computed(() =>
    (warehouses.value ?? []).map((data) => ({
        label: data.name,
        value: data.id,
        good_quantity: data.good_quantity,
        product_unit: data.product_unit,
        disabled: Number(data.good_quantity) <= 0,
    })),
);

/**
 * Tombol Tambahkan Barang enabled hanya jika form item lengkap:
 * - product_id
 * - product_name
 * - quantity
 * - warehouse_id
 */
const canAddItem = computed(() => {
    if (
        !itemForm.product_id ||
        !itemForm.product_name ||
        !itemForm.quantity ||
        !itemForm.warehouse_id
    ) {
        return false;
    }

    const wh = selectedWarehouse.value;
    if (!wh) return false;

    // quantity tidak boleh melebihi stok siap pakai (kondisi GOOD)
    return Number(itemForm.quantity) <= Number(wh.good_quantity);
});

/**
 * Tombol Simpan Peminjaman enabled hanya kalau:
 * - Nama & nomor telepon terisi
 * - Minimal ada 1 item
 */
const canSubmit = computed(() => {
    const hasBorrower =
        borrower.borrower_name.trim() !== "" &&
        borrower.borrower_phone.trim() !== "";
    const hasItems = items.value.length > 0;
    return hasBorrower && hasItems;
});
</script>
