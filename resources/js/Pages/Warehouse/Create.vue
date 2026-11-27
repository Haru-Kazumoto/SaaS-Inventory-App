<template>
    <div class="flex flex-col gap-4">
        <TitlePage title="Buat Penyimpanan Barang" subTitle="Pembuatan tempat penyimpanan barang baru " />
        <Back route="warehouse.index" />

        <n-card hoverable>
            <n-form class="grid grid-cols-3 gap-2" :rules ref="formInst" :model="form">
                <n-form-item label="Nama Tempat" path="name">
                    <n-input placeholder="" v-model:value="form.name" @input="(value) => form.name = value.toUpperCase()"></n-input>
                </n-form-item>
                <n-form-item label="Kode Tempat" path="code">
                    <n-tooltip placement="bottom" trigger="click">
                        <template #trigger>
                            <n-input placeholder="" v-model:value="form.code" @input="inputRules"></n-input>
                        </template>
                        <span>Format pembuatan kode yaitu kapital disambung underscore, contoh:  <code>NAMA_TEMPAT</code> <br> tapi ini udah otomoatis underscore kok</span>
                    </n-tooltip>
                </n-form-item>
                <n-form-item>
                    <n-button type="primary" @click="handleSubmit" :loading :disabled="loading">Submit</n-button>
                </n-form-item>
            </n-form>
        </n-card>
    </div>
</template>

<script lang="ts">
import Back from '@/Components/Back.vue';
import TitlePage from '@/Components/TitlePage.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { FormInst, FormRules, NButton, NCard, NDataTable, NForm, NFormItem, NIcon, NInput, NTooltip, useNotification } from 'naive-ui';
import Swal from 'sweetalert2';
import { defineComponent, h, ref } from 'vue'

export default defineComponent({
    props: {
        data: { type: Array, required: true }
    },
    setup() {
        const formInst = ref<FormInst | null>(null);
        const form = useForm({
            name: '',
            code: '',
        });
        const loading = ref(false);
        const notification = useNotification();

        const rules = ref<FormRules>({
            name: { required: true, trigger: ['blur'], message: 'Nama harus diisi'},
            code: { required: true, trigger: ['blur'], message: 'Kode harus diisi' },
        });

        function inputRules(value: string) {
            form.code = value.toUpperCase().replace(/\s+/g, '_');
        }

        function handleSubmit() {
            formInst.value?.validate((errors) => {
                if(errors){
                    return;
                }
                loading.value = true;

                form.post(route('warehouse.store'), {
                    preserveScroll: true,
                    onSuccess: (page) => {
                        loading.value = false;

                        notification.success({title: "Berhasil membuat", duration: 2000, closable: false});
                    },
                    onError: ()  => {
                        Swal.fire('Gagal membuat', 'Silahkan hubungi developer segera', 'error');
                    }
                });
            })
        }

        return {
            formInst,
            form,
            rules,
            loading,
            inputRules,
            handleSubmit
        }
    },
    layout: AuthenticatedLayout,
    components: {
        TitlePage,
        Back,
        NButton,
        NIcon,
        NInput,
        NCard,
        NForm,
        NFormItem,
        NTooltip
    }
})
</script>