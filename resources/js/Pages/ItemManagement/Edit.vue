<template>
    <div class="flex flex-col gap-3">
        <TitlePage title="Perbarui Barang" subTitle="Memperbarui data barang yang ada di gudang" />

        <div class="grid grid-cols-3 gap-3">
            <ItemCard borderColor="border-blue-400" backgroundColor="bg-blue-100" titleColor="text-gray-700"
                subTitleColor="text-blue-700" :title="`Jumlah parsial barang ${data.name}`"
                :count="data_partial_count.count_all" />
            <ItemCard borderColor="border-green-400" backgroundColor="bg-green-100" titleColor="text-gray-700"
                subTitleColor="text-green-700" :title="`Jumlah parsial bagus`"
                :count="data_partial_count.count_good_condition" />
            <ItemCard borderColor="border-red-400" backgroundColor="bg-red-100" titleColor="text-gray-700"
                subTitleColor="text-red-700" :title="`Barang parsial rusak`"
                :count="data_partial_count.count_damaged_condition" />
        </div>

        <div class="flex items-center">
            <Back route="item-management.index" />
            <n-button class="ms-auto" type="info" @click="handleSubmitNewProduct">PERBARUI BARANG</n-button>
        </div>
        <n-card hoverable :bordered="false" class="border border-gray-300 rounded-2xl">
            <n-form ref="formRef" :model="formItem" size="large" :rule="rules">
                <div class="grid grid-cols-3 gap-3 ">
                    <n-form-item label="Nama Barang" path="name" class="col-span-1">
                        <n-input v-model:value="formItem.name" placeholder=""
                            @input="(value) => formItem.name = value.toUpperCase()" />
                    </n-form-item>
                    <n-form-item label="Merk Barang" path="brand_name" class="col-span-1">
                        <n-input v-model:value="formItem.brand_name" placeholder=""
                            @input="(value) => formItem.brand_name = value.toUpperCase()" />
                    </n-form-item>
                    <n-form-item label="Harga Satuan" path="price" class="col-span-1">
                        <CurrencyInput v-model:modelValue="formItem.price" />
                    </n-form-item>
                    <n-form-item label="Satuan Barang" path="unit" class="col-span-1">
                        <n-input v-model:value="formItem.unit" placeholder=""
                            @input="(value) => formItem.unit = value.toUpperCase()" />
                    </n-form-item>
                    <n-form-item label="Kategori Barang" path="category" class="col-span-1">
                        <n-select v-model:value="formItem.category" placeholder="" :options="categoryOptions" />
                    </n-form-item>
                </div>
            </n-form>
        </n-card>

        <n-card hoverable :bordered="false" class="flex flex-col border border-gray-300 rounded-2xl" >
            <div class="flex">
                <span class="text-xl font-bold">Informasi Parsial</span>
                <n-button type="info" secondary strong class="ms-auto" @click="addPartialItemForm"
                    v-if="showActionPartialForm">TAMBAH PARSIAL
                    BARANG</n-button>
            </div>

            <n-form label-placement="top" class="mt-4" size="large">
                <div v-for="(item, index) in formPartialItem" :key="index" class="grid grid-cols-6 gap-4 mt-6">
                    <!-- Divider -->
                    <n-divider class="col-span-6">PARTIAL {{ index + 1 }}</n-divider>

                    <!-- Kode Barang -->
                    <n-form-item label="Kode Barang" path="code" class="col-span-2">
                        <n-input v-model:value="item.code_item" placeholder="" size="large"
                            @input="(value) => item.code_item = value.toUpperCase()" />
                    </n-form-item>

                    <!-- Tanggal Pelabelan -->
                    <n-form-item label="Tanggal Pelabelan" path="labeling_date" class="col-span-2">
                        <n-date-picker value-format="yyyy-MM-dd HH:mm:ss" type="datetime" placeholder="" class="w-full"
                            size="large" v-model:formatted-value="item.labeling_date" />
                    </n-form-item>

                    <!-- Kondisi Barang -->
                    <n-form-item label="Kondisi Barang" class="col-span-2">
                        <n-select v-model:value="item.condition_item" placeholder="" :options="[
                            { label: 'BAIK', value: 'GOOD' },
                            { label: 'RUSAK', value: 'DAMAGED' },
                            { label: 'HILANG', value: 'LOST' },
                        ]" />
                    </n-form-item>

                    <!-- Jumlah Barang (Hanya untuk kategori CONSUMABLE) -->
                    <n-form-item label="Jumlah Barang" class="col-span-2" v-if="formItem.category === 'CONSUMABLE'">
                        <n-input v-model:value="item.base_quantity" placeholder=""
                            @input="(value) => item.base_quantity = value.replace(/\D/, '')" />
                    </n-form-item>

                    <!-- Gambar Barang -->
                    <n-form-item label="Gambar Barang" class="col-span-2">
                        <n-upload list-type="image-card" :multiple="false" :max="1" accept=".png,.jpg"
                            @update:file-list="(fileList) => handleFileUpload(index, fileList)"
                            v-model:file-list="imageFiles[index]">
                            Click to Upload
                        </n-upload>
                    </n-form-item>

                    <!-- Status Barang -->
                    <n-form-item label="Status Barang" class="col-span-2">
                        <n-tag :type="item.is_available > 0 ? 'info' : 'error'">
                            {{ item.is_available > 0 ? 'TERSEDIA' : 'TIDAK TERSEDIA' }}
                        </n-tag>
                    </n-form-item>

                    <!-- Tombol Hapus -->
                    <div class="col-span-6 flex justify-end">
                        <n-button type="error" strong secondary @click="removePartialItem(index)"
                            v-if="showActionPartialForm">
                            HAPUS FORM
                        </n-button>
                    </div>
                </div>
            </n-form>

            <n-back-top :right="100" />
        </n-card>

        <!-- modal preview images -->
        <n-modal v-model:show="showModal">
            <n-card style="width: 500px" title="Pratinjau Gambar" closable @close="showModal = false">
                <n-image v-if="previewImage" :src="previewImage" width="100%" />
                <p v-else>Tidak ada gambar untuk ditampilkan</p>
            </n-card>
        </n-modal>
    </div>
