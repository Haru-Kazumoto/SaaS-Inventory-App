<template>
    <n-modal
        v-model:show="show"
        preset="card"
        :bordered="false"
        title="Ganti Paket"
        class="!w-[calc(100vw-2rem)] max-w-xl !rounded"
        :segmented="{ content: true, footer: true }"
        :mask-closable="!form.processing"
    >
        <div class="flex flex-col gap-4">
            <!-- siklus -->
            <div class="grid grid-cols-2 overflow-hidden rounded border border-slate-300" role="radiogroup">
                <button
                    v-for="c in BILLING_CYCLES"
                    :key="c.value"
                    type="button"
                    role="radio"
                    :aria-checked="form.billing_cycle === c.value"
                    class="py-2 text-sm font-semibold transition-colors duration-150 first:border-r first:border-slate-300"
                    :class="form.billing_cycle === c.value ? 'bg-[#2A126C] text-white' : 'bg-white text-slate-600 hover:bg-slate-50'"
                    @click="form.billing_cycle = c.value"
                >
                    {{ c.label }}
                </button>
            </div>

            <!-- paket -->
            <div class="flex flex-col gap-2" role="radiogroup" aria-label="Pilih paket">
                <button
                    v-for="p in plans"
                    :key="p.id"
                    type="button"
                    role="radio"
                    :aria-checked="form.plan_id === p.id"
                    class="flex items-center justify-between gap-3 rounded border px-4 py-3 text-left transition-colors duration-150"
                    :class="
                        form.plan_id === p.id
                            ? 'border-[#2A126C] bg-[#2A126C]/[0.04] ring-1 ring-[#2A126C]'
                            : 'border-slate-200 hover:border-[#2A126C]/50'
                    "
                    @click="form.plan_id = p.id"
                >
                    <span class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-slate-900">{{ p.name }}</span>
                        <span
                            v-if="p.id === currentPlanId"
                            class="rounded bg-slate-100 px-1.5 text-[10px] font-semibold text-slate-500"
                            >Paket sekarang</span
                        >
                    </span>
                    <span class="text-sm font-bold tabular-nums text-slate-900">
                        {{ formatIDR(priceOf(p)) }}
                        <span class="text-[11px] font-normal text-slate-400">
                            /{{ form.billing_cycle === "yearly" ? "thn" : "bln" }}
                        </span>
                    </span>
                </button>
            </div>

            <div class="grid grid-cols-1 gap-3 rounded border border-slate-200 bg-slate-50/60 p-3 sm:grid-cols-2">
                <label class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-slate-600">Masa coba (hari)</span>
                    <n-input-number v-model:value="form.trial_days" :min="0" :max="90" />
                </label>
                <label class="flex items-center justify-between gap-2 sm:flex-col sm:items-start">
                    <span class="text-xs font-medium text-slate-600">Buat tagihan periode pertama</span>
                    <n-switch v-model:value="form.issue_invoice" :disabled="form.trial_days > 0 || selectedPrice === 0" />
                </label>
            </div>

            <p class="text-xs leading-relaxed text-slate-500">
                Langganan berjalan dibatalkan dan langganan baru dimulai hari ini.
                <template v-if="form.trial_days > 0">Masa coba {{ form.trial_days }} hari tanpa tagihan.</template>
                <template v-else-if="selectedPrice > 0 && form.issue_invoice">
                    Tagihan <b class="text-slate-800">{{ formatIDR(selectedPrice) }}</b> dibuat otomatis, jatuh tempo 7 hari.
                </template>
                <template v-else-if="selectedPrice > 0">Tidak ada tagihan yang dibuat.</template>
            </p>

            <p v-if="form.errors.plan_id || (form.errors as any).billing" class="text-xs text-rose-600">
                {{ form.errors.plan_id || (form.errors as any).billing }}
            </p>
        </div>

        <template #footer>
            <div class="flex justify-end gap-2">
                <n-button :disabled="form.processing" @click="show = false">Batal</n-button>
                <n-button type="primary" :loading="form.processing" :disabled="!form.plan_id" @click="submit">
                    Ganti Paket
                </n-button>
            </div>
        </template>
    </n-modal>
</template>

<script setup lang="ts">
import { computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import { NButton, NInputNumber, NModal, NSwitch } from "naive-ui";
import { BILLING_CYCLES, formatIDR } from "./platform-meta";

interface PlanOption {
    id: number;
    name: string;
    price_monthly: number;
    price_yearly: number;
}

const props = defineProps<{
    organizationId: number;
    plans: PlanOption[];
    currentPlanId?: number | null;
    currentCycle?: string | null;
}>();

const show = defineModel<boolean>("show", { default: false });

const form = useForm({
    plan_id: null as number | null,
    billing_cycle: "monthly",
    trial_days: 0,
    issue_invoice: true,
});

watch(show, (open) => {
    if (!open) return;
    form.clearErrors();
    form.plan_id = props.currentPlanId ?? props.plans[0]?.id ?? null;
    form.billing_cycle = props.currentCycle ?? "monthly";
    form.trial_days = 0;
    form.issue_invoice = true;
});

const priceOf = (p: PlanOption) => (form.billing_cycle === "yearly" ? p.price_yearly : p.price_monthly);
const selectedPrice = computed(() => {
    const p = props.plans.find((x) => x.id === form.plan_id);
    return p ? priceOf(p) : 0;
});

function submit() {
    form.post(route("platform.organizations.subscription", props.organizationId), {
        preserveScroll: true,
        onSuccess: () => (show.value = false),
    });
}
</script>
