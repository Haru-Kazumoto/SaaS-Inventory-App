<template>
    <div class="flex flex-col gap-3">
        <TitlePage title="Masukan Barang Baru" subTitle="Pemasukan barang baru ke gudang" />
        <div class="flex items-center">
            <Back route="item-management.index" />
            <n-button class="ms-auto" type="primary" @click="handleSubmitNewProduct">SUBMIT BARANG</n-button>
        </div>
        <n-card hoverable :bordered="false" class="border border-gray-300 rounded-2xl">
            <n-form ref="formRef" :model="formItem" :rules="rules" size="large">
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

        <n-card hoverable :bordered="false" class="flex flex-col border border-gray-300 rounded-2xl">
            <div class="flex">
                <span class="text-xl font-bold">Informasi Parsial</span>
                <n-button type="info" secondary strong class="ms-auto" @click="addPartialItemForm"
                    v-if="showActionPartialForm">TAMBAH PARSIAL
                    BARANG</n-button>
            </div>

            <n-form label-placement="top" class="mt-4" size="large">
                <div v-for="(item, index) in formPartialItem" :key="index" class="grid grid-cols-3 gap-3 mt-4">
                    <n-divider class="col-start-1 col-end-6">PARTIAL {{ index + 1 }}</n-divider>
                    <n-form-item label="Kode Barang" path="code" class="col-span-1">
                        <n-input v-model:value="item.code_item" placeholder="" size="large"
                            @input="(value) => item.code_item = value.toUpperCase()" />
                    </n-form-item>

                    <n-form-item label="Tanggal Pelabelan" path="labeling_date" class="col-span-1">
                        <n-date-picker value-format="yyyy-MM-dd HH:mm:ss" type="datetime" placeholder="" class="w-full"
                            size="large" v-model:formatted-value="item.labeling_date" />
                    </n-form-item>

                    <n-form-item label="Kondisi Barang" class="col-span-1">
                        <n-select v-model:value="item.condition_item" placeholder="" :options="[
                            { label: 'BAIK', value: 'GOOD' },
                            { label: 'RUSAK', value: 'DAMAGED' },
                            { label: 'HILANG', value: 'LOST' },
                        ]" />
                    </n-form-item>

                    <n-form-item label="Jumlah Barang" class="col-span-1" v-if="formItem.category === 'CONSUMABLE'">
                        <n-input v-model:value="item.base_quantity" placeholder=""
                            @input="(value) => item.base_quantity = value.replace(/\D/, '')" />
                    </n-form-item>
                    <n-form-item label="Tempat penyimpanan" class="col-span-2">
                        <n-select class="w-[19rem]" placeholder="" :options="warehouseOptions" v-model:value="item.warehouse_name"></n-select>
                    </n-form-item>
                    <n-form-item label="Gambar Barang" class="col-span-5">
                        <n-upload list-type="image-card" :multiple="false" directory-dnd :max="1" accept=".png,.jpg"
                            v-model:file-list="item.imageFiles">
                            Click to Upload
                        </n-upload>
                    </n-form-item>


                    <div class="col-span-1 col-start-5 flex">
                        <n-button type="error" class="ms-auto" strong secondary @click="removePartialItem(index)"
                            v-if="showActionPartialForm">HAPUS
                            FORM</n-button>
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
import { FormInst, NBackTop, NButton, NCard, NDataTable, NDatePicker, NDivider, NForm, NFormItem, NIcon, NImage, NInput, NModal, NP, NSelect, NText, NUpload, NUploadDragger, useNotification } from 'naive-ui';
import { defineComponent, h, ref } from 'vue';
import { Add12Filled, Filter16Filled, Image24Regular } from '@vicons/fluent';
import { router, useForm } from '@inertiajs/vue3';
import Back from '@/Components/Back.vue';
import { watch } from 'vue';
import type { UploadFileInfo } from 'naive-ui'

