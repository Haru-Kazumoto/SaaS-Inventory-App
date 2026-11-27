<template>
    <div class="flex flex-col gap-5">
        <TitlePage eyebrow="Penagihan" title="Paket Langganan" subTitle="Atur harga dan batas pemakaian. Perubahan harga hanya berlaku untuk langganan berikutnya.">
            <template #action>
                <n-button type="primary" class="w-full sm:w-auto" @click="openForm(null)">
                    <template #icon><n-icon :component="Add20Regular" /></template>
                    Tambah Paket
                </n-button>
            </template>
        </TitlePage>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            <section
                v-for="p in plans"
                :key="p.id"
                class="flex flex-col rounded border bg-white"
                :class="p.is_active ? 'border-slate-200' : 'border-dashed border-slate-300 opacity-70'"
            >
                <header class="border-b border-slate-100 px-5 py-4">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="font-mono text-[11px] text-slate-400">{{ p.code }}</p>
                            <h2 class="text-lg font-bold text-slate-900">{{ p.name }}</h2>
                        </div>
                        <span
                            class="rounded border px-1.5 py-0.5 text-[11px] font-semibold"
                            :class="p.is_active ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-50 text-slate-500'"
                        >
                            {{ p.is_active ? "Aktif" : "Nonaktif" }}
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">{{ p.description || "—" }}</p>
                </header>
                <div class="px-5 py-4">
                    <p class="text-2xl font-bold tabular-nums text-slate-900">
                        {{ p.price_monthly ? formatIDR(p.price_monthly) : "Gratis" }}
                        <span v-if="p.price_monthly" class="text-xs font-normal text-slate-400">/bulan</span>
                    </p>
                    <p v-if="p.price_yearly" class="text-xs text-slate-500">
                        {{ formatIDR(p.price_yearly) }}/tahun
                        <span v-if="yearlySaving(p) > 0" class="font-semibold text-emerald-600">(hemat {{ yearlySaving(p) }}%)</span>
                    </p>
                    <ul class="mt-4 flex flex-col gap-1.5 text-sm text-slate-700">
                        <li v-for="l in limits(p)" :key="l.label" class="flex items-center gap-2">
                            <n-icon :component="Checkmark16Filled" class="text-[#2A126C]" />
                            <b class="tabular-nums">{{ l.value }}</b> {{ l.label }}
                        </li>
                    </ul>
                </div>
                <footer class="mt-auto flex items-center justify-between border-t border-slate-100 px-5 py-3">
                    <span class="text-xs text-slate-500"><b class="tabular-nums text-slate-900">{{ p.subscribers }}</b> organisasi berlangganan</span>
                    <n-button size="small" @click="openForm(p)">
                        <template #icon><n-icon :component="Edit20Regular" /></template>
                        Ubah
                    </n-button>
                </footer>
            </section>
        </div>

        <n-modal
            v-model:show="showForm"
            preset="card"
            :bordered="false"
            :title="editing ? `Ubah Paket ${editing.name}` : 'Tambah Paket'"
            class="!w-[calc(100vw-2rem)] max-w-xl !rounded"
            :segmented="{ content: true, footer: true }"
            :mask-closable="!form.processing"
        >
            <n-form label-placement="top" :model="form">
                <div class="grid grid-cols-1 gap-x-4 sm:grid-cols-2">
                    <n-form-item label="Nama paket" v-bind="err('name')">
                        <n-input v-model:value="form.name" placeholder="cth: Pro" />
                    </n-form-item>
                    <n-form-item label="Kode" v-bind="err('code')">
                        <n-input
                            :value="form.code"
                            placeholder="cth: pro"
                            @update:value="(v) => (form.code = v.toLowerCase().replace(/[^a-z0-9_-]/g, '-'))"
                        />
                    </n-form-item>
                    <n-form-item label="Deskripsi" v-bind="err('description')" class="sm:col-span-2">
                        <n-input v-model:value="form.description" placeholder="Untuk siapa paket ini" />
                    </n-form-item>
                    <n-form-item label="Harga bulanan" v-bind="err('price_monthly')">
                        <n-input :value="fmt(form.price_monthly)" inputmode="numeric" @update:value="(v) => (form.price_monthly = num(v))">
                            <template #prefix><span class="text-xs text-slate-400">Rp</span></template>
                        </n-input>
                    </n-form-item>
                    <n-form-item label="Harga tahunan" v-bind="err('price_yearly')">
                        <div class="flex w-full flex-col gap-1">
                            <n-input :value="fmt(form.price_yearly)" inputmode="numeric" @update:value="(v) => (form.price_yearly = num(v))">
                                <template #prefix><span class="text-xs text-slate-400">Rp</span></template>
                            </n-input>
                            <button
                                v-if="form.price_monthly"
                                type="button"
                                class="self-start text-[11px] font-semibold text-[#2A126C] hover:underline"
                                @click="form.price_yearly = form.price_monthly * 10"
                            >
                                Isi 10× bulanan (gratis 2 bulan)
                            </button>
                        </div>
                    </n-form-item>
                </div>

                <p class="mb-2 text-xs font-semibold text-slate-600">Batas pemakaian <span class="font-normal text-slate-400">(kosongkan = tanpa batas)</span></p>
                <div class="grid grid-cols-3 gap-3">
                    <n-form-item label="Unit" v-bind="err('max_units')">
                        <n-input-number v-model:value="form.max_units" :min="1" placeholder="∞" clearable />
                    </n-form-item>
                    <n-form-item label="Pengguna" v-bind="err('max_users')">
                        <n-input-number v-model:value="form.max_users" :min="1" placeholder="∞" clearable />
                    </n-form-item>
                    <n-form-item label="Barang" v-bind="err('max_products')">
                        <n-input-number v-model:value="form.max_products" :min="1" placeholder="∞" clearable />
                    </n-form-item>
                </div>

                <div class="flex items-center justify-between rounded border border-slate-200 px-3 py-2">
                    <div>
                        <p class="text-sm font-medium text-slate-800">Paket aktif</p>
                        <p class="text-[11px] text-slate-500">Paket nonaktif tidak bisa dipilih untuk langganan baru.</p>
                    </div>
                    <n-switch v-model:value="form.is_active" />
                </div>
            </n-form>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <n-button :disabled="form.processing" @click="showForm = false">Batal</n-button>
                    <n-button type="primary" :loading="form.processing" @click="submit">Simpan Paket</n-button>
                </div>
            </template>
        </n-modal>
    </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import { NButton, NForm, NFormItem, NIcon, NInput, NInputNumber, NModal, NSwitch } from "naive-ui";
