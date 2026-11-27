<template>
    <n-modal
        v-model:show="show"
        preset="card"
        :bordered="false"
        :title="mode === 'restock' ? 'Catat Barang Masuk' : 'Hapuskan Barang'"
        class="!w-[calc(100vw-2rem)] max-w-md !rounded"
        :segmented="{ content: true, footer: true }"
        :mask-closable="!form.processing"
    >
        <p class="mb-4 text-xs leading-relaxed text-slate-500">
            <template v-if="mode === 'restock'">Stok bertambah dan tercatat di halaman Barang Masuk.</template>
            <template v-else>Barang rusak / pensiun / hilang dikeluarkan dari gudang, tidak lagi dihitung stok, dan tercatat di Barang Dihapuskan.</template>
        </p>

        <div class="flex flex-col gap-4">
            <label class="flex flex-col gap-1">
                <span class="text-xs font-medium text-slate-600">Gudang</span>
                <n-select
                    v-model:value="form.warehouse_id"
                    :options="warehouseOptions"
                    placeholder="Pilih gudang"
                    filterable
                    :status="form.errors.warehouse_id ? 'error' : undefined"
                />
                <span v-if="form.errors.warehouse_id" class="text-xs text-rose-600">{{ form.errors.warehouse_id }}</span>
            </label>

            <div class="flex flex-col gap-1">
                <span class="text-xs font-medium text-slate-600">
                    {{ mode === "restock" ? "Kondisi barang masuk" : "Diambil dari kondisi" }}
                </span>
                <div class="grid h-[34px] grid-cols-2 overflow-hidden rounded border border-slate-300" role="radiogroup">
                    <button
                        v-for="c in CONDITIONS"
                        :key="c.value"
                        type="button"
                        role="radio"
                        :aria-checked="form.condition === c.value"
                        class="text-xs font-semibold transition-colors duration-150 first:border-r first:border-slate-300"
                        :class="form.condition === c.value ? c.active : 'bg-white text-slate-500 hover:bg-slate-50'"
                        @click="form.condition = c.value"
                    >
                        {{ c.label }}
                    </button>
                </div>
            </div>

            <label class="flex flex-col gap-1">
                <span class="text-xs font-medium text-slate-600">Jumlah</span>
                <n-input-number
                    v-model:value="form.quantity"
                    :min="1"
                    :max="mode === 'writeoff' ? available || undefined : undefined"
                    placeholder="0"
                    :status="form.errors.quantity ? 'error' : undefined"
                >
                    <template #suffix><span class="text-xs text-slate-400">{{ uomName || "unit" }}</span></template>
                </n-input-number>
                <span v-if="form.errors.quantity" class="text-xs text-rose-600">{{ form.errors.quantity }}</span>
                <span v-else-if="selected" class="text-[11px] text-slate-500">
                    Stok {{ form.condition === "GOOD" ? "bagus" : "rusak" }} di gudang ini:
                    <b class="tabular-nums text-slate-700">{{ available }}</b> {{ uomName }}
                </span>
            </label>

            <label v-if="mode === 'writeoff'" class="flex flex-col gap-1">
                <span class="text-xs font-medium text-slate-600">Alasan</span>
                <n-input v-model:value="form.reason" placeholder="cth: rusak berat, sudah tidak layak pakai" maxlength="255" />
            </label>
        </div>

        <template #footer>
            <div class="flex justify-end gap-2">
                <n-button :disabled="form.processing" @click="show = false">Batal</n-button>
                <n-button
                    :type="mode === 'restock' ? 'primary' : 'error'"
                    :loading="form.processing"
                    :disabled="!form.warehouse_id || !form.quantity"
                    @click="submit"
                >
                    {{ mode === "restock" ? "Simpan Barang Masuk" : "Hapuskan" }}
                </n-button>
            </div>
        </template>
    </n-modal>
</template>

<script setup lang="ts">
import { computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import { NButton, NInput, NInputNumber, NModal, NSelect } from "naive-ui";
import type { Warehouse } from "@/types/global-object";

const props = defineProps<{
    mode: "restock" | "writeoff";
    productId: number;
    uomName?: string | null;
    warehouses: Warehouse[];
}>();

const show = defineModel<boolean>("show", { required: true });

const CONDITIONS = [
    { value: "GOOD", label: "Bagus", active: "bg-emerald-600 text-white" },
    { value: "DAMAGED", label: "Rusak", active: "bg-rose-600 text-white" },
];

const form = useForm({
    warehouse_id: null as number | null,
    condition: "GOOD",
    quantity: null as number | null,
    reason: "",
});

watch(show, (open) => {
    if (!open) return;
    form.reset();
    form.clearErrors();
    form.warehouse_id = props.warehouses.length === 1 ? props.warehouses[0].id : null;
});

const warehouseOptions = computed(() =>
    props.warehouses.map((w) => ({ label: `${w.name} (stok ${w.current_quantity ?? 0})`, value: w.id })),
);

const selected = computed(() => props.warehouses.find((w) => w.id === form.warehouse_id));
const available = computed(() =>
    form.condition === "GOOD" ? selected.value?.good_quantity ?? 0 : selected.value?.damaged_quantity ?? 0,
);

function submit() {
    const name = props.mode === "restock" ? "products.restock" : "products.write-off";
    form.post(route(name, props.productId), {
        preserveScroll: true,
        onSuccess: () => (show.value = false),
    });
}
</script>
