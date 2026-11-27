<template>
    <n-modal
        v-model:show="show"
        preset="card"
        :bordered="false"
        :title="organization ? 'Ubah Profil Organisasi' : 'Tambah Organisasi'"
        class="!w-[calc(100vw-2rem)] max-w-2xl !rounded"
        :segmented="{ content: true, footer: true }"
        :mask-closable="!form.processing"
        @after-leave="form.clearErrors()"
    >
        <n-form label-placement="top" :model="form" require-mark-placement="right-hanging">
            <div class="grid grid-cols-1 gap-x-4 sm:grid-cols-2">
                <n-form-item label="Nama organisasi" required v-bind="err('name')" class="sm:col-span-2">
                    <n-input v-model:value="form.name" placeholder="cth: SMK Yatindo / PT Maju Jaya" />
                </n-form-item>

                <n-form-item label="Jenis organisasi" v-bind="err('template')" class="sm:col-span-2">
                    <div class="grid w-full grid-cols-2 gap-2 sm:grid-cols-4">
                        <button
                            v-for="t in TEMPLATES"
                            :key="t.value"
                            type="button"
                            class="rounded border px-3 py-2 text-xs font-semibold transition-colors duration-150"
                            :class="
                                form.template === t.value
                                    ? 'border-[#2A126C] bg-[#2A126C]/[0.05] text-[#2A126C] ring-1 ring-[#2A126C]'
                                    : 'border-slate-300 text-slate-600 hover:border-[#2A126C]/50'
                            "
                            @click="form.template = t.value"
                        >
                            {{ t.label }}
                        </button>
                    </div>
                </n-form-item>

                <n-form-item label="Nama pemilik / PIC" v-bind="err('owner_name')">
                    <n-input v-model:value="form.owner_name" placeholder="Nama penanggung jawab" />
                </n-form-item>
                <n-form-item label="Email pemilik" v-bind="err('owner_email')">
                    <n-input v-model:value="form.owner_email" placeholder="email@organisasi.id" inputmode="email" />
                </n-form-item>
                <n-form-item label="Telepon" v-bind="err('phone')">
                    <n-input v-model:value="form.phone" placeholder="08xx" inputmode="tel" />
                </n-form-item>
                <n-form-item label="Alamat" v-bind="err('address')">
                    <n-input v-model:value="form.address" placeholder="Opsional" />
                </n-form-item>
                <n-form-item label="Catatan internal" v-bind="err('notes')" class="sm:col-span-2">
                    <n-input
                        v-model:value="form.notes"
                        type="textarea"
                        :autosize="{ minRows: 2, maxRows: 4 }"
                        placeholder="Hanya terlihat oleh pengelola platform"
                    />
                </n-form-item>
            </div>

            <!-- Paket awal (hanya saat membuat) -->
            <div v-if="!organization" class="mt-2 rounded border border-slate-200 bg-slate-50/60 p-4">
                <p class="text-sm font-semibold text-slate-800">Paket awal</p>
                <p class="mb-3 text-xs text-slate-500">
                    Opsional. Dengan masa coba, tagihan pertama tidak dibuat sampai paket diganti.
                </p>
                <div class="grid grid-cols-1 gap-x-4 sm:grid-cols-3">
                    <n-form-item label="Paket" v-bind="err('plan_id')" class="sm:col-span-3">
                        <n-select v-model:value="form.plan_id" :options="planOptions" placeholder="Tanpa paket" clearable />
                    </n-form-item>
                    <template v-if="form.plan_id">
                        <n-form-item label="Siklus tagihan">
                            <n-select v-model:value="form.billing_cycle" :options="BILLING_CYCLES" />
                        </n-form-item>
                        <n-form-item label="Masa coba (hari)" v-bind="err('trial_days')" class="sm:col-span-2">
                            <div class="flex w-full gap-2">
                                <n-input-number v-model:value="form.trial_days" :min="0" :max="90" class="flex-1" />
                                <n-button v-for="d in [0, 14, 30]" :key="d" @click="form.trial_days = d">
                                    {{ d === 0 ? "Tanpa" : `${d} hari` }}
                                </n-button>
                            </div>
                        </n-form-item>
                        <p class="text-xs text-slate-500 sm:col-span-3">
                            <template v-if="form.trial_days > 0">
                                Masa coba {{ form.trial_days }} hari, belum ada tagihan.
                            </template>
                            <template v-else>
                                Tagihan pertama
                                <b class="text-slate-800">{{ formatIDR(selectedPrice) }}</b>
                                dibuat otomatis (jatuh tempo 7 hari).
                            </template>
                        </p>
                    </template>
                </div>
            </div>
        </n-form>

        <template #footer>
            <div class="flex justify-end gap-2">
                <n-button :disabled="form.processing" @click="show = false">Batal</n-button>
                <n-button type="primary" :loading="form.processing" @click="submit">
                    {{ organization ? "Simpan Perubahan" : "Buat Organisasi" }}
                </n-button>
            </div>
        </template>
    </n-modal>
</template>

<script setup lang="ts">
import { computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import { NButton, NForm, NFormItem, NInput, NInputNumber, NModal, NSelect } from "naive-ui";
import { BILLING_CYCLES, TEMPLATES, formatIDR } from "./platform-meta";

interface PlanOption {
    id: number;
    name: string;
    price_monthly: number;
    price_yearly: number;
}

const props = defineProps<{
    plans: PlanOption[];
    organization?: Record<string, any> | null;
}>();

const show = defineModel<boolean>("show", { default: false });

const form = useForm({
    name: "",
    template: "school",
    owner_name: "",
    owner_email: "",
    phone: "",
    address: "",
    notes: "",
    plan_id: null as number | null,
    billing_cycle: "monthly",
    trial_days: 14,
});

watch(show, (open) => {
    if (!open) return;
    const o = props.organization;
    form.defaults({
        name: o?.name ?? "",
        template: o?.template ?? "school",
        owner_name: o?.owner_name ?? "",
        owner_email: o?.owner_email ?? "",
        phone: o?.phone ?? "",
        address: o?.address ?? "",
        notes: o?.notes ?? "",
        plan_id: null,
        billing_cycle: "monthly",
        trial_days: 14,
    });
    form.reset();
});

const planOptions = computed(() =>
    props.plans.map((p) => ({
        label: `${p.name} · ${formatIDR(p.price_monthly)}/bln`,
        value: p.id,
    })),
);

const selectedPrice = computed(() => {
    const p = props.plans.find((x) => x.id === form.plan_id);
    if (!p) return 0;
    return form.billing_cycle === "yearly" ? p.price_yearly : p.price_monthly;
});

function err(field: string) {
    const msg = (form.errors as Record<string, string>)[field];
    return msg ? { validationStatus: "error" as const, feedback: msg } : {};
}

function submit() {
    const opts = { preserveScroll: true, onSuccess: () => (show.value = false) };
    if (props.organization) {
        form.put(route("platform.organizations.update", props.organization.id), opts);
    } else {
        form.post(route("platform.organizations.store"), opts);
    }
}
</script>
