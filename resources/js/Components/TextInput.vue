<template>
    <div class="flex flex-col">
        <label v-if="label" class="text-lg">{{ label }}</label>
        <n-input
            :size="size"
            :placeholder="placeholder"
            :type="type"
            v-model:value="modelValue"
            @input="handleInput"
            show-password-on="click"
        >
            <template v-if="icon" #prefix>
                <n-icon :component="icon" />
            </template>
        </n-input>
    </div>
</template>

<script lang="ts">
import { defineComponent, type Component } from 'vue';
import { NIcon, NInput } from 'naive-ui';
import { Size } from 'naive-ui/es/input/src/interface';

export default defineComponent({
    props: {
        size: { type: String as () => Size, required: false, default: 'medium' },
        placeholder: { type: String, required: false, default: '' },
        label: { type: String, required: false, default: '' },
        icon: { type: Object as () => Component | null, required: false, default: null },
        modelValue: { type: String, required: false, default: '' },
        type: { type: String as () => any, required: false, default: 'text' },
        ignoreSpace: { type: Boolean, required: false, default: false }
    },
    emits: ['update:modelValue'],
    setup(props, { emit }) {
        // Fungsi untuk menangani perubahan input
        const handleInput = (newValue: string) => {
            let valueToEmit = newValue;
            if (props.ignoreSpace) {
                valueToEmit = valueToEmit.replace(/\s+/g, ''); // Menghapus semua spasi jika aktif
            }
            emit('update:modelValue', valueToEmit);
        };

        return { handleInput };
    },
    components: {
        NInput,
        NIcon
    }
});
</script>
