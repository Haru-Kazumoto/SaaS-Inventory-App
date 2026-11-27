<template>
    <div class="flex flex-col gap-4">
        <div v-if="$page.props.auth.user.role.code === 'SUPER'">
            <TitlePage title="Proses Pengajuan" subTitle="Proses Pengajuan Barang Per Jurusan" />
        </div>
        <div v-else>
            <TitlePage title="Detail Pengajuan" subTitle="Detail Pengajuan Barang" />
        </div>

        <div class="flex">
            <Back route="request-item.index" />
        </div>

        <n-card hoverable class="h-[36rem]">
            <n-form size="large" formRef="formRef" :model="form">
                <div class="grid grid-cols-3 gap-4">
                    <n-form-item label="Nama Barang" path="brand_name">
                        <n-input placeholder="" v-model:value="form.brand_name"
                            @input="(value) => form.brand_name = value.toUpperCase()"></n-input>
                    </n-form-item>
                    <n-form-item label="Jumlah Pengajuan" path="quantity">
                        <n-input placeholder="" v-model:value="form.quantity"></n-input>
                    </n-form-item>
                    <n-form-item label="Referensi Link Olshop Barang">
                        <n-input placeholder="" v-model:value="form.link_shop"></n-input>
                    </n-form-item>
                    <n-form-item label="Alasan Pengajuan" class="col-span-2" path="reason_request">
                        <n-input placeholder="" type="textarea" v-model:value="form.reason_request"></n-input>
                    </n-form-item>
                    <n-form-item label="Gambar Barang" v-if="form.image_item">
                        <n-image :src="form.image_item" width="200"></n-image>
                    </n-form-item>
                </div>
            </n-form>
        </n-card>
    </div>
</template>

<script lang="ts">
import Back from '@/Components/Back.vue';
import TitlePage from '@/Components/TitlePage.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { NButton, NCard, NForm, NFormItem, NImage, NInput, NStep, NSteps, useNotification } from 'naive-ui';
import Swal from 'sweetalert2';
import { defineComponent, ref } from 'vue';

type RequestItem = {
    brand_name: string;
    quantity: number;
    link_shop: string;
    reason_request: string;
    image_item: any;
    current_step: number;
    current_step_status: string;
    images: [];
}

export default defineComponent({
    props: {
        data: { type: Object, required: true }
    },
    setup(props) {
        const notification = useNotification();
        const form = ref<RequestItem>({
            brand_name: props.data.brand_name,
            quantity: props.data.quantity,
            link_shop: props.data.link_shop,
            reason_request: props.data.reason_request,
            image_item: props.data.image_item,
            current_step: props.data.current_step,
            current_step_status: props.data.current_step_status,
            images: []
        });

        const formProcess = useForm({
            current_step: props.data.current_step,
            current_step_status: props.data.current_step_status
        });

        function handleProcessRequest() {
            processRequestStatus();
        }

        function processRequestStatus() {
            Swal.fire({
                title: "Proses pengajuan barang ini?",
                text: "Setelah proses pengajuan diproses status tidak akan bisa diubah kembali",
                icon: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'red',
                confirmButtonText: 'PROSES',
                cancelButtonText: 'BATALKAN',
                showDenyButton: true
            }).then(result => {
                if (result.isConfirmed) {
                    const next_step = formProcess.current_step + 1;
                    formProcess.current_step = next_step;
                    formProcess.current_step_status = 'process';

                    formProcess.patch(route('request-item.process', props.data.id), {
                        onSuccess: (page) => {
                            notification.success({
                                title: page.props.flash.success,
                                duration: 2500,
                                closable: false
                            });
                        },
                        onError: () => {
                            notification.error({
                                title: "Gagal memproses ajuan ",
                                duration: 2500,
                                closable: false
                            });
                        }
                    })
                } else if (result.isDismissed) {
                    formProcess.current_step_status = 'error';

                    formProcess.patch(route('request-item.process', props.data.id), {
                        onSuccess: (page) => {
                            notification.success({
                                title: page.props.flash.success,
                                duration: 2500,
                                closable: false
                            });
                        },
                        onError: () => {
                            notification.error({
                                title: "Gagal memproses ajuan ",
                                duration: 2500,
                                closable: false
                            });
                        }
                    })
                }
            });
        }

        // Fungsi untuk submit form ke backend
        function submitForm() {
            formProcess.post(route('request-item.process'), {
                onSuccess: (page) => {
                    notification.success({
                        title: page.props.flash.success,
                        duration: 2500,
                        closable: false
                    });
                },
                onError: () => {
                    notification.error({
                        title: "Pengajuan gagal diproses",
                        duration: 2500,
                        closable: false
                    });
                }
            });
        }


        return {
            form,
            handleProcessRequest,
            formProcess
        }
    },
    layout: AuthenticatedLayout,
    components: {
        TitlePage,
        NButton,
        NForm,
        NFormItem,
        NImage,
        NInput,
        NCard,
        Back
    }
})
</script>

<style scoped></style>