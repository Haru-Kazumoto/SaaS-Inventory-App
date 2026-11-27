<template>
    <n-modal
        v-model:show="innerShow"
        preset="card"
        :bordered="false"
        :mask-closable="maskClosable"
        :auto-focus="autoFocus"
        :segmented="{ content: true, footer: hasFooter }"
        :style="{ width: computedWidth }"
        content-style="padding: 0"
        transform-origin="center"
        class="rounded-2xl"
        @after-leave="onAfterLeave"
    >
        <!-- HEADER -->
        <template #header>
            <div class="flex items-center gap-3">
                <!-- Optional icon slot -->
                <slot name="icon" />
                <span class="text-lg font-semibold leading-none">
                    {{ title }}
                </span>
            </div>
        </template>

        <!-- BODY -->
        <div class="p-5">
            <slot />
        </div>

        <!-- FOOTER -->
        <template v-if="hasFooter" #footer>
            <div class="flex items-center justify-end gap-2">
                <slot name="footer">
                    <n-button
                        v-if="cancelText"
                        :disabled="loading"
                        @click="onCancel"
                    >
                        {{ cancelText }}
                    </n-button>
                    <n-button type="primary" :loading="loading" @click="onOk">
                        {{ okText }}
                    </n-button>
                </slot>
            </div>
        </template>
    </n-modal>
</template>

<script setup lang="ts">
import { computed, useSlots } from "vue";
import { NModal, NButton } from "naive-ui";

const props = withDefaults(
    defineProps<{
        show: boolean;
        title?: string;
        /** preset sizes; override with `width` for custom */
        size?: "sm" | "md" | "lg" | "xl";
        /** number (px) or css string (e.g. '42rem') */
        width?: number | string;
        /** close when clicking mask */
        maskClosable?: boolean;
        /** autofocus first focusable element on open */
        autoFocus?: boolean;
        /** show top-right close button */
        dismissible?: boolean;
        /** footer texts; set empty string to hide a button */
        okText?: string;
        cancelText?: string;
        /** primary button loading state */
        loading?: boolean;
    }>(),
    {
        title: "Modal",
        size: "md",
        maskClosable: false,
        autoFocus: true,
        dismissible: true,
        okText: "Simpan",
        cancelText: "",
        loading: false,
    }
);

const emit = defineEmits<{
    (e: "update:show", v: boolean): void;
    (e: "ok"): void;
    (e: "cancel"): void;
    (e: "after-leave"): void;
}>();

const innerShow = computed({
    get: () => props.show,
    set: (v: boolean) => emit("update:show", v),
});

const widthMap: Record<NonNullable<typeof props.size>, string> = {
    sm: "420px",
    md: "600px",
    lg: "800px",
    xl: "960px",
};

const computedWidth = computed(() => {
    if (props.width !== undefined) {
        return typeof props.width === "number"
            ? `${props.width}px`
            : props.width;
    }
    return widthMap[props.size];
});

const slots = useSlots();
const hasFooter = computed(
    () => !!props.okText || !!props.cancelText || !!slots.footer
);

function close() {
    emit("update:show", false);
}
function onOk() {
    emit("ok");
}
function onCancel() {
    emit("cancel");
    close();
}
function onAfterLeave() {
    emit("after-leave");
}
</script>

<style scoped>
/* polish the built-in card look */
:deep(.n-card) {
    border-radius: 1rem;
}
:deep(.n-card-header) {
    padding: 16px 20px;
}
</style>
