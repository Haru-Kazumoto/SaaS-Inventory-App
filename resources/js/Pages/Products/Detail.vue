<template>
    <div class="flex flex-col gap-4">
        <TitlePage
            eyebrow="Barang"
            title="Detail Barang"
            :subTitle="`Detail lengkap dari barang ${product.name}`"
        >
            <template #action>
                <div class="flex w-full gap-2 sm:w-auto">
                    <n-button class="flex-1 sm:flex-none" @click="goBack">
                        <template #icon><n-icon :component="ArrowLeft20Regular" /></template>
                        Kembali
                    </n-button>
                    <n-button v-if="canManage" class="flex-1 sm:flex-none" @click="goEdit">
                        <template #icon><n-icon :component="Edit20Regular" /></template>
                        Ubah
                    </n-button>
                </div>
            </template>
        </TitlePage>

        <div class="grid grid-cols-7 gap-4">
            <div class="col-span-7 flex flex-col gap-4 lg:col-span-5">
                <ProductInfoCard :product="product" />
                <WarehouseStockCard
                    :product="product"
                    :warehouse-products="stock"
                    :warehouses="warehouses"
                    :can-manage="canManage"
                />
            </div>

            <div class="col-span-7 lg:col-span-2">
                <StockSummaryPanel :product="product" class="lg:sticky lg:top-20" />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import { NButton, NIcon } from "naive-ui";
import { ArrowLeft20Regular, Edit20Regular } from "@vicons/fluent";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TitlePage from "@/Components/TitlePage.vue";
import ProductInfoCard from "@/Components/Feature/Products/ProductInfoCard.vue";
import WarehouseStockCard from "@/Components/Feature/Products/WarehouseStockCard.vue";
import StockSummaryPanel from "@/Components/Feature/Products/StockSummaryPanel.vue";
import type { Product, Warehouse, WarehouseProduct } from "@/types/global-object";
import { useFlash } from "@/Composables/useFlash";

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps<{
    product: Product;
    stock: WarehouseProduct[];
    warehouses: Warehouse[];
    canManage: boolean;
}>();

useFlash();

function goBack() {
    router.visit(route("products.index"));
}

function goEdit() {
    router.visit(route("products.edit", props.product.id));
}
</script>
