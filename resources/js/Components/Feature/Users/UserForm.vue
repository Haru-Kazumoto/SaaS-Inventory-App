<template>
    <div class="grid grid-cols-1 gap-5 pb-24 lg:grid-cols-3 lg:pb-0">
        <n-form
            ref="formRef"
            :model="form"
            :rules="rules"
            label-placement="top"
            require-mark-placement="right-hanging"
            class="flex flex-col gap-5 lg:col-span-2"
        >
            <!-- 1. PROFIL -->
            <FormSection :step="1" title="Profil" description="Identitas dan kontak anggota.">
                <div class="flex flex-col gap-5 sm:flex-row">
                    <div class="flex shrink-0 flex-col items-center gap-2 sm:w-32">
                        <input ref="fileInput" type="file" accept=".png,.jpg,.jpeg" class="hidden" @change="onFilePicked" />
                        <button
                            type="button"
                            class="group relative flex h-24 w-24 items-center justify-center overflow-hidden rounded-full border border-slate-200 bg-slate-50"
                            :aria-label="avatarSrc ? 'Ganti foto' : 'Pilih foto'"
                            @click="pickFile"
                        >
                            <img v-if="avatarSrc" :src="avatarSrc" alt="" class="h-full w-full object-cover" />
                            <span v-else class="text-2xl font-bold text-slate-400">{{ initials }}</span>
                            <span class="absolute inset-0 flex items-center justify-center bg-slate-900/50 text-white opacity-0 transition-opacity duration-150 group-hover:opacity-100">
                                <n-icon :component="Camera20Regular" size="22" />
                            </span>
                        </button>
                        <n-button size="tiny" @click="pickFile">{{ avatarSrc ? "Ganti" : "Pilih foto" }}</n-button>
                        <span class="text-center text-[11px] leading-snug text-slate-400">Opsional · persegi, &lt; 2 MB</span>
                    </div>

                    <div class="grid flex-1 grid-cols-1 gap-x-4 sm:grid-cols-2">
                        <n-form-item label="Nama lengkap" path="fullname" class="sm:col-span-2" v-bind="err('fullname')">
                            <n-input v-model:value="form.fullname" placeholder="cth: Budi Santoso" />
                        </n-form-item>
                        <n-form-item label="Nomor telepon" path="phone" v-bind="err('phone')">
                            <n-input :value="form.phone" placeholder="0812xxxxxxxx" inputmode="tel" @update:value="(v) => (form.phone = v.replace(/[^\d+]/g, ''))" />
                        </n-form-item>
                        <n-form-item label="Email" path="email" v-bind="err('email')">
                            <n-input v-model:value="form.email" placeholder="nama@organisasi.id" inputmode="email" />
                        </n-form-item>
                    </div>
                </div>
            </FormSection>

            <!-- 2. AKUN & AKSES -->
            <FormSection :step="2" title="Akun & Akses" description="Data login dan hak akses di organisasi.">
                <div class="grid grid-cols-1 gap-x-4 sm:grid-cols-2">
                    <n-form-item label="Username" path="username" v-bind="err('username')">
                        <n-input
                            :value="form.username"
                            placeholder="cth: budi.tjkt"
                            autocomplete="off"
                            @update:value="(v) => (form.username = v.replace(/\s+/g, '').toLowerCase())"
                        >
                            <template #prefix><span class="text-slate-400">@</span></template>
                        </n-input>
                    </n-form-item>

                    <n-form-item :label="mode === 'edit' ? 'Password baru' : 'Password'" path="password" v-bind="err('password')">
                        <div class="flex w-full flex-col gap-1">
                            <div class="flex gap-2">
                                <n-input
                                    v-model:value="form.password"
                                    type="password"
                                    show-password-on="click"
                                    autocomplete="new-password"
                                    :placeholder="mode === 'edit' ? 'Kosongkan jika tidak diganti' : 'Minimal 8 karakter'"
                                />
                                <n-button title="Buat password acak" @click="generatePassword">
                                    <template #icon><n-icon :component="Key20Regular" /></template>
                                </n-button>
                            </div>
                            <span v-if="generated" class="text-[11px] text-emerald-700">
                                Password dibuat: <b class="font-mono">{{ form.password }}</b> — catat dan berikan ke anggota.
                            </span>
                            <span v-else-if="mode === 'edit'" class="text-[11px] text-slate-400">Biarkan kosong untuk tetap memakai password lama.</span>
                        </div>
                    </n-form-item>

                    <!-- Peran organisasi -->
                    <n-form-item label="Peran" path="organization_role" class="sm:col-span-2" v-bind="err('organization_role')">
                        <div class="grid w-full grid-cols-1 gap-2 sm:grid-cols-3" role="radiogroup">
                            <button
                                v-for="r in roleOptions"
                                :key="r.value"
                                type="button"
                                role="radio"
                                :aria-checked="form.organization_role === r.value"
                                class="flex flex-col items-start gap-0.5 rounded border p-3 text-left transition-colors duration-150"
                                :class="
                                    form.organization_role === r.value
                                        ? 'border-[#2A126C] bg-[#2A126C]/[0.04] ring-1 ring-[#2A126C]'
                                        : 'border-slate-300 hover:border-[#2A126C]/50'
                                "
                                @click="form.organization_role = r.value as MemberFormState['organization_role']"
                            >
                                <span class="text-sm font-semibold text-slate-900">{{ r.label }}</span>
                                <span class="text-[11px] leading-snug text-slate-500">{{ r.hint }}</span>
                            </button>
                        </div>
                    </n-form-item>

                    <!-- Unit & peran per unit (untuk anggota) -->
                    <div v-if="form.organization_role === 'member'" class="flex flex-col gap-2 sm:col-span-2">
                        <span class="text-sm text-slate-700">{{ t("unit") }} & peran *</span>
                        <div
                            v-for="(m, i) in form.memberships"
                            :key="i"
                            class="grid grid-cols-12 items-center gap-2 rounded border border-slate-200 p-2"
                        >
                            <n-select v-model:value="m.unit_id" :options="unitOptionsFor(i)" :placeholder="`Pilih ${t('unit').toLowerCase()}`" class="col-span-12 sm:col-span-6" />
                            <div class="col-span-10 grid h-[34px] grid-cols-3 overflow-hidden rounded border border-slate-300 sm:col-span-5" role="radiogroup">
                                <button
                                    v-for="ur in UNIT_ROLES"
                                    :key="ur.value"
                                    type="button"
                                    role="radio"
                                    :aria-checked="m.role === ur.value"
                                    :title="ur.hint"
                                    class="border-r border-slate-300 text-xs font-semibold transition-colors duration-150 last:border-r-0"
                                    :class="m.role === ur.value ? 'bg-[#2A126C] text-white' : 'bg-white text-slate-500 hover:bg-slate-50'"
                                    @click="m.role = ur.value"
                                >
                                    {{ ur.label }}
                                </button>
                            </div>
                            <n-button quaternary class="col-span-2 sm:col-span-1" :disabled="form.memberships.length === 1" aria-label="Hapus unit" @click="form.memberships.splice(i, 1)">
                                <template #icon><n-icon :component="Delete20Regular" /></template>
                            </n-button>
                        </div>
                        <button
                            v-if="form.memberships.length < units.length"
                            type="button"
                            class="flex items-center justify-center gap-1 rounded border border-dashed border-slate-300 py-2 text-xs font-semibold text-slate-600 hover:border-[#2A126C] hover:text-[#2A126C]"
                            @click="form.memberships.push({ unit_id: null, role: 'staff' })"
                        >
                            <n-icon :component="Add20Regular" /> Tambah {{ t("unit").toLowerCase() }}
                        </button>
                        <p class="text-[11px] leading-relaxed text-slate-500">
                            <b>Admin</b>: kelola barang, stok, data master, pengajuan · <b>Staf</b>: catat peminjaman &amp; pengembalian · <b>Viewer</b>: hanya melihat.
                        </p>
                        <p v-if="err('memberships').feedback" class="text-xs text-rose-600">{{ err("memberships").feedback }}</p>
                    </div>
                </div>
            </FormSection>
        </n-form>

        <!-- PRATINJAU -->
        <aside class="hidden lg:block">
            <section class="sticky top-20 rounded border border-slate-200 bg-white">
                <header class="border-b border-slate-100 px-5 py-4">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">Pratinjau akun</p>
                </header>
                <div class="flex flex-col items-center gap-2 px-5 py-6 text-center">
                    <div class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-full border border-slate-200 bg-slate-50">
                        <img v-if="avatarSrc" :src="avatarSrc" alt="" class="h-full w-full object-cover" />
                        <span v-else class="text-lg font-bold text-slate-400">{{ initials }}</span>
                    </div>
                    <p class="max-w-full truncate text-base font-bold text-slate-900">{{ form.fullname || "Nama anggota" }}</p>
                    <p class="text-xs text-slate-500">@{{ form.username || "username" }}</p>
                    <span class="mt-1 rounded border border-[#2A126C]/20 bg-[#2A126C]/[0.06] px-1.5 py-0.5 text-[11px] font-semibold text-[#2A126C]">
                        {{ roleOptions.find((r) => r.value === form.organization_role)?.label }}
                    </span>
                    <div v-if="form.organization_role === 'member'" class="mt-1 flex flex-wrap justify-center gap-1">
                        <span
                            v-for="m in form.memberships.filter((x) => x.unit_id)"
                            :key="m.unit_id!"
                            class="rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-[11px] text-slate-600"
                        >
                            {{ unitName(m.unit_id) }} · {{ UNIT_ROLES.find((u) => u.value === m.role)?.label }}
                        </span>
                    </div>
                </div>
                <div class="flex flex-col gap-2 border-t border-slate-100 p-5">
                    <n-button type="primary" size="large" block :loading="processing" @click="submit">
                        <template #icon><n-icon :component="Save20Regular" /></template>
                        {{ mode === "edit" ? "Simpan Perubahan" : "Tambah Anggota" }}
                    </n-button>
                    <n-button block :disabled="processing" @click="$emit('cancel')">Batal</n-button>
                </div>
            </section>
        </aside>

        <div
            class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 px-4 py-3 backdrop-blur lg:hidden"
            style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom))"
        >
            <div class="mx-auto flex max-w-3xl justify-end gap-2">
                <n-button :disabled="processing" @click="$emit('cancel')">Batal</n-button>
                <n-button type="primary" :loading="processing" @click="submit">
                    {{ mode === "edit" ? "Simpan Perubahan" : "Tambah Anggota" }}
                </n-button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, ref } from "vue";
import { NButton, NForm, NFormItem, NIcon, NInput, NSelect, useMessage, type FormInst, type FormRules } from "naive-ui";
import { Add20Regular, Camera20Regular, Delete20Regular, Key20Regular, Save20Regular } from "@vicons/fluent";
import FormSection from "@/Components/FormSection.vue";
import { useTerms } from "@/Composables/useTerms";
import type { MemberFormState } from "./types";

const props = defineProps<{
    mode: "create" | "edit";
    form: MemberFormState;
    units: Array<{ id: number; name: string }>;
    canAssignOwner: boolean;
    initialAvatar?: string | null;
    processing?: boolean;
}>();

const emit = defineEmits<{ (e: "submit"): void; (e: "cancel"): void }>();

const { t } = useTerms();
const message = useMessage();
const formRef = ref<FormInst | null>(null);

const UNIT_ROLES = [
    { value: "admin", label: "Admin", hint: "Kelola barang, stok, data master, pengajuan" },
    { value: "staff", label: "Staf", hint: "Catat peminjaman & pengembalian" },
    { value: "viewer", label: "Viewer", hint: "Hanya melihat" },
] as const;

const roleOptions = computed(() => [
    ...(props.canAssignOwner ? [{ value: "owner", label: "Pemilik", hint: "Semua akses + penagihan" }] : []),
    { value: "admin", label: "Admin organisasi", hint: `Kelola semua ${t("unit_plural").toLowerCase()}, anggota & data master` },
    { value: "member", label: `Anggota ${t("unit").toLowerCase()}`, hint: `Akses sesuai peran di ${t("unit").toLowerCase()} tertentu` },
]);

