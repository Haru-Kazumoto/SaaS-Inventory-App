<template>
    <div class="flex flex-col gap-3">
        <TitlePage title="Buat Transaksi" subTitle="Buat transaksi barang baru" />

        <div class="flex items-center">
            <Back route="transaction.index" />
            <n-button class="ms-auto" type="primary" @click="handleSubmitTransaction">SUBMIT BARANG</n-button>
        </div>

        <n-card hoverable title="Informasi Peminjam">
            <n-form ref="formRef" size="large" :rules="rules" :model="formBorrower">
                <div class="grid grid-cols-4 gap-3 ">
                    <n-form-item label="Nama Panjang Peminjam" path="fullname" class="col-span-1">
                        <n-input placeholder="" v-model:value="formBorrower.fullname"
                            @input="(value) => formBorrower.fullname = value.toUpperCase()"></n-input>
                    </n-form-item>
                    <n-form-item label="Nomor Handphone Peminjam" path="phone" class="col-span-1">
                        <n-input placeholder="" v-model:value="formBorrower.phone"></n-input>
                    </n-form-item>
                    <n-form-item label="Ruangan Peminjam" path="room_code" class="col-span-1">
                        <n-input placeholder="" v-model:value="formBorrower.room_code"
                            @input="(value) => formBorrower.room_code = value.toUpperCase()"></n-input>
                    </n-form-item>
                    <n-form-item label="Guru Pengawas" path="teacher_name" class="col-span-1">
                        <n-input placeholder="" v-model:value="formBorrower.teacher_name"
                            @input="(value) => formBorrower.teacher_name = value.toUpperCase()"></n-input>
                    </n-form-item>
                    <n-form-item label="Deskripsi" path="description" class="col-span-2">
                        <n-input placeholder="" type="textarea" v-model:value="formBorrower.description"></n-input>
                    </n-form-item>
                </div>
            </n-form>
            <div class="flex flex-col gap-4">
                <span class="text-lg font-medium">Input Barang Dipinjam</span>
                <div class="grid grid-cols-4 gap-4">
                    <div class="col-span-1 flex flex-col">
                        <label for="item code">Kode Barang</label>
                        <n-select placeholder="" size="large" clearable filterable :options="itemOptions"
                            v-model:value="formItemsChoosen.code_item" :consistent-menu-width="false"></n-select>
                    </div>
                    <div class="col-span-1 flex flex-col">
                        <label for="item code">Nama Barang</label>
                        <n-input placeholder="" size="large" readonly
                            v-model:value="formItemsChoosen.item_name"></n-input>
                    </div>
                    <div class="col-span-1 flex flex-col" v-if="formItemsChoosen.base_quantity">
                        <label for="item code">Jumlah Barang, Tersedia ({{ formItemsChoosen.base_quantity }} {{ formItemsChoosen.unit }})</label>
                        <n-input :allow-input="onlyAllowNumber" placeholder="" size="large" v-model:value="formItemsChoosen.quantity">
                            <template #suffix>{{ formItemsChoosen.unit }}</template>
                        </n-input>
                    </div>
                    <div class="col-span-1 flex flex-col justify-end items-start">
                        <n-tooltip trigger="hover">
                            <template #trigger>
                                <n-button class="" size="large" type="info" @click="addItem">
                                    <template #icon>
                                        <n-icon :component="Add12Filled"></n-icon>
                                    </template>
                                </n-button>
                            </template>
                            Masukan barang ke list
                        </n-tooltip>
                    </div>
                </div>
                <n-data-table :columns :data="formBorrower.item_choosens" />
                <n-back-top :right="100" />
            </div>
        </n-card>
    </div>
</template>

<script lang="ts">
import Back from '@/Components/Back.vue';
import TitlePage from '@/Components/TitlePage.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { Add12Filled } from '@vicons/fluent';
import { FormInst, NBackTop, NButton, NCard, NDataTable, NDivider, NForm, NFormItem, NIcon, NInput, NSelect, NTooltip, useNotification } from 'naive-ui';
import Swal from 'sweetalert2';
import { h } from 'vue';
import { defineComponent, ref, watch } from 'vue';

type Items = {
    item_name: string,
    code_item: string,
    base_quantity: number,
    quantity: number,
    category: string,
    unit: string
}

