<template>
    <div class="flex flex-col gap-3">
        <TitlePage title="Detail Barang" subTitle="Informasi dari barang dan parsialnya" />

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
            <n-button text @click="visitRoute('item-management.index')"
                class="self-start mt-auto">
                <template #icon>
                    <n-icon :component="IosArrowLtr24Filled" />
                </template>
                KEMBALI
            </n-button>
            <n-button class="ms-auto" type="warning" @click="handleGoEdit">EDIT BARANG INI</n-button>
        </div>
        <n-card hoverable :bordered="false" class="border border-gray-300 rounded-2xl">
            <div class="flex mb-3">
                <span class="text-base text-gray-400">Informasi Barang</span>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <!-- Nama Barang -->
                <div class="col-span-1">
                    <p class="text-sm text-gray-500">Nama Barang</p>
                    <p class="text-lg font-semibold">{{ formItem.name }}</p>
                </div>

                <!-- Merk Barang -->
                <div class="col-span-1">
                    <p class="text-sm text-gray-500">Merk Barang</p>
                    <p class="text-lg font-semibold">{{ formItem.brand_name }}</p>
                </div>

                <!-- Harga Satuan -->
                <div class="col-span-1">
                    <p class="text-sm text-gray-500">Harga Satuan</p>
                    <p class="text-lg font-semibold">Rp {{ Number(formItem.price).toLocaleString("id-ID") }}</p>
                </div>

                <!-- Satuan Barang -->
                <div class="col-span-1">
                    <p class="text-sm text-gray-500">Satuan Barang</p>
                    <p class="text-lg font-semibold">{{ formItem.unit }}</p>
                </div>

                <!-- Kategori Barang (pakai tag) -->
                <div class="col-span-1">
                    <p class="text-sm text-gray-500">Kategori Barang</p>
                    <n-tag type="info" size="medium" strong>{{
                        formItem.category === 'CONSUMABLE' ? 'HABIS PAKAI' :
                            'TIDAK HABIS PAKAI' }}</n-tag>
                </div>
            </div>
        </n-card>


        <n-card hoverable class="flex flex-col border border-gray-300 rounded-2xl" :bordered="false">
            <div class="flex items-center gap-3 mb-3">
                <span class="text-base text-gray-400">
                    Informasi Parsial Barang
                </span>

                <n-tooltip trigger="hover">
                    <template #trigger>
                        <n-icon :component="Info24Filled" size="25"></n-icon>
                    </template>

                    Parsial barang adalah pecahan barang dari unit barang
                </n-tooltip>
            </div>

            <div v-for="(item, index) in formPartialItem" :key="index" class="grid grid-cols-6 gap-4 mt-6">
                <!-- Divider -->
                <n-divider class="col-span-6">PARTIAL {{ index + 1 }}</n-divider>

                <!-- Kode Barang -->
                <div class="col-span-2">
                    <p class="text-sm text-gray-500">Kode Barang</p>
                    <p class="font-semibold">{{ item.code_item }}</p>
                </div>

                <!-- Tanggal Pelabelan -->
                <div class="col-span-2">
                    <p class="text-sm text-gray-500">Tanggal Pelabelan</p>
                    <p class="font-semibold">{{ formatDate(item.labeling_date) }}</p>
                </div>

                <!-- Kondisi Barang -->
                <div class="col-span-2">
                    <p class="text-sm text-gray-500">Kondisi Barang</p>
                    <n-tag :type="item.condition_item === 'GOOD'
                        ? 'success'
                        : item.condition_item === 'DAMAGED'
                            ? 'warning'
                            : 'error'">
                        {{
                            item.condition_item === 'GOOD'
                                ? 'BAIK'
                                : item.condition_item === 'DAMAGED'
                                    ? 'RUSAK'
                                    : 'HILANG'
                        }}
                    </n-tag>
                </div>

                <!-- Jumlah Barang (jika consumable) -->
                <div class="col-span-2" v-if="formItem.category === 'CONSUMABLE'">
                    <p class="text-sm text-gray-500">Jumlah Barang</p>
                    <p class="font-semibold">{{ item.base_quantity }}</p>
                </div>

                <!-- Gambar Barang -->
                <div class="col-span-2">
                    <p class="text-sm text-gray-500">Gambar Barang</p>
                    <n-image v-if="imageFiles[index] && imageFiles[index].length > 0"
                        :src="imageFiles[index][0].url || URL.createObjectURL(imageFiles[index][0].file as File)"
                        width="120" height="120" object-fit="cover" />
                    <p v-else class="text-gray-400 italic">Tidak ada gambar</p>
                </div>

                <!-- Status Barang -->
                <div class="col-span-2">
                    <p class="text-sm text-gray-500">Status Barang</p>
                    <n-tag :type="item.is_available > 0 ? 'success' : 'error'">
                        {{ item.is_available > 0 ? 'TERSEDIA' : 'TIDAK TERSEDIA' }}
                    </n-tag>
                </div>
            </div>

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
import { FormInst, NBackTop, NButton, NCard, NDataTable, NDatePicker, NDivider, NForm, NFormItem, NIcon, NImage, NInput, NModal, NP, NSelect, NTag, NText, NTooltip, NUpload, NUploadDragger, useNotification } from 'naive-ui';
import { defineComponent, h, ref } from 'vue';
import { Add12Filled, Filter16Filled, Image24Regular, IosArrowLtr24Filled, Info24Filled } from '@vicons/fluent';
import { router, useForm } from '@inertiajs/vue3';
import Back from '@/Components/Back.vue';
import { watch } from 'vue';
import type { UploadFileInfo } from 'naive-ui'
import { onMounted } from 'vue';
import { PartialItem } from '@/types/global-object';
import { formatDate } from '@/Utils/helper-function';

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
        const formRef = ref<FormInst | null>(null);
        const notification = useNotification();
        const showActionPartialForm = ref(true);
        const formPartialItem = ref<Array<any>>([]);
        const imageFiles = ref<Array<any>>([]);
        const backRoute = ref('item-managemen.major.' + props.utils.major.code.toLowerCase());

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

        function handleGoEdit() {
            router.get(route('item-management.edit', props.data.id));
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
            visitRoute: (name: string, params?: any) => router.visit(route(name, params)),
            rules,
            formRef,
            formItem,
            router,
            warehouseOptions,
            categoryOptions,
            Filter16Filled,
            Add12Filled,
            Image24Regular,
            IosArrowLtr24Filled,
            handleSubmitNewProduct,
            handlePreview,
            showModal,
            previewImage,
            addPartialItemForm,
            removePartialItem,
            formPartialItem,
            handleGoEdit,
            showActionPartialForm,
            handleFileUpload,
            imageFiles,
            backRoute,
            Info24Filled,
            formatDate
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
        NTag,
        NTooltip
    }
})
</script>