const unitName = (id: number | null) => props.units.find((u) => u.id === id)?.name ?? "-";

/** unit yang belum dipilih di baris lain */
function unitOptionsFor(index: number) {
    const taken = props.form.memberships.filter((_, i) => i !== index).map((m) => m.unit_id);
    return props.units.filter((u) => !taken.includes(u.id)).map((u) => ({ label: u.name, value: u.id }));
}

const rules = computed<FormRules>(() => ({
    fullname: [
        { required: true, message: "Nama wajib diisi", trigger: "blur" },
        { min: 3, message: "Nama minimal 3 karakter", trigger: "blur" },
    ],
    email: { type: "email", message: "Format email tidak valid", trigger: "blur" },
    username: [
        { required: true, message: "Username wajib diisi", trigger: "blur" },
        { min: 4, message: "Username minimal 4 karakter", trigger: "blur" },
    ],
    password: {
        trigger: "blur",
        validator: (_r: unknown, v: string | null) => {
            if (!v) return props.mode === "edit" ? true : new Error("Password wajib diisi");
            return v.length >= 8 ? true : new Error("Password minimal 8 karakter");
        },
    },
}));

function err(field: string) {
    const msg = props.form.errors?.[field];
    return msg ? { validationStatus: "error" as const, feedback: msg } : {};
}

/* ---------------- Password acak ---------------- */
const generated = ref(false);
function generatePassword() {
    const chars = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789";
    const arr = new Uint32Array(10);
    crypto.getRandomValues(arr);
    props.form.password = Array.from(arr, (n) => chars[n % chars.length]).join("");
    generated.value = true;
}

/* ---------------- Avatar ---------------- */
const fileInput = ref<HTMLInputElement | null>(null);
const preview = ref<string | null>(null);
const avatarSrc = computed(() => preview.value ?? props.initialAvatar ?? null);
const initials = computed(() =>
    (props.form.fullname || "?").trim().split(/\s+/).slice(0, 2).map((w) => w[0]).join("").toUpperCase(),
);

function pickFile() {
    fileInput.value?.click();
}
function onFilePicked(e: Event) {
    const input = e.target as HTMLInputElement;
    const file = input.files?.[0];
    input.value = "";
    if (!file) return;
    if (!file.type.startsWith("image/")) return message.error("File harus berupa gambar.");
    if (preview.value) URL.revokeObjectURL(preview.value);
    preview.value = URL.createObjectURL(file);
    props.form.avatar = file;
}
onBeforeUnmount(() => {
    if (preview.value) URL.revokeObjectURL(preview.value);
});

/* ---------------- Submit ---------------- */
async function submit() {
    try {
        await formRef.value?.validate();
    } catch {
        message.error("Masih ada data yang belum benar.");
        return;
    }
    if (props.form.organization_role === "member" && !props.form.memberships.some((m) => m.unit_id)) {
        message.error(`Pilih minimal satu ${t("unit").toLowerCase()} untuk anggota.`);
        return;
    }
    emit("submit");
}
</script>
