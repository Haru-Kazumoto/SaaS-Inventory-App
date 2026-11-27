<template>
    <div class="flex flex-col gap-5">
        <TitlePage eyebrow="Barang" title="Ubah Barang" :subTitle="`Perbarui data ${product.name} lalu simpan perubahan.`">
            <template #action>
                <n-button class="w-full sm:w-auto" @click="goBack">
                    <template #icon><n-icon :component="ArrowLeft20Regular" /></template>
                    Kembali
                </n-button>
            </template>
        </TitlePage>

        <ProductForm
            mode="edit"
            :product="product"
            :stock="stock"
            :units="units"
            :categories="categories"
            :uoms="uoms"
            :warehouses="warehouses"
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
import type { ProductFormOptions, ProductFormPayload } from "@/Components/Feature/Products/product-form";

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps<
    ProductFormOptions & {
        product: Record<string, any>;
        stock: Array<{ warehouse_id: number; warehouse_name: string; on_hand: number; condition: string }>;
    }
>();

const notification = useNotification();
const processing = ref(false);

function goBack() {
    router.visit(route("products.show", props.product.id));
}

function submit(payload: ProductFormPayload) {
    router.post(
        route("products.update", props.product.id),
        { _method: "PUT", ...payload, image: payload.image ?? undefined } as any,
        {
            forceFormData: true,
            preserveScroll: true,
            onStart: () => (processing.value = true),
            onFinish: () => (processing.value = false),
            onSuccess: () => notification.success({ title: "Barang berhasil diperbarui", duration: 3000 }),
            onError: () =>
                notification.error({
                    title: "Gagal menyimpan perubahan",
                    content: "Periksa kembali data yang ditandai merah.",
                    duration: 5000,
                    closable: true,
                }),
        },
    );
}
</script>
