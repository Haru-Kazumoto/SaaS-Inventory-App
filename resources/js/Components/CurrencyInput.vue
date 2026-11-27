<template>
    <n-input size="large" placeholder="" v-model:value="inputValue" @update:value="handleInput">
        <template #prefix> Rp </template>
    </n-input>
</template>

<script>
import { ref, computed, watch } from 'vue';
import { NInput } from 'naive-ui';

export default {
    components: { NInput },
    props: {
        modelValue: {
            type: [String, Number],
            default: '',
        },
    },
    emits: ['update:modelValue'],
    setup(props, { emit }) {
        const inputValue = ref(props.modelValue); // Gunakan ref agar nilai tetap reaktif

        // Fungsi untuk memformat angka dengan separator ribuan
        const formatCurrency = (value) => {
            if (!value) return '';
            return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        };

        // Watch untuk update jika modelValue berubah dari luar
        watch(() => props.modelValue, (newVal) => {
            inputValue.value = formatCurrency(newVal);
        }, { immediate: true, deep: true });

        // Fungsi untuk menghapus separator dan mengupdate nilai asli
        const handleInput = (value) => {
            const rawValue = value.replace(/\./g, ''); // Hapus titik separator
            emit('update:modelValue', rawValue); // Emit nilai asli
            inputValue.value = formatCurrency(rawValue); // Perbarui tampilan input
        };

        return { inputValue, handleInput };
    },
};
</script>
