<template>
    <div class="flex flex-col gap-4">
        <!-- Page title -->
        <TitlePage
            eyebrow="Manajemen Barang"
            title="Detail Barang"
            :subTitle="`Detail lengkap dari barang ${product.name}`"
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

        <!-- Layout utama -->
        <div class="grid grid-cols-7 gap-4">
            <!-- Kolom kiri (5/7) -->
            <div class="col-span-7 flex flex-col gap-4 lg:col-span-5">
                <ProductInfoCard :product="product" />

                <WarehouseStockCard
                    :product="product"
                    :warehouse-products="warehouse_products ?? []"
                    :warehouses="warehouses ?? []"
                    :createable="createable"
                />
            </div>

            <!-- Kolom kanan (2/7) -->
            <div class="col-span-7 lg:col-span-2">
                <StockSummaryPanel :product="product" class="lg:sticky lg:top-20" />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { PropType } from "vue";
import { router } from "@inertiajs/vue3";
import { ArrowLeft20Regular } from "@vicons/fluent";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TitlePage from "@/Components/TitlePage.vue";
import { NButton, NIcon } from "naive-ui";
import ProductInfoCard from "@/Components/Feature/Products/ProductInfoCard.vue";
import WarehouseStockCard from "@/Components/Feature/Products/WarehouseStockCard.vue";
import StockSummaryPanel from "@/Components/Feature/Products/StockSummaryPanel.vue";

import type {
    Product,
    WarehouseProduct,
    Warehouse,
} from "@/types/global-object";

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    product: { type: Object as PropType<Product>, required: true },
    createable: { type: Boolean, required: true },
    warehouse_products: {
        type: Array as PropType<WarehouseProduct[]>,
        required: false,
    },
    warehouses: { type: Array as PropType<Warehouse[]>, required: false },
    major: { type: String, required: false },
});

function goBack() {
    if (props.createable && props.major) {
        router.visit(route("products.major", props.major));
    } else {
        router.visit(route("products.index"));
    }
}
</script>