export default defineComponent({
    props: {
        warehouses: { type: Array }
    },
    setup(props) {
        const formRef = ref<FormInst | null>(null);
        const imageFiles = ref([]);
        const notification = useNotification();
        const formPartialItem = ref([
            {
                labeling_date: null as unknown as string,
                code_item: null as unknown as string,
                condition_item: null as unknown as string,
                base_quantity: null as unknown as number,
                item_image: null as unknown as string,
                warehouse_name: null as unknown as string,
                imageFiles: []
            }
        ]);
        const formItem = useForm({
            name: null as unknown as string,
            brand_name: null as unknown as string,
            price: null as unknown as string,
            category: null as unknown as string,
            unit: null as unknown as string,
            partial_items: formPartialItem.value
        });
        const rules = ref({
            name: { required: true, message: 'Nama barang harus di isi', trigger: 'blur' },
            code: { required: true, message: 'Kode barang harus di isi', trigger: 'blur' },
            brand_name: { required: false },
            labeling_date: { required: false },
            quantity: { required: true, message: 'Jumlah barang harus di isi', trigger: 'blur' },
            item_picture: { required: true, message: 'Foto barang harus di isi', trigger: 'blur' },
            price: { required: true, message: 'Harga barang harus di isi', trigger: 'blur' },
            category: { required: true, message: 'Kategori barang harus di isi', trigger: 'blur' },
            condition: { required: true, message: 'Kondisi barang harus di isi', trigger: 'blur' },
            status: { required: true, message: 'Status barang harus di isi', trigger: 'blur' },
            unit: { required: true, message: 'Satuan barang harus di isi', trigger: 'blur' },
            warehouse: { required: true, message: 'Gudang barang harus di isi', trigger: 'blur' },
        });
        const previewImage = ref('');
        const showModal = ref(false);
        const showActionPartialForm = ref(true);

        // Watch setiap perubahan file upload
        watch(
            () => formPartialItem.value,
            (newList) => {
                newList.forEach((item) => {
                    if (!item.imageFiles) return;
                    item.item_image = item.imageFiles.length > 0 ? item.imageFiles[0].file : "";
                });
            },
            { deep: true, immediate: true }
        );

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


        const warehouseOptions = props.warehouses?.map((warehouse: any) => {
            return { label: warehouse.name, value: warehouse.code }
        });

        const categoryOptions = [
            { label: "HABIS PAKAI", value: "CONSUMABLE" },
            { label: "TIDAK HABIS PAKAI", value: "DURABLE" }
        ];

        function handleImageChange(fileList: any[], index: number) {
            if (fileList.length > 0) {
                formPartialItem.value[index].item_image = fileList[0].url; // Ambil URL dari file pertama
            } else {
                formPartialItem.value[index].item_image = ""; // Kosongkan jika tidak ada gambar
            }
        };

        function addPartialItemForm() {
            formPartialItem.value.push({
                labeling_date: null as unknown as string,
                code_item: "",
                condition_item: "",
                base_quantity: null as unknown as number,
                item_image: "",
                warehouse_name: "",
                imageFiles: []
            });
        };

        // Menghapus item parsial berdasarkan indeks
        function removePartialItem(index: number) {
            formPartialItem.value.splice(index, 1);
        };

        function handleSubmitNewProduct(e: MouseEvent) {
            e.preventDefault();
            formRef.value?.validate();

            // Validasi Partial Items
            const isValid = formPartialItem.value.every((item) => {
                return item.labeling_date && item.code_item && item.condition_item;
            });

            if (!isValid) {
                notification.error({
                    title: "Pastikan field parsial item terisi!",
                    description: "Cek kembali!",
                    closable: false,
                    duration: 2500
                });
                return; // Hentikan submit jika ada field kosong
            }

            // Insert Partial Items ke FormItem
            formItem.partial_items = [...formPartialItem.value];

            // Submit Form
            formItem.post(route('item-management.store'), {
                preserveScroll: true,
                onSuccess: () => {
                    formItem.reset();
                    notification.success({
                        title: "Barang berhasil ditambahkan",
                        closable: false,
                        duration: 2500
                    });
                },
                onError: () => {
                    notification.error({
                        title: "Gagal menambahkan barang",
                        closable: false,
                        duration: 2500
                    });
                }
            });
        }


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
            imageFiles,
            addPartialItemForm,
            removePartialItem,
            formPartialItem,
            handleImageChange,
            showActionPartialForm
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
        NBackTop
    }
})
</script>