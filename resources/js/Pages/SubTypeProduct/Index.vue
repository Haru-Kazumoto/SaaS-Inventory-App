<template>
    <MasterDataPage
        title="Sub Kategori Barang"
        subtitle="Kelompok barang yang lebih spesifik untuk memudahkan pencarian dan laporan."
        item-label="Sub Kategori"
        :icon="Tag20Regular"
        :rows="(datas as MasterRow[])"
        :routes="{
            store: 'sub-type-product.store',
            update: 'sub-type-product.update',
            delete: 'sub-type-product.delete',
        }"
        with-description
        :examples="{ name: 'TANG', code: 'TANG', description: 'Semua jenis tang (potong, crimping, dll.)' }"
    >
        <template #hint>
            Sub kategori mengelompokkan barang secara spesifik, mis. <b>TANG</b>, <b>OBENG</b>,
            <b>KABEL</b>.
        </template>
    </MasterDataPage>
</template>

<script setup lang="ts">
import { Tag20Regular } from "@vicons/fluent";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import MasterDataPage from "@/Components/Feature/MasterData/MasterDataPage.vue";
import type { MasterRow } from "@/Components/Feature/MasterData/types";

defineOptions({ layout: AuthenticatedLayout });

defineProps<{ datas: unknown[] }>();
</script>
