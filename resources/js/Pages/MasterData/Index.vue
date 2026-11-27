<template>
    <MasterDataPage
        :key="type"
        :type="type"
        :title="config.title"
        :subtitle="config.subtitle"
        :item-label="config.itemLabel"
        :icon="config.icon"
        :rows="rows"
        :owner-options="ownerOptions"
        :can-create="canCreate"
        :examples="config.examples"
    >
        <template #hint>{{ config.hint }}</template>
    </MasterDataPage>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { Organization20Regular, Ruler20Regular, Tag20Regular, Vault20Regular } from "@vicons/fluent";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import MasterDataPage from "@/Components/Feature/MasterData/MasterDataPage.vue";
import type { MasterRow } from "@/Components/Feature/MasterData/types";
import { useTerms } from "@/Composables/useTerms";
import { useFlash } from "@/Composables/useFlash";

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps<{
    type: "units" | "warehouses" | "categories" | "uoms";
    rows: MasterRow[];
    ownerOptions: { label: string; value: number | null }[];
    canCreate: boolean;
}>();

useFlash();
const { t } = useTerms();

const config = computed(() => {
    switch (props.type) {
        case "units":
            return {
                title: t("unit_plural"),
                subtitle: `Bagian organisasi pemilik barang. Di organisasi ini disebut "${t("unit")}".`,
                itemLabel: t("unit"),
                icon: Organization20Regular,
                examples: { name: "Teknik Jaringan", code: "TJKT", description: "Unit pengelola alat jaringan" },
                hint: `Setiap barang, gudang, dan anggota terhubung ke satu ${t("unit").toLowerCase()}.`,
            };
        case "warehouses":
            return {
                title: "Penyimpanan",
                subtitle: "Gudang atau ruang tempat barang disimpan.",
                itemLabel: "Penyimpanan",
                icon: Vault20Regular,
                examples: { name: "Gudang Utama", code: "GUDANG_UTAMA", description: "Lantai 2, dekat lab" },
                hint: "Penyimpanan dipakai saat mencatat stok, barang masuk, dan peminjaman. Pilih \"Bersama\" bila dipakai semua unit.",
            };
        case "categories":
            return {
                title: "Kategori Barang",
                subtitle: "Kelompok barang untuk memudahkan pencarian dan laporan.",
                itemLabel: "Kategori",
                icon: Tag20Regular,
                examples: { name: "Elektronik", code: "ELEKTRONIK", description: "Perangkat elektronik & aksesorinya" },
                hint: "Kategori mengelompokkan barang, mis. ALAT PRAKTIK, ELEKTRONIK, ATK.",
            };
        default:
            return {
                title: "Satuan Barang",
                subtitle: "Satuan hitung stok barang.",
                itemLabel: "Satuan",
                icon: Ruler20Regular,
                examples: { name: "Pcs", code: "PCS", description: "Satuan dasar untuk barang eceran" },
                hint: "Satuan menandai cara stok dihitung, mis. PCS, BOX, ROLL, METER.",
            };
    }
});
</script>
