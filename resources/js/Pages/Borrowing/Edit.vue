<template>
    <div class="flex flex-col gap-3">
        <TitlePage
            :title="`Ubah Peminjaman #${txId}`"
            subTitle="Edit daftar barang & jumlah"
        >
            <template #action>
                <div class="flex gap-2">
                    <n-button @click="goBack">Kembali</n-button>
                    <n-button
                        type="primary"
                        :loading="submitting"
                        @click="submit"
                        >Simpan Perubahan</n-button
                    >
                </div>
            </template>
        </TitlePage>

        <n-card :bordered="false" class="rounded-2xl border">
            <n-form label-placement="left" :label-width="140" size="small">
                <n-form-item label="Tanggal Transaksi">
                    <n-date-picker
                        v-model:value="occurredAtMs"
                        type="datetime"
                        class="w-[280px]"
                    />
                </n-form-item>
                <n-form-item label="Catatan">
                    <n-input
                        v-model:value="description"
                        type="textarea"
                        placeholder="Catatan perubahan..."
                    />
                </n-form-item>
            </n-form>
        </n-card>

        <n-card
            :bordered="false"
            class="rounded-2xl border"
            title="Daftar Barang"
        >
            <n-dynamic-input
                v-model:value="rows"
                :on-create="onCreate"
                show-on-hover
            >
                <template #default="{ value, index }">
                    <div class="grid grid-cols-12 gap-2 w-full items-start">
                        <div class="col-span-5">
                            <n-select
                                v-model:value="value.product_id"
                                :options="productOptions"
                                placeholder="Pilih Barang"
                                filterable
                                clearable
                            />
                        </div>
                        <div class="col-span-4">
                            <n-select
                                v-model:value="value.warehouse_id"
                                :options="warehouseOptions"
                                placeholder="Pilih gudang"
                                filterable
                                clearable
                            />
                        </div>
                        <div class="col-span-3">
                            <n-input-number
                                v-model:value="value.quantity"
                                :min="1"
                                placeholder="Qty"
                                class="w-full"
                            />
                        </div>

                        <!-- hidden line id -->
                        <input type="hidden" :value="value.line_id ?? ''" />
                    </div>
                </template>
            </n-dynamic-input>

            <div class="flex justify-between pt-3">
                <n-tag round size="small">Total baris: {{ rows.length }}</n-tag>
                <n-button size="small" secondary @click="resetToOriginal"
                    >Reset</n-button
                >
            </div>
        </n-card>
    </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import TitlePage from "@/Components/TitlePage.vue";
import {
    NCard,
    NButton,
    NDynamicInput,
    NSelect,
    NInputNumber,
    NForm,
    NFormItem,
    NDatePicker,
    NTag,
    NInput,
    useMessage,
} from "naive-ui";

const props = defineProps<{
    txId: number;
    occurred_at: string; // "YYYY-MM-DD HH:mm:ss"
    description?: string | null;
    items: Array<{
        line_id: number;
        product_id: number;
        warehouse_id: number;
        quantity: number;
    }>;
    productOptions: Array<{ label: string; value: number }>;
    warehouseOptions: Array<{ label: string; value: number }>;
}>();

const message = useMessage();
const submitting = ref(false);
const occurredAtMs = ref(Date.parse(props.occurred_at.replace(" ", "T")));
const description = ref(props.description ?? null);

type Row = {
    line_id: number | null;
    product_id: number | null;
    warehouse_id: number | null;
    quantity: number | null;
};
const rows = ref<Row[]>(props.items.map((i) => ({ ...i })));

// keep originals for reset
const original = JSON.parse(JSON.stringify(rows.value));

function onCreate(): Row {
    return { line_id: null, product_id: null, warehouse_id: null, quantity: 1 };
}
function resetToOriginal() {
    rows.value = JSON.parse(JSON.stringify(original));
}

function goBack() {
    router.visit(route("borrowing.index"));
}

function fmt(dtMs: number | null) {
    if (!dtMs) return null;
    const d = new Date(dtMs);
    const pad = (n: number) => String(n).padStart(2, "0");
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(
        d.getDate()
    )} ${pad(d.getHours())}:${pad(d.getMinutes())}:00`;
}

function submit() {
    // basic check
    for (const [i, r] of rows.value.entries()) {
        if (
            !r.product_id ||
            !r.warehouse_id ||
            !r.quantity ||
            r.quantity <= 0
        ) {
            message.error(`Baris ${i + 1}: Barang/gudang/qty tidak valid`);
            return;
        }
    }

    submitting.value = true;
    router.put(
        route("borrowing.update", props.txId),
        {
            occurred_at: fmt(occurredAtMs.value),
            description: description.value,
            lines: rows.value.map((r, idx) => ({
                line_id: r.line_id,
                product_id: r.product_id,
                warehouse_id: r.warehouse_id,
                quantity: r.quantity,
            })),
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                submitting.value = false;
                message.success("Berhasil diperbarui");
            },
            onError: () => {
                submitting.value = false;
                message.error("Gagal memperbarui");
            },
        }
    );
}
</script>
