<template>
    <div class="flex flex-col gap-4">
        <TitlePage title="Detail Laporan Bugs" subTitle="Detail laporan bugs yang telah dibuat" />

        <Back route="reports.index" />

        <n-card hoverable title="Informasi Laporan">
            <div class="space-y-2">
                <div class="grid grid-cols-1 gap-2">
                    <div>
                        <strong>Judul:</strong> {{ report.title }}
                    </div>
                    <div>
                        <strong>Waktu Dilaporkan:</strong>
                        {{ formatDate(report.created_at) }}
                    </div>
                    <div>
                        <strong>Deskripsi:</strong>
                        <p class="whitespace-pre-line">{{ report.description }}</p>
                    </div>
                </div>


                <n-divider>Gambar Terkait</n-divider>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div v-for="image in report.images">
                        <p class="text-lg">{{ image.image_url }}</p>
                    </div>
                    <n-image v-for="image in report.images" :key="image.id" :src="image.image_url" object-fit="cover"
                        class="rounded-lg hover:scale-105 transition-transform duration-300" width="100%" />
                </div>
            </div>
        </n-card>
    </div>
</template>

<script lang="ts">
import TitlePage from '@/Components/TitlePage.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { defineComponent } from 'vue';
import { FormInst, NButton, NCard, NForm, NFormItem, NInput, NUpload, useMessage, UploadFileInfo, NDivider, NImage } from 'naive-ui';
import { useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import Back from '@/Components/Back.vue';
import { formatDate } from '@/Utils/helper-function';

export default defineComponent({
    props: {
        report: { type: Object, required: true }
    },
    setup(props) {
        console.log(props.report);
        const message = useMessage();

        const form = useForm({
            title: '',
            description: '',
            images: [] as any[]
        });

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
            formatDate,
            handleFileUpload,
            handleSubmit
        }
    },
    components: {
        TitlePage,
        NButton,
        NCard,
        NUpload,
        NImage,
        NInput,
        Back,
        NDivider
    },
    layout: AuthenticatedLayout,
})
</script>