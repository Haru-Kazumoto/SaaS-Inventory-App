<template>
    <CardModal
        v-model:show="show"
        title="Restock Barang ke Gudang"
        size="md"
        @after-leave="onAfterLeave"
    >
        <div class="space-y-4">
            <n-alert
                v-if="form.warehouse_id"
                type="info"
                :title="`Informasi quantity gudang ${form.warehouse_name}`"
            >
                Jumlah saat ini {{ form.current_quantity }} {{ unitName }}
            </n-alert>

            <n-form ref="formRef" :model="form" size="large">
                <div class="grid grid-cols-2 gap-2">
                    <n-form-item label="Penyimpanan" path="warehouse_id">
                        <n-select
                            :options="warehouseOptions"
                            v-model:value="form.warehouse_id"
                            clearable
                            placeholder="Pilih gudang penyimpanan"
                            @update:value="onWarehouseChange"
                        />
                    </n-form-item>

                    <n-form-item label="Quantity" path="quantity">
                        <n-input-number
                            class="w-full"
                            v-model:value="form.quantity"
                            placeholder="Masukan quantity restock"
                        />
                    </n-form-item>
                </div>
            </n-form>
        </div>

        <template #footer>
            <n-button quaternary @click="show = false">Tutup</n-button>
            <n-button type="primary" :loading="loading" @click="handleSubmit">
                Simpan
            </n-button>
        </template>
    </CardModal>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import { FormInst, FormRules, NSelect } from "naive-ui";
import Swal from "sweetalert2";
import CardModal from "@/Components/CardModal.vue";
import type { Warehouse, StockForm } from "@/types/global-object";
import { NButton, NForm, NFormItem, NInputNumber, NAlert } from "naive-ui";

const props = defineProps<{
    productId: number;
    unitName: string;
    warehouses: Warehouse[];
}>();

const show = defineModel<boolean>("show", { required: true });

const loading = ref(false);
const formRef = ref<FormInst | null>(null);

const defaultForm = (): StockForm => ({
    warehouse_id: null,
    quantity: null,
    warehouse_name: null,
    current_quantity: null,
});

const form = ref<StockForm>(defaultForm());

const warehouseOptions = computed(() =>
    props.warehouses.map((d) => ({
        label: d.name,
        value: d.id,
        current_quantity: d.current_quantity,
    })),
);

function onWarehouseChange(_val: number, option: any) {
    form.value.warehouse_name = option?.label ?? null;
    form.value.current_quantity = option?.current_quantity ?? null;
}

function onAfterLeave() {
    form.value = defaultForm();
}

const rules: FormRules = {
    warehouse_id: [
        {
            required: true,
            message: "Pilih gudang penyimpanan",
            trigger: ["change"],
        },
    ],
    quantity: [
        {
            validator: (_rule, value) => {
                if (value == null) return new Error("Quantity wajib diisi");
                if (Number(value) <= 0)
                    return new Error("Quantity harus lebih besar dari 0");
                return true;
            },
            trigger: ["change"],
        },
    ],
};

function handleSubmit() {
    if (!formRef.value) return;
    formRef.value.validate((errors) => {
        if (errors) return;

        loading.value = true;
        router.post(
            route("product-logs.restock"),
            {
                warehouse_id: form.value.warehouse_id,
                product_id: props.productId,
                quantity: form.value.quantity,
            },
            {
                onSuccess: (page: any) => {
                    show.value = false;
                    Swal.fire("", page.props.flash.success, "success");
                },
                onError: (errors) => {
                    Swal.fire(
                        "Gagal menambah stok",
                        errors["quantity"] ?? "Terjadi kesalahan",
                        "error",
                    );
                },
                onFinish: () => (loading.value = false),
            },
        );
    });
}
</script>
