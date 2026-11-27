<template>
    <MasterDataPage
        title="Satuan Barang"
        subtitle="Satuan hitung stok barang, dipakai di seluruh pencatatan stok."
        item-label="Satuan"
        :icon="Ruler20Regular"
        :rows="(datas as MasterRow[])"
        :routes="{ store: 'unit-product.store', update: 'unit-product.update', delete: 'unit-product.delete' }"
        with-description
        :examples="{ name: 'PCS', code: 'PCS', description: 'Satuan dasar untuk barang eceran' }"
    >
        <template #hint>
            Satuan menandai cara stok dihitung, mis. <b>PCS</b>, <b>BOX</b>, <b>ROLL</b>,
            <b>METER</b>.
        </template>
    </MasterDataPage>
</template>

<script setup lang="ts">
import { Ruler20Regular } from "@vicons/fluent";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import MasterDataPage from "@/Components/Feature/MasterData/MasterDataPage.vue";
import type { MasterRow } from "@/Components/Feature/MasterData/types";

defineOptions({ layout: AuthenticatedLayout });

defineProps<{ datas: unknown[] }>();
</script>