</template>

<script lang="ts">
import TitlePage from '@/Components/TitlePage.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CurrencyInput from '@/Components/CurrencyInput.vue';
import ItemCard from '@/Components/InformationCard.vue';
import { FormInst, NBackTop, NButton, NCard, NDataTable, NDatePicker, NDivider, NForm, NFormItem, NIcon, NImage, NInput, NModal, NP, NSelect, NTag, NText, NUpload, NUploadDragger, useNotification } from 'naive-ui';
import { defineComponent, h, ref } from 'vue';
import { Add12Filled, Filter16Filled, Image24Regular } from '@vicons/fluent';
import { router, useForm } from '@inertiajs/vue3';
import Back from '@/Components/Back.vue';
import { watch } from 'vue';
import type { UploadFileInfo } from 'naive-ui'
import { onMounted } from 'vue';
import { PartialItem } from '@/types/global-object';

interface CountObject {
    count_all: number;
    count_good_condition: number;
    count_damaged_condition: number;
}

export default defineComponent({
    props: {
        utils: { type: Object, required: true },
        data: { type: Object, required: true },
        data_partial_count: { type: Object as () => CountObject, required: true }
    },
    setup(props) {
        console.log(props.data);
        const formRef = ref<FormInst | null>(null);
        const notification = useNotification();
        const showActionPartialForm = ref(true);
        const formPartialItem = ref<Array<any>>([]);
        const imageFiles = ref<Array<any>>([]);

        const formItem = useForm({
            name: props.data.name,
            brand_name: props.data.brand_name,
            price: props.data.price,
            category: props.data.category,
            unit: props.data.unit,
            partial_items: [] as Array<any>
        });
        const rules = ref({
            name: { required: true, message: 'Nama barang harus di isi', trigger: 'blur' },
            brand_name: { required: false },
            price: { required: true, message: 'Harga barang harus di isi', trigger: 'blur' },
            category: { required: true, message: 'Kategori barang harus di isi', trigger: 'blur' },
            unit: { required: true, message: 'Satuan barang harus di isi', trigger: 'blur' },
        });
        const previewImage = ref('');
        const showModal = ref(false);

        onMounted(() => {
            formPartialItem.value = props.data.partial_items.map((data: PartialItem) => ({
                id: data.id,
                labeling_date: data.labeling_date,
                code_item: data.code_item,
                condition_item: data.condition_item,
                base_quantity: data.base_quantity,
                existing_image: data.item_image, // Simpan path gambar existing
                item_image: null, // Untuk file baru
                is_available: data.is_available,
                // warehouse_name: data.warehouse.code
            }));

            // Inisialisasi imageFiles dengan array kosong jika tidak ada gambar
            imageFiles.value = props.data.partial_items.map((data: PartialItem) => {
                return data.item_image
                    ? [{ id: String(data.id), name: "Image.jpg", status: "finished", url: data.item_image }]
                    : [];
            });
        });

        // Handle file upload

        const handleFileUpload = (index: number, fileList: UploadFileInfo[]) => {
            if (fileList.length > 0 && fileList[0].file) {
                formPartialItem.value[index].item_image = fileList[0].file;
                imageFiles.value[index] = fileList; // Simpan file ke imageFiles per index
            } else {
                formPartialItem.value[index].item_image = null;
                imageFiles.value[index] = [];
            }
        };

        watch(
            () => formItem.category,
            (newCategory) => {
                if (newCategory !== 'CONSUMABLE') {
                    showActionPartialForm.value = true;
                    formPartialItem.value.forEach((item) => {
                        item.base_quantity = null as unknown as number;
                    });
                } else {
                    showActionPartialForm.value = false;
                }
            }
        );


        const warehouseOptions = props.utils.warehouses.map((warehouse: any) => {
            return { label: warehouse.name, value: warehouse.code }
        });

        const categoryOptions = [
            { label: "HABIS PAKAI", value: "CONSUMABLE" },
            { label: "TIDAK HABIS PAKAI", value: "DURABLE" }
        ];

        function addPartialItemForm() {
            // Tambahan array form baru
            formPartialItem.value.push({
                labeling_date: null as unknown as string,
                code_item: "",
                condition_item: "",
                base_quantity: null as unknown as number,
                item_image: null,
                warehouse_name: "",
            });

            // Tambahkan array kosong untuk gambar baru
            imageFiles.value.push([]);
        };

        // Menghapus item parsial berdasarkan indeks
        function removePartialItem(index: number) {
            formPartialItem.value.splice(index, 1);
        };

        const handleSubmitNewProduct = async (e: MouseEvent) => {
            e.preventDefault();

            const formData = new FormData();
            formData.append('_method', 'PUT');

            // Data utama
            Object.keys(formItem.data()).forEach(key => {
                if (key !== 'partial_items') {
                    formData.append(key, formItem[key] as any);
                }
            });

            // Data partial items
            formPartialItem.value.forEach((item, index) => {
                // Data non-file
                Object.keys(item).forEach(key => {
                    if (key !== 'image' && key !== 'existing_image') {
                        formData.append(`partial_items[${index}][${key}]`, item[key]);
                    }
                });

                // Handle file
                if (item.image instanceof File) {
                    formData.append(`partial_items[${index}][image]`, item.image);
                } else if (item.existing_image) {
                    formData.append(`partial_items[${index}][existing_image]`, item.existing_image);
                }
            });

            // Kirim request
            router.post(route('item-management.update', props.data.id), formData, {
                preserveScroll: true,
                onSuccess: () => {
                    notification.success({
                        title: "Barang berhasil diupdate",
                        closable: false,
                        duration: 2500
                    });
                },
                onError: (errors) => {
                    notification.error({
                        title: "Gagal mengupdate barang",
                        description: Object.values(errors).join(', '),
                        closable: false,
                        duration: 2500
                    });
                }
            });
        };



        function handlePreview(file: UploadFileInfo) {
            if (file.url) {
                previewImage.value = file.url // Gunakan URL dari file yang diunggah
                showModal.value = true // Tampilkan modal
            } else {
                notification.warning({
                    title: "Gambar tidak dapat di pratinjau",
                    closable: false,
                    duration: 2500
                })
            }
        }

        return {
            rules,
            formRef,
            formItem,
            router,
            warehouseOptions,
            categoryOptions,
            Filter16Filled,
            Add12Filled,
            Image24Regular,
            handleSubmitNewProduct,
            handlePreview,
            showModal,
            previewImage,
            addPartialItemForm,
            removePartialItem,
            formPartialItem,
            showActionPartialForm,
            handleFileUpload,
            imageFiles
        }
    },
    layout: AuthenticatedLayout,
    components: {
        TitlePage,
        NDataTable,
        NButton,
        NIcon,
        NInput,
        NCard,
        Back,
        NForm,
        NFormItem,
        NDatePicker,
        CurrencyInput,
        NSelect,
        NUpload,
        NUploadDragger,
        NText,
        NP,
        NModal,
        NImage,
        NDivider,
        NBackTop,
        ItemCard,
        NTag
    }
})
</script>