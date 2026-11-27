<template>
    <MasterDataPage
        title="Penyimpanan"
        subtitle="Daftar gudang atau ruang tempat barang disimpan."
        item-label="Penyimpanan"
        :icon="Vault20Regular"
        :rows="(data as MasterRow[])"
        :routes="{ store: 'warehouse.store', update: 'warehouse.update', delete: 'warehouse.delete' }"
        :examples="{ name: 'GUDANG UTAMA', code: 'GUDANG_UTAMA' }"
    >
        <template #hint>
            Penyimpanan dipakai saat mencatat stok, restock, dan peminjaman barang.
        </template>
    </MasterDataPage>
</template>

<script setup lang="ts">
import { Vault20Regular } from "@vicons/fluent";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import MasterDataPage from "@/Components/Feature/MasterData/MasterDataPage.vue";
import type { MasterRow } from "@/Components/Feature/MasterData/types";

defineOptions({ layout: AuthenticatedLayout });

defineProps<{ data: unknown[] }>();
</script>
