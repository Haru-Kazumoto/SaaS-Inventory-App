<template>
    <div class="flex flex-col gap-5">
        <TitlePage
            eyebrow="Manajemen Barang"
            title="Tambah Barang"
            subTitle="Isi data barang dan stok awal di setiap gudang."
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
            mode="create"
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

defineProps<{
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
    router.post(route("products.store"), payload as any, {
        forceFormData: true,
        preserveScroll: true,
        onStart: () => (processing.value = true),
        onFinish: () => (processing.value = false),
        onSuccess: () =>
            notification.success({
                title: "Barang berhasil disimpan",
                duration: 3000,
            }),
        onError: (errors) =>
            notification.error({
                title: "Gagal menyimpan barang",
                content: Object.values(errors).join(", "),
                duration: 6000,
                closable: true,
            }),
    });
}
</script>
