<template>
    <span
        class="inline-flex items-center gap-1.5 whitespace-nowrap rounded border px-2 py-0.5 text-[11px] font-semibold"
        :class="meta.badge"
    >
        <span class="h-1.5 w-1.5 shrink-0 rounded-full" :class="meta.dot" aria-hidden="true"></span>
        {{ meta.label }}
    </span>
</template>

<script setup lang="ts">
import { computed } from "vue";
import {
    INVOICE_STATUS,
    ORGANIZATION_STATUS,
    PAYMENT_STATUS,
    SUBSCRIPTION_STATUS,
    metaOf,
} from "./platform-meta";

const props = defineProps<{
    kind: "organization" | "subscription" | "invoice" | "payment";
    status?: string | null;
}>();

const MAPS = {
    organization: ORGANIZATION_STATUS,
    subscription: SUBSCRIPTION_STATUS,
    invoice: INVOICE_STATUS,
    payment: PAYMENT_STATUS,
};

const meta = computed(() => metaOf(MAPS[props.kind], props.status));
</script>