export default defineComponent({
    props: {
        items: { type: Array, required: true },
        warehouse: { type: Array, required: true }
    },
    setup(props) {
        const formRef = ref<FormInst | null>(null);
        const notification = useNotification();
        const formItemsChoosen = ref({
            item_name: null as unknown as string,
            code_item: null as unknown as string,
            base_quantity: null as unknown as number,
            quantity: null as unknown as number,
            category: null as unknown as string,
            unit: null as unknown as string
        });
        const formBorrower = useForm({
            fullname: null as unknown as string,
            phone: null as unknown as string,
            room_code: null as unknown as string,
            teacher_name: null as unknown as string,
            description: null as unknown as string,
            item_choosens: [] as any[]
            // action passing out 
        });
        const rules = ref({
            fullname: { required: true, message: "Nama panjang peminjam harus diisi!", trigger: 'blur' },
            phone: { required: true, message: "Nomor handphone peminjam harus diisi!", trigger: 'blur' },
            room_code: { required: true, message: "Ruangan peminjam harus diisi!", trigger: 'blur' },
            teacher_name: { required: false, message: "Guru Pengawas harus diisi!", trigger: 'blur' },
        });

        function addItem() {
            if (!formItemsChoosen.value.code_item) {
                notification.error({
                    title: "Kode item harus diisi!",
                    duration: 2000,
                    closable: false
                });
                return;
            }

            // Cek apakah kode item sudah ada di dalam daftar `item_choosens`
            const isDuplicate = formBorrower.item_choosens.some(item => item.code_item === formItemsChoosen.value.code_item);

            if (isDuplicate) {
                notification.error({
                    title: "Item sudah dipilih!",
                    description: `Kode barang ${formItemsChoosen.value.code_item} sudah ada dalam daftar.`,
                    duration: 3000,
                    closable: false
                });
                return;
            }

            if(formItemsChoosen.value.base_quantity && formItemsChoosen.value.quantity > formItemsChoosen.value.base_quantity) {
                notification.error({
                    title: "Oops, quantity barang sepertinya melebihi yang ada",
                    meta: "Tolong di cek lagi yaa",
                    duration: 3000,
                    closable: false
                });
                return;
            }

            // Tambahkan item jika tidak duplikat
            formBorrower.item_choosens.push({ ...formItemsChoosen.value });

            // Reset form
            formItemsChoosen.value = {
                item_name: null as unknown as string,
                code_item: null as unknown as string,
                base_quantity: null as unknown as number,
                quantity: null as unknown as number,
                category: null as unknown as string,
                unit: null as unknown as string
            };
        }


        function deleteItem(index: number) {
            formBorrower.item_choosens.splice(index, 1);
        }

        function createColumns() {
            return [
                {
                    title: "Nama Barang",
                    key: 'item_name',
                    width: 150,
                },
                {
                    title: "Kode Barang",
                    key: "code_item",
                    width: 200,
                },
                {
                    title: "Jumlah Barang",
                    key: "quantity",
                    width: 150,
                    render(row: Items) {
                        return row.quantity ? `${row.quantity} ${row.unit}` : null;
                    }
                },
                {
                    title: "Aksi",
                    key: "action",
                    width: 100,
                    render(row: any, index: number) {
                        return h(NButton, {
                            type: 'error',
                            size: 'medium',
                            strong: true,
                            onClick: () => {
                                deleteItem(index);
                            }
                        }, "HAPUS");
                    }
                }
            ]
        }

        function handleSubmitTransaction(e: MouseEvent) {
            e.preventDefault();
            formRef.value?.validate();

            // Cek apakah ada barang yang dipilih
            if (formBorrower.item_choosens.length === 0) {
                notification.error({
                    title: "Barang belum dipilih!",
                    duration: 2000,
                    closable: false
                });
                return;
            }

            // Cek apakah ada item dengan `code_item` yang duplikat
            const itemCounts = new Map();
            for (const item of formBorrower.item_choosens) {
                itemCounts.set(item.code_item, (itemCounts.get(item.code_item) || 0) + 1);
            }

            const duplicates = [...itemCounts.entries()].filter(([_, count]) => count > 1);
            if (duplicates.length > 0) {
                notification.error({
                    title: "Terdapat item yang duplikat!",
                    description: `Kode barang ${duplicates.map(d => d[0]).join(', ')} tidak boleh duplikat.`,
                    duration: 3000,
                    closable: false
                });
                return;
            }

            // Jika lolos validasi, kirim data
            formBorrower.post(route('transaction.store'), {
                onSuccess: (page) => {
                    formBorrower.reset();
                    Swal.fire(page.props.flash.success, '', 'success');
                },
                onError: () => {
                    Swal.fire('Gagal mengirim data!', '', 'error');
                }
            });
        }


        const itemOptions = props.items.map((data: any) => ({
            label: `${data.code_item} - ${data.name}`,
            value: data.code_item,
            item_name: data.name,
            base_quantity: data.base_quantity,
            category: data.category,
            unit: data.unit
        }));

        const warehouseOptions = props.warehouse.map((data: any) => ({
            label: data.name,
            value: data.code
        }));
        // Mengubah item_name secara otomatis ketika code_item berubah
        watch(() => formItemsChoosen.value.code_item, (newCode) => {
            const selectedItem = itemOptions.find((opt) => opt.value === newCode);
            formItemsChoosen.value.item_name = selectedItem ? selectedItem.item_name : null;
            formItemsChoosen.value.base_quantity = selectedItem?.base_quantity;
            formItemsChoosen.value.category = selectedItem?.category;
            formItemsChoosen.value.unit = selectedItem?.unit;
        }, { deep: true });

        return {
            columns: createColumns(),
            handleSubmitTransaction,
            addItem,
            deleteItem,
            rules,
            formRef,
            formItemsChoosen,
            formBorrower,
            itemOptions,
            warehouseOptions,
            Add12Filled,
            onlyAllowNumber: (value: string) => !value || /^\d+$/.test(value),
        }
    },
    layout: AuthenticatedLayout,
    components: {
        NButton,
        NInput,
        NIcon,
        NCard,
        NForm,
        NFormItem,
        TitlePage,
        Back,
        NDivider,
        NDataTable,
        NSelect,
        NTooltip,
        NBackTop
    }
})
</script>