import { Add20Regular, Checkmark16Filled, Edit20Regular } from "@vicons/fluent";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TitlePage from "@/Components/TitlePage.vue";
import { formatIDR } from "@/Components/Feature/Platform/platform-meta";
import { useFlash } from "@/Composables/useFlash";

defineOptions({ layout: AuthenticatedLayout });

interface Plan {
    id: number;
    code: string;
    name: string;
    description?: string | null;
    price_monthly: number;
    price_yearly: number;
    max_units: number | null;
    max_users: number | null;
    max_products: number | null;
    is_active: boolean;
    sort_order: number;
    subscribers: number;
}

defineProps<{ plans: Plan[] }>();
useFlash();

const limits = (p: Plan) => [
    { label: "unit", value: p.max_units ?? "Tanpa batas" },
    { label: "pengguna", value: p.max_users ?? "Tanpa batas" },
    { label: "barang", value: p.max_products ?? "Tanpa batas" },
];

const yearlySaving = (p: Plan) =>
    p.price_monthly ? Math.round((1 - p.price_yearly / (p.price_monthly * 12)) * 100) : 0;

const showForm = ref(false);
const editing = ref<Plan | null>(null);

const form = useForm({
    code: "",
    name: "",
    description: "",
    price_monthly: 0,
    price_yearly: 0,
    max_units: null as number | null,
    max_users: null as number | null,
    max_products: null as number | null,
    is_active: true as boolean,
    sort_order: 0,
});

function openForm(p: Plan | null) {
    editing.value = p;
    form.clearErrors();
    form.code = p?.code ?? "";
    form.name = p?.name ?? "";
    form.description = p?.description ?? "";
    form.price_monthly = p?.price_monthly ?? 0;
    form.price_yearly = p?.price_yearly ?? 0;
    form.max_units = p?.max_units ?? null;
    form.max_users = p?.max_users ?? null;
    form.max_products = p?.max_products ?? null;
    form.is_active = p?.is_active ?? true;
    form.sort_order = p?.sort_order ?? 0;
    showForm.value = true;
}

const fmt = (n: number) => (n ? new Intl.NumberFormat("id-ID").format(n) : "");
const num = (v: string) => Number(v.replace(/\D/g, "")) || 0;

function err(field: string) {
    const msg = (form.errors as Record<string, string>)[field];
    return msg ? { validationStatus: "error" as const, feedback: msg } : {};
}

function submit() {
    const opts = { preserveScroll: true, onSuccess: () => (showForm.value = false) };
    editing.value
        ? form.put(route("platform.plans.update", editing.value.id), opts)
        : form.post(route("platform.plans.store"), opts);
}
</script>
