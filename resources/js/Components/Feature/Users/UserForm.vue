<template>
    <div class="grid grid-cols-1 gap-5 pb-24 lg:grid-cols-3 lg:pb-0">
        <!-- ================= FORM ================= -->
        <n-form
            ref="formRef"
            :model="form"
            :rules="rules"
            label-placement="top"
            require-mark-placement="right-hanging"
            class="flex flex-col gap-5 lg:col-span-2"
        >
            <!-- 1. PROFIL -->
            <FormSection :step="1" title="Profil" description="Identitas dan kontak pengguna.">
                <div class="flex flex-col gap-5 sm:flex-row">
                    <!-- Avatar -->
                    <div class="flex shrink-0 flex-col items-center gap-2 sm:w-32">
                        <input
                            ref="fileInput"
                            type="file"
                            accept=".png,.jpg,.jpeg"
                            class="hidden"
                            @change="onFilePicked"
                        />
                        <button
                            type="button"
                            class="group relative flex h-24 w-24 items-center justify-center overflow-hidden rounded-full border border-slate-200 bg-slate-50"
                            :aria-label="avatarSrc ? 'Ganti foto' : 'Pilih foto'"
                            @click="pickFile"
                        >
                            <img v-if="avatarSrc" :src="avatarSrc" alt="" class="h-full w-full object-cover" />
                            <span v-else class="text-2xl font-bold text-slate-400">{{ initials }}</span>
                            <span
                                class="absolute inset-0 flex items-center justify-center bg-slate-900/50 text-white opacity-0 transition-opacity duration-150 group-hover:opacity-100"
                            >
                                <n-icon :component="Camera20Regular" size="22" />
                            </span>
                        </button>
                        <div class="flex gap-1">
                            <n-button size="tiny" @click="pickFile">
                                {{ avatarSrc ? "Ganti" : "Pilih foto" }}
                            </n-button>
                            <n-button v-if="avatarFile" size="tiny" quaternary @click="clearAvatar">
                                Batal
                            </n-button>
                        </div>
                        <span class="text-center text-[11px] leading-snug text-slate-400">
                            Opsional · persegi, &lt; 1 MB
                        </span>
                    </div>

                    <div class="grid flex-1 grid-cols-1 gap-x-4 sm:grid-cols-2">
                        <n-form-item label="Nama lengkap" path="fullname" class="sm:col-span-2" v-bind="err('fullname')">
                            <n-input v-model:value="form.fullname" placeholder="cth: Budi Santoso" />
                        </n-form-item>
                        <n-form-item label="Nomor telepon" path="phone" v-bind="err('phone')">
                            <n-input
                                :value="form.phone"
                                placeholder="0812xxxxxxxx"
                                inputmode="tel"
                                @update:value="(v) => (form.phone = v.replace(/[^\d+]/g, ''))"
                            />
                        </n-form-item>
                        <n-form-item label="Email" path="email" v-bind="err('email')">
                            <n-input v-model:value="form.email" placeholder="nama@sekolah.sch.id" inputmode="email" />
                        </n-form-item>
                    </div>
                </div>
            </FormSection>

            <!-- 2. AKUN & AKSES -->
            <FormSection
                :step="2"
                title="Akun & Akses"
                description="Data login dan hak akses pengguna di aplikasi."
            >
                <div class="grid grid-cols-1 gap-x-4 sm:grid-cols-2">
                    <n-form-item label="Username" path="username" v-bind="err('username')">
                        <n-input
                            :value="form.username"
                            placeholder="cth: budi.tjkt"
                            autocomplete="off"
                            @update:value="(v) => (form.username = v.replace(/\s+/g, '').toLowerCase())"
                        >
                            <template #prefix>
                                <span class="text-slate-400">@</span>
                            </template>
                        </n-input>
                    </n-form-item>

                    <n-form-item
                        :label="mode === 'edit' ? 'Password baru' : 'Password'"
                        path="password"
                        v-bind="err('password')"
                    >
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
                                    <template #icon>
                                        <n-icon :component="Key20Regular" />
                                    </template>
                                </n-button>
                            </div>
                            <span v-if="generated" class="text-[11px] text-emerald-700">
                                Password dibuat: <b class="font-mono">{{ form.password }}</b> — catat dan
                                berikan ke pengguna.
                            </span>
                            <span v-else-if="mode === 'edit'" class="text-[11px] text-slate-400">
                                Biarkan kosong untuk tetap memakai password lama.
                            </span>
                        </div>
                    </n-form-item>

                    <n-form-item label="Role" path="role" v-bind="err('role')">
                        <n-select
                            v-model:value="form.role"
                            :options="roleOptions"
                            placeholder="Pilih role"
                            filterable
                            @update:value="onRoleChange"
                        />
                    </n-form-item>

                    <n-form-item v-if="!isSuper" label="Jurusan" path="major" v-bind="err('major')">
                        <div class="flex w-full flex-col gap-1">
                            <n-select
                                v-model:value="form.major"
                                :options="majorOptions"
                                placeholder="Pilih jurusan"
                                filterable
                            />
                            <span
                                v-if="roleMajor && form.major && form.major !== roleMajor"
                                class="text-[11px] text-amber-600"
                            >
                                Role {{ roleLabel(form.role) }} biasanya untuk jurusan {{ roleMajor }}.
                            </span>
                        </div>
                    </n-form-item>
                </div>
            </FormSection>
        </n-form>

        <!-- ================= PRATINJAU (desktop) ================= -->
        <aside class="hidden lg:block">
            <section class="sticky top-20 rounded border border-slate-200 bg-white">
                <header class="border-b border-slate-100 px-5 py-4">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">
                        Pratinjau akun
                    </p>
                </header>
                <div class="flex flex-col items-center gap-2 px-5 py-6 text-center">
                    <div
                        class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-full border border-slate-200 bg-slate-50"
                    >
                        <img v-if="avatarSrc" :src="avatarSrc" alt="" class="h-full w-full object-cover" />
                        <span v-else class="text-lg font-bold text-slate-400">{{ initials }}</span>
                    </div>
                    <p class="max-w-full truncate text-base font-bold text-slate-900">
                        {{ form.fullname || "Nama pengguna" }}
                    </p>
                    <p class="text-xs text-slate-500">@{{ form.username || "username" }}</p>
                    <div class="mt-1 flex flex-wrap justify-center gap-1.5">
                        <span
                            v-if="form.role"
                            class="rounded border px-1.5 py-0.5 text-[11px] font-semibold"
                            :class="roleBadgeClass(form.role)"
                        >
                            {{ roleLabel(form.role) }}
                        </span>
                        <span
                            v-if="!isSuper && form.major"
                            class="rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-[11px] font-semibold text-slate-600"
                        >
                            {{ majorName }}
                        </span>
                    </div>
                </div>
                <div class="flex flex-col gap-2 border-t border-slate-100 p-5">
                    <n-button type="primary" size="large" block :loading="processing" @click="submit">
                        <template #icon><n-icon :component="Save20Regular" /></template>
                        {{ mode === "edit" ? "Simpan Perubahan" : "Buat Pengguna" }}
                    </n-button>
                    <n-button block :disabled="processing" @click="$emit('cancel')">Batal</n-button>
                </div>
            </section>
        </aside>

        <!-- ================= ACTION BAR (mobile) ================= -->
        <div
            class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 px-4 py-3 backdrop-blur lg:hidden"
            style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom))"
        >
            <div class="mx-auto flex max-w-3xl justify-end gap-2">
                <n-button :disabled="processing" @click="$emit('cancel')">Batal</n-button>
                <n-button type="primary" :loading="processing" @click="submit">
                    {{ mode === "edit" ? "Simpan Perubahan" : "Buat Pengguna" }}
                </n-button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, ref } from "vue";
