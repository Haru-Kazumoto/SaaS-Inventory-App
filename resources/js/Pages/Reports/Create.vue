<template>
    <div class="flex flex-col gap-2">
        <TitlePage title="Lapor bugs" subTitle="Pembuatan laporan terhadap bugs langsung ke developer" />
        <div class="flex">
            <Back route="reports.index" />
            <n-button type="primary" class="ms-auto" strong @click="handleSubmit">SUBMIT LAPORAN</n-button>
        </div>


        <n-card hoverable>
            <n-form formRef="formRef" :rules :model="form" size="large" @submit.prevent="handleSubmit" label-placement="left" :label-width="180"
                require-mark-placement="right-hanging" :style="{
                    maxWidth: '640px',
                }">
                <n-form-item label="Judul Bugs" path="title">
                    <n-input v-model:value="form.title" placeholder="Masukkan judul issue terkait" />
                </n-form-item>

                <n-form-item label="Module Terkait" path="related_module">
                    <n-input v-model:value="form.related_module" placeholder="Contoh: Peminjaman, atau Manajemen Barang" />
                </n-form-item>

                <n-form-item label="Deskripsi Bugs" class="col-span-2" path="description">
                    <n-input v-model:value="form.description" type="textarea" placeholder="Masukkan deskripsi issue" />
                </n-form-item>

                <n-form-item label="Foto Bugs">
                    <n-upload v-model:file-list="form.images" list-type="image-card" :max="5" :default-upload="false"
                        :custom-request="handleFileUpload">
                        Click to Upload
                    </n-upload>
                </n-form-item>
            </n-form>
        </n-card>
    </div>
</template>

<script lang="ts">
import TitlePage from '@/Components/TitlePage.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { defineComponent, ref } from 'vue';
import { FormInst, NButton, NCard, NForm, NFormItem, NInput, NUpload, useMessage, UploadFileInfo, FormRules } from 'naive-ui';
import { useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import Back from '@/Components/Back.vue';

export default defineComponent({
    setup() {
        const message = useMessage();

        const formRef = ref();
        const form = useForm({
            title: '',
            related_module: '',
            description: '',
            images: [] as any[]
        });

        const rules: FormRules = {
            title: { required: true, message: "Judul harus diisi!", trigger: ['blur','change']},
            related_module: { required: true, message: "Modul terkait harus diisi!", trigger: ['blur','change']},
            description: { required: true, message: "Deskripsi harus diisi!", trigger: ['blur','change']},
        }

        // Fungsi custom upload (menyimpan file ke form.images)
        function handleFileUpload(fileList: Array<any>) {
            // Ambil hanya File aslinya dari setiap item
            form.images = fileList.map(item => item.file); // item.file adalah `UploadedFile`
            message.success(`${form.images.length} file dipilih`);
        }

        // Fungsi submit menggunakan Inertia
        const handleSubmit = () => {
            form.post(route('reports.post'), {
                preserveScroll: true,
                onSuccess: (page) => {
                    Swal.fire({
                        title: page.props.flash.success,
                        icon: "success",
                        text: "Laporan akan terkirim ke developer dan akan diproses"
                    });
                    form.reset();
                    form.images = [];
                },
                onError: () => {
                    message.error('Gagal mengirim laporan');
                }
            });
        };

        return {
            form,
            formRef,
            handleFileUpload,
            handleSubmit,
            rules
        }
    },
    components: {
        TitlePage,
        NForm,
        NFormItem,
        NButton,
        NCard,
        NUpload,
        NInput,
        Back
    },
    layout: AuthenticatedLayout,
})
</script>