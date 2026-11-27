<template>
    <div class="flex flex-col gap-5">
        <TitlePage
            eyebrow="Manajemen Barang"
            title="Ubah Barang"
            :subTitle="`Perbarui data ${product?.name ?? 'barang'} lalu simpan perubahan.`"
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

        <ProductForm
            mode="edit"
            :product="product"
            :quantities="quantities_product ?? []"
            :units="units ?? []"
            :sub-types="subTypes ?? []"
            :warehouses="warehouses ?? []"
            :processing="processing"
            @submit="submit"
            @cancel="goBack"
        />
    </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { NButton, NIcon, useNotification } from "naive-ui";
import { ArrowLeft20Regular } from "@vicons/fluent";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TitlePage from "@/Components/TitlePage.vue";
import ProductForm from "@/Components/Feature/Products/ProductForm.vue";
import type { ProductFormPayload } from "@/Components/Feature/Products/product-form";

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps<{
    product: Record<string, any>;
    quantities_product?: Array<{
        warehouse_id: number;
        warehouse_name: string;
        on_hand: number | string;
        condition: string | null;
    }>;
    units?: Array<{ name: string; code: string }>;
    subTypes?: Array<{ name: string; code: string }>;
    warehouses?: Array<{ id: number; name: string }>;
}>();

const notification = useNotification();
const processing = ref(false);

function goBack() {
    router.visit(route("products.index"));
}

function submit(payload: ProductFormPayload) {
    router.post(
        route("products.update", props.product.id),
        {
            _method: "PUT",
            ...payload,
            // tanpa file baru → gambar lama tetap dipakai
            image_product: payload.image_product ?? undefined,
        } as any,
        {
            forceFormData: true,
            preserveScroll: true,
            onStart: () => (processing.value = true),
            onFinish: () => (processing.value = false),
            onSuccess: () =>
                notification.success({
                    title: "Barang berhasil diperbarui",
                    duration: 3000,
                }),
            onError: (errors) =>
                notification.error({
                    title: "Gagal menyimpan perubahan",
                    content: Object.values(errors).join(", "),
                    duration: 6000,
                    closable: true,
                }),
        },
    );
}
</script>