import {
    NButton,
    NForm,
    NFormItem,
    NIcon,
    NInput,
    NSelect,
    useMessage,
    type FormInst,
    type FormRules,
} from "naive-ui";
import { Camera20Regular, Key20Regular, Save20Regular } from "@vicons/fluent";
import FormSection from "@/Components/FormSection.vue";
import { majorFromRole, roleBadgeClass, roleLabel } from "./user-meta";
import type { UserFormState } from "./types";

const props = defineProps<{
    mode: "create" | "edit";
    /** state form (dari useForm Inertia di halaman) */
    form: UserFormState;
    majors: Array<{ name: string; code: string }>;
    roles: Array<{ name: string }>;
    /** foto lama (mode edit) */
    initialAvatar?: string | null;
    processing?: boolean;
}>();

const emit = defineEmits<{ (e: "submit"): void; (e: "cancel"): void }>();

const message = useMessage();
const formRef = ref<FormInst | null>(null);

const roleOptions = computed(() =>
    props.roles.map((r) => ({ label: roleLabel(r.name), value: r.name })),
);
const majorOptions = computed(() =>
    props.majors.map((m) => ({ label: m.name, value: m.code })),
);
const majorName = computed(
    () => props.majors.find((m) => m.code === props.form.major)?.name ?? props.form.major,
);

const isSuper = computed(() => props.form.role === "SUPERADMIN");
const roleMajor = computed(() => majorFromRole(props.form.role));

function onRoleChange(role: string) {
    if (role === "SUPERADMIN") {
        props.form.major = "";
        return;
    }
    const m = majorFromRole(role);
    if (m && props.majors.some((x) => x.code === m)) props.form.major = m;
}

/* ---------------- Validasi ---------------- */
const rules = computed<FormRules>(() => ({
    fullname: [
        { required: true, message: "Nama wajib diisi", trigger: "blur" },
        { min: 3, message: "Nama minimal 3 karakter", trigger: "blur" },
    ],
    phone: { required: true, message: "Nomor telepon wajib diisi", trigger: ["input", "blur"] },
    email: [
        { required: true, message: "Email wajib diisi", trigger: "blur" },
        { type: "email", message: "Format email tidak valid", trigger: "blur" },
    ],
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
    role: { required: true, message: "Pilih role", trigger: ["change", "blur"] },
    major: {
        trigger: ["change", "blur"],
        validator: () => (isSuper.value || props.form.major ? true : new Error("Pilih jurusan")),
    },
}));

/** error validasi dari server (Laravel) */
function err(field: keyof UserFormState) {
    const msg = props.form.errors?.[field as string];
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
const avatarFile = ref<File | null>(null);
const preview = ref<string | null>(null);
const avatarSrc = computed(() => preview.value ?? props.initialAvatar ?? null);

const initials = computed(() =>
    (props.form.fullname || "?")
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((w) => w[0])
        .join("")
        .toUpperCase(),
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
    if (file.size > 1024 * 1024) message.warning("Ukuran foto lebih dari 1 MB, unggahan mungkin lambat.");
    if (preview.value) URL.revokeObjectURL(preview.value);
    preview.value = URL.createObjectURL(file);
    avatarFile.value = file;
    props.form.avatar = file;
}
function clearAvatar() {
    if (preview.value) URL.revokeObjectURL(preview.value);
    preview.value = null;
    avatarFile.value = null;
    props.form.avatar = null;
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
    emit("submit");
}
</script>
