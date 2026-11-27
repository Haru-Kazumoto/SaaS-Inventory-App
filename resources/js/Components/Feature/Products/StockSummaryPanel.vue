<template>
    <div
        class="relative overflow-hidden rounded border border-slate-200 bg-white"
    >

        <!-- Header -->
        <div class="flex items-center gap-3 border-b border-slate-100 px-5 py-4">
            <div
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded bg-[#2A126C]/[0.06] text-[#2A126C]"
            >
                <n-icon
                    :component="ChartMultiple24Regular"
                    size="18"
                />
            </div>
            <div class="flex flex-col leading-tight">
                <span
                    class="text-[15px] font-bold tracking-tight text-slate-900"
                >
                    Informasi Keseluruhan
                </span>
                <span class="mt-0.5 text-[11.5px] text-slate-500">
                    Ringkasan stok barang
                </span>
            </div>
        </div>


        <!-- Cards -->
        <div class="grid grid-cols-1 gap-3 px-5 py-4 sm:grid-cols-2 lg:grid-cols-1">
            <StockStatCard
                :icon="BoxMultiple24Regular"
                label="Dalam gudang"
                :count="product.usable_qty"
                :unit="product.uom_name ?? ''"
                color="blue"
            />
            <StockStatCard
                :icon="BoxCheckmark24Regular"
                label="Kondisi bagus"
                :count="product.good_qty"
                :unit="product.uom_name ?? ''"
                color="green"
            />
            <StockStatCard
                :icon="BoxDismiss20Regular"
                label="Kondisi rusak"
                :count="product.damaged_qty"
                :unit="product.uom_name ?? ''"
                color="red"
            />
            <template v-if="product.type === 'DURABLE'">
                <StockStatCard
                    :icon="BoxArrowUp24Regular"
                    label="Sedang dipinjam"
                    :count="product.out_qty"
                    :unit="product.uom_name ?? ''"
                    color="amber"
                />
                <StockStatCard
                    :icon="BookToolbox20Regular"
                    label="Dihapuskan"
                    :count="product.off_qty"
                    :unit="product.uom_name ?? ''"
                    color="gray"
                />
            </template>
        </div>
    </div>
</template>

<script setup lang="ts">
import {
    BoxMultiple24Regular,
    BoxCheckmark24Regular,
    BoxDismiss20Regular,
    BoxArrowUp24Regular,
    BookToolbox20Regular,
    ChartMultiple24Regular,
} from "@vicons/fluent";
import StockStatCard from "@/Components/Feature/Products/StockStatCard.vue";
import type { Product } from "@/types/global-object.js";
import { NIcon } from "naive-ui";

defineProps<{ product: Product }>();
</script>
