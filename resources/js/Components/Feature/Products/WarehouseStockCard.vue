<template>
    <div
        class="relative overflow-hidden rounded border border-slate-200 bg-white"
    >

        <!-- Header -->
        <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded bg-[#2A126C]/[0.06] text-[#2A126C]"
                >
                    <n-icon
                        :component="Building24Regular"
                        size="18"
                    />
                </div>
                <div class="flex flex-col leading-tight">
                    <span class="text-[15px] font-bold tracking-tight text-slate-900">
                        Informasi Lokasi Penyimpanan
                    </span>
                    <span class="mt-0.5 text-[11.5px] text-slate-500">
                        Stok per gudang dan kondisi barang
                    </span>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex items-center gap-2" v-if="canManage">
                <n-button
                    type="primary"
                    class="flex-1 sm:flex-none"
                    @click="showRestock = true"
                >
                    Restock Barang
                </n-button>

                <n-button
                    type="error"
                    ghost
                    class="flex-1 sm:flex-none"
                    @click="showWriteOff = true"
                >
                    Keluarkan Barang
                </n-button>
            </div>
        </div>

        
        <!-- Table -->
        <div class="px-5 py-4">
            <div class="overflow-x-auto rounded border border-slate-200">
                <n-table
                    :single-line="true"
                    size="medium"
                    class="text-left text-sm"
                >
                    <thead>
                        <tr>
                            <th
                                class="!bg-slate-50 !text-slate-500 !font-semibold !text-[11px] uppercase tracking-[0.08em] py-2.5 px-4"
                            >
                                Kondisi
                            </th>
                            <th
                                class="!bg-slate-50 !text-slate-500 !font-semibold !text-[11px] uppercase tracking-[0.08em] py-2.5 px-4"
                            >
                                Nama Gudang / Ruang
                            </th>
                            <th
                                class="!bg-slate-50 !text-slate-500 !font-semibold !text-[11px] uppercase tracking-[0.08em] py-2.5 px-4 !text-right"
                            >
                                Stok Tersedia
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="(wp, index) in warehouseProducts"
                            :key="index"
                            class="hover:bg-slate-50/40 transition-colors"
                        >
                            <td class="py-3.5 px-4">
                                <span
                                    :class="[
                                        'inline-flex items-center whitespace-nowrap rounded border px-1.5 py-0.5 text-[11px] font-semibold',
                                        conditionClass(wp.condition),
                                    ]"
                                >
                                    {{ formatCondition(wp.condition) }}
                                </span>
                            </td>
                            <td
                                class="py-3.5 px-4 font-medium text-slate-700"
                            >
                                {{ wp.warehouse_name }}
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <span
                                    class="font-bold tabular-nums text-slate-900"
                                >
                                    {{ wp.on_hand }}
                                </span>
                                <span
                                    class="ml-0.5 text-xs font-normal text-slate-400"
                                >
                                    {{ product.uom_name }}
                                </span>
                            </td>
                        </tr>

                        <tr v-if="!warehouseProducts?.length">
                            <td
                                colspan="3"
                                class="py-8 text-center text-sm text-slate-400"
                            >
                                Belum ada data penyimpanan
                            </td>
                        </tr>
                    </tbody>
                </n-table>
            </div>

            <!-- Footer info -->
            <div class="mt-4 flex items-center gap-2">
                <span class="text-xs text-slate-500 font-medium"
                    >Milik {{ t('unit').toLowerCase() }}:</span
                >
                <span
                    class="inline-flex items-center rounded border border-[#2A126C]/15 bg-[#2A126C]/[0.05] px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-[#2A126C]"
                >
                    {{ product.unit_name }}
                </span>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <StockMoveModal
        v-model:show="showRestock"
        mode="restock"
        :product-id="product.id"
        :uom-name="product.uom_name"
        :warehouses="warehouses"
    />
    <StockMoveModal
        v-model:show="showWriteOff"
        mode="writeoff"
        :product-id="product.id"
        :uom-name="product.uom_name"
        :warehouses="warehouses"
    />
</template>

<script setup lang="ts">
import { ref } from "vue";
import { useTerms } from "@/Composables/useTerms";
import { Building24Regular } from "@vicons/fluent";
import StockMoveModal from "./StockMoveModal.vue";
import type {
    Product,
    WarehouseProduct,
    Warehouse,
} from "@/types/global-object";
import { NTable, NButton, NIcon } from "naive-ui";

defineProps<{
    product: Product;
    warehouseProducts: WarehouseProduct[];
    warehouses: Warehouse[];
    canManage: boolean;
}>();

const { t } = useTerms();
const showRestock = ref(false);
const showWriteOff = ref(false);

function formatCondition(condition: string) {
    switch (condition) {
        case "GOOD":
            return "Kondisi bagus";
        case "DAMAGED":
            return "Kondisi rusak";
        case "OUT":
            return "Dipinjam";
        default:
            return "-";
    }
}

function conditionClass(condition: string) {
    switch (condition) {
        case "GOOD":
            return "border-emerald-200 bg-emerald-50 text-emerald-700";
        case "DAMAGED":
            return "border-rose-200 bg-rose-50 text-rose-700";
        case "OUT":
            return "border-amber-200 bg-amber-50 text-amber-700";
        default:
            return "border-slate-200 bg-slate-50 text-slate-600";
    }
}
</script>
