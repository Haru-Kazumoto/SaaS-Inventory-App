<template>
    <div class="flex flex-col gap-3">
        <TitlePage title="Detail Transaksi" subTitle="Informasi detail transaksi" />

        <div class="flex items-center">
            <Back route="transaction.index" />
        </div>

        <n-card hoverable title="Informasi Peminjam">
            <n-form ref="formRef" size="large" :rules="rules" :model="formBorrower">
                <div class="grid grid-cols-4 gap-3 ">
                    <n-form-item label="Nama Panjang Peminjam" path="fullname" class="col-span-1">
                        <n-input readonly placeholder="" v-model:value="formBorrower.fullname"></n-input>
                    </n-form-item>
                    <n-form-item label="Nomor Handphone Peminjam" path="phone" class="col-span-1">
                        <n-input readonly placeholder="" v-model:value="formBorrower.phone"></n-input>
                    </n-form-item>
                    <n-form-item label="Ruangan Peminjam" path="room_code" class="col-span-1">
                        <n-input readonly placeholder="" v-model:value="formBorrower.room_code"></n-input>
                    </n-form-item>
                    <n-form-item label="Guru Pengawas" path="teacher_name" class="col-span-1">
                        <n-input readonly placeholder="" v-model:value="formBorrower.teacher_name"></n-input>
                    </n-form-item>
                    <n-form-item label="Waktu Dipinjam" path="teacher_name" class="col-span-1">
                        <n-input readonly placeholder="" v-model:value="formBorrower.out_date"></n-input>
                    </n-form-item>
                    <n-form-item label="Deskripsi" path="description" class="col-span-2">
                        <n-input readonly placeholder="" type="textarea"
                            v-model:value="formBorrower.description"></n-input>
                    </n-form-item>
                </div>
            </n-form>
            <div class="flex flex-col gap-4">
                <span class="text-lg font-medium">Input Barang Dipinjam</span>
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
import { formatDate } from '@/Utils/helper-function';
import { defineComponent, ref, watch, onMounted } from 'vue';

export default defineComponent({
    props: {
        data: { type: Object, required: true }
    },
    setup(props) {
        const formRef = ref<FormInst | null>(null);
        const notification = useNotification();
        const formItemsChoosen = ref({
            item_name: null as unknown as string,
            code_item: null as unknown as string,
        });
        const formBorrower = useForm({
            fullname: null as unknown as string,
            phone: null as unknown as string,
            room_code: null as unknown as string,
            teacher_name: null as unknown as string,
            description: null as unknown as string,
            out_date: null as unknown as string,
            item_choosens: [] as any[]
            // action passing out 
        });
        const rules = ref({
            fullname: { required: true, message: "Nama panjang peminjam harus diisi!", trigger: 'blur' },
            phone: { required: true, message: "Nomor handphone peminjam harus diisi!", trigger: 'blur' },
            room_code: { required: true, message: "Ruangan peminjam harus diisi!", trigger: 'blur' },
            teacher_name: { required: false, message: "Guru Pengawas harus diisi!", trigger: 'blur' },
        });

        // Fungsi untuk mengisi form dari props
        function fillForm() {
            if (props.data && props.data.borrower) {
                formBorrower.fullname = props.data.borrower.fullname;
                formBorrower.phone = props.data.borrower.phone;
                formBorrower.room_code = props.data.borrower.room_name;
                formBorrower.teacher_name = props.data.borrower.teacher_name;
                formBorrower.description = props.data.description;
                formBorrower.out_date = formatDate(props.data.out_date, true);

                formBorrower.item_choosens = props.data.items_out.map((data: any) => ({
                    code_item: data.partial_item?.code_item ?? null,
                    item_name: data.partial_item?.item?.name ?? null,
                    description: data.description ?? (null as unknown as string), // Jika sudah ada status_in, gunakan
                    status_in: data.status_in ?? (data.partial_item?.item?.category === "CONSUMABLE" ? "APPROVED" : (null as unknown as string)),
                    category: data.partial_item?.item?.category
                }));


            }
        };

        // Jalankan saat pertama kali halaman dimuat
        onMounted(() => {
            fillForm();
        });

        // Watch untuk menangani perubahan data setelah komponen dimuat
        watch(() => props.data, (newData) => {
            if (newData) {
                fillForm();
            }
        }, { deep: true });

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

            // Tambahkan item jika tidak duplikat
            formBorrower.item_choosens.push({ ...formItemsChoosen.value });

            // Reset form
            formItemsChoosen.value = {
                item_name: null as unknown as string,
                code_item: null as unknown as string,
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
            formBorrower.put(route('transaction.update', props.data.id), {
                onSuccess: (page) => {
                    formBorrower.reset();
                    Swal.fire(page.props.flash.success, '', 'success');
                },
                onError: () => {
                    Swal.fire('Gagal mengirim data!', '', 'error');
                }
            });
        }

        return {
            columns: createColumns(),
            handleSubmitTransaction,
            addItem,
            deleteItem,
            rules,
            formRef,
            formItemsChoosen,
            formBorrower,
            Add12Filled
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