<template>
    <div
        class="relative overflow-hidden rounded border border-slate-200 bg-white"
    >

        <!-- Header -->
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
            <div class="flex items-center gap-3">
                <div
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded bg-[#2A126C]/[0.06] text-[#2A126C]"
                >
                    <n-icon
                        :component="Info24Regular"
                        size="18"
                    />
                </div>
                <div class="flex flex-col leading-tight">
                    <span
                        class="text-[15px] font-bold tracking-tight text-slate-900"
                    >
                        Informasi Detail Barang
                    </span>
                    <span class="mt-0.5 text-[11.5px] text-slate-500">
                        Data lengkap dan atribut barang
                    </span>
                </div>
            </div>
            <RefreshButton
                :refreshProps="['stock', 'product', 'warehouses']"
                size="medium"
            />
        </div>


        <!-- Body -->
        <div class="grid grid-cols-1 gap-6 px-5 py-5 sm:grid-cols-3">
            <!-- Descriptions -->
            <div class="sm:col-span-2">
                <div class="grid grid-cols-2 gap-x-6 gap-y-5">
                    <DescField label="Nama Barang">
                        <span
                            class="text-sm font-bold text-slate-900"
                        >
                            {{ product.name ?? "-" }}
                        </span>
                    </DescField>

                    <DescField label="Kode Barang">
                        <span
                            class="rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 font-mono text-xs font-semibold text-slate-700"
                        >
                            {{ product.code ?? "-" }}
                        </span>
                    </DescField>

                    <DescField label="Merk / Brand">
                        <span
                            class="text-sm font-semibold text-slate-700"
                        >
                            {{ product.brand_name ?? "-" }}
                        </span>
                    </DescField>

                    <DescField label="Kategori Tipe">
                        <span
                            class="rounded border px-1.5 py-0.5 text-[11px] font-semibold"
                            :class="
                                product.type === 'DURABLE'
                                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                    : 'border-sky-200 bg-sky-50 text-sky-700'
                            "
                        >
                            {{ typeLabel }}
                        </span>
                    </DescField>

                    <DescField label="Sub Kategori">
                        <span
                            class="text-sm font-medium text-slate-600"
                        >
                            {{ product.category_name ?? "-" }}
                        </span>
                    </DescField>

                    <DescField label="Satuan Hitung">
                        <span
                            class="text-sm font-medium text-slate-700"
                        >
                            {{ product.uom_name ?? "-" }}
                        </span>
                    </DescField>

                    <DescField label="Nilai Satuan (Harga)">
                        <span class="text-sm font-bold tabular-nums text-slate-900">
                            {{ formatIDR(product.price) }}
                        </span>
                    </DescField>

                    <DescField label="Tanggal Pelabelan">
                        <span
                            class="text-sm font-medium text-slate-600"
                        >
                            {{ formatDate(product.labeling_date ?? '') }}
                        </span>
                    </DescField>

                    <DescField label="Terdaftar Pada" class="col-span-2">
                        <span
                            class="text-sm tabular-nums text-slate-600"
                        >
                            {{ formatDate(product.created_at ?? '') }}
                        </span>
                    </DescField>
                </div>
            </div>

            <!-- Visual Aset -->
            <div class="flex flex-col gap-2">
                <span
                    class="text-[10.5px] font-semibold uppercase tracking-[0.14em] text-slate-400"
                >
                    Visual Aset
                </span>

                <div
                    v-if="product.image_url"
                    class="flex aspect-square max-h-64 items-center justify-center overflow-hidden rounded border border-slate-200 bg-slate-50"
                >
                    <n-image
                        :src="product.image_url"
                        alt="Gambar Barang"
                        object-fit="contain"
                        class="h-full w-full"
                        :img-props="{ class: 'h-full w-full object-contain' }"
                    />
                </div>

                <div
                    v-else
                    class="flex aspect-square max-h-64 flex-col items-center justify-center gap-2 rounded border border-dashed border-slate-300 bg-slate-50 text-slate-400"
                >
                    <n-icon
                        :size="32"
                        :component="ImageProhibited20Regular"
                        class="text-slate-300"
                    />
                    <span class="text-xs font-medium"
                        >Berkas gambar belum diunggah</span
                    >
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { Info24Regular, ImageProhibited20Regular } from "@vicons/fluent";
import RefreshButton from "@/Components/RefreshButton.vue";
import DescField from "./DescField.vue";
import { formatDate } from "@/Utils/helper-function";
import type { Product } from "@/types/global-object";
import { NImage, NIcon } from "naive-ui";

const props = defineProps<{ product: Product }>();

const typeLabel = computed(() =>
    props.product.type === "DURABLE" ? "TIDAK HABIS PAKAI" : "HABIS PAKAI",
);

function formatIDR(val?: number | null) {
    if (val == null) return "-";
    try {
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            maximumFractionDigits: 0,
        }).format(val);
    } catch {
        return `${val}`;
    }
}
</script>
