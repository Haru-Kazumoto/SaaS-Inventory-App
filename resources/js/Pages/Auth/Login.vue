<template>
    <GuestLayout>
        <Head title="Login - Sistem Inventaris" />

        <n-config-provider :theme-overrides="loginTheme" abstract>
            <div
                class="min-h-screen w-full grid grid-cols-1 lg:grid-cols-[minmax(0,5fr)_minmax(0,6fr)] bg-white"
            >
                <!-- ===== Kiri: panel brand ===== -->
                <aside
                    class="relative overflow-hidden bg-brand text-white flex flex-col justify-between px-6 py-6 sm:px-10 lg:px-14 lg:py-12"
                >
                    <!-- Watermark logo (dekoratif) -->
                    <img
                        src="/images/logo-yatindo-hd.png"
                        alt=""
                        aria-hidden="true"
                        class="hidden lg:block absolute -right-28 -bottom-28 w-[30rem] opacity-[0.06] select-none pointer-events-none"
                    />

                    <!-- Header brand -->
                    <div class="relative flex items-center gap-3 lg:gap-4">
                        <img
                            src="/images/logo-yatindo-hd.png"
                            alt="Logo Yayasan Tinta Emas Indonesia"
                            class="h-10 w-10 lg:h-14 lg:w-14 object-contain select-none"
                        />
                        <div class="leading-tight">
                            <p
                                class="text-[11px] lg:text-xs font-semibold uppercase tracking-[0.18em] text-brand-accent"
                            >
                                Yatindo Bekasi
                            </p>
                            <p class="text-sm lg:text-base font-medium text-white/90">
                                Yayasan Tinta Emas Indonesia
                            </p>
                        </div>
                    </div>

                    <!-- Konten utama (desktop) -->
                    <div class="relative hidden lg:block max-w-md py-12">
                        <span class="block h-1 w-12 bg-brand-accent mb-8"></span>
                        <h2
                            class="text-4xl xl:text-5xl font-bold leading-[1.1] tracking-tight"
                        >
                            Sistem Inventaris Sekolah
                        </h2>
                        <p class="mt-5 text-base text-white/70 leading-relaxed">
                            Pencatatan real-time untuk distribusi barang, kondisi
                            barang, serta peminjaman inventaris sekolah.
                        </p>

                        <ul class="mt-10 space-y-4 text-sm text-white/85">
                            <li
                                v-for="item in highlights"
                                :key="item"
                                class="flex items-start gap-3"
                            >
                                <span
                                    class="mt-[7px] h-1.5 w-1.5 shrink-0 bg-brand-accent"
                                ></span>
                                {{ item }}
                            </li>
                        </ul>
                    </div>

                    <!-- Footer brand (desktop) -->
                    <div
                        class="relative hidden lg:flex items-center justify-between border-t border-white/15 pt-6 text-xs text-white/60"
                    >
                        <span
                            ref="typewriterContainer"
                            class="font-mono text-white/75 min-h-[1rem]"
                        ></span>
                        <span>&copy; {{ year }} Yatindo</span>
                    </div>
                </aside>

                <!-- ===== Kanan: form login ===== -->
                <main
                    class="flex flex-col px-6 py-10 sm:px-10 lg:px-16 xl:px-24"
                >
                    <div
                        class="flex-1 flex items-center justify-center lg:justify-start"
                    >
                        <div class="w-full max-w-md">
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.18em] text-brand"
                            >
                                Masuk
                            </p>
                            <h1
                                class="mt-3 text-3xl sm:text-4xl font-bold tracking-tight text-slate-900"
                            >
                                Selamat datang kembali
                            </h1>
                            <p class="mt-3 text-sm sm:text-base text-slate-500">
                                Masuk dengan akun Anda untuk mengelola stok dan
                                peminjaman barang.
                            </p>

                            <n-form
                                ref="loginFormRef"
                                class="mt-10"
                                :model="loginForm"
                                :rules="loginRules"
                                label-placement="top"
                                require-mark-placement="left"
                                @submit.prevent="handleSubmitLogin"
                            >
                                <n-form-item label="Username" path="username">
                                    <n-input
                                        v-model:value="loginForm.username"
                                        size="large"
                                        placeholder="Masukkan username"
                                        :disabled="loadingLogin"
                                        :input-props="{ autocomplete: 'username' }"
                                        clearable
                                    />
                                </n-form-item>

                                <n-form-item label="Password" path="password">
                                    <n-input
                                        v-model:value="loginForm.password"
                                        size="large"
                                        type="password"
                                        show-password-on="click"
                                        placeholder="Masukkan password"
                                        :disabled="loadingLogin"
                                        :input-props="{
                                            autocomplete: 'current-password',
                                        }"
                                    />
                                </n-form-item>

                                <n-button
                                    type="primary"
                                    size="large"
                                    attr-type="submit"
                                    block
                                    class="mt-2 font-semibold"
                                    :loading="loadingLogin"
                                    :disabled="loadingLogin"
                                    icon-placement="right"
                                >
                                    Masuk ke Dashboard
                                    <template #icon>
                                        <n-icon :component="ArrowImport20Filled" />
                                    </template>
                                </n-button>

                                <p
                                    class="mt-6 border-l-2 border-slate-200 pl-3 text-xs text-slate-500 leading-relaxed"
                                >
                                    Lupa username atau password? Hubungi tim IT
                                    sekolah untuk mengatur ulang akun Anda.
                                </p>
                            </n-form>
                        </div>
                    </div>

                    <div
                        class="mt-10 flex items-center justify-between text-xs text-slate-400 lg:max-w-md"
                    >
                        <span>Sistem Inventaris</span>
                        <span v-if="version" class="font-mono">v{{ version }}</span>
                    </div>
                </main>
            </div>
        </n-config-provider>
    </GuestLayout>
</template>

<script setup lang="ts">
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import {
    NConfigProvider,
    NInput,
    NButton,
    NForm,
    NFormItem,
    NIcon,
    type FormInst,
    type FormRules,
    type GlobalThemeOverrides,
} from "naive-ui";
import { onMounted, ref } from "vue";
import Swal from "sweetalert2";
import { ArrowImport20Filled } from "@vicons/fluent";

// Mengatasi Type-safety typewriter jika dimuat via CDN script tags
declare const Typewriter: any;

type LoginForm = {
    username: string;
    password: string;
};

// Warna biru pekat dari logo Yatindo
const BRAND = "#2A126C";
const BRAND_HOVER = "#3A1F8A";
const BRAND_PRESSED = "#1F0D52";

const loginTheme: GlobalThemeOverrides = {
    common: {
        primaryColor: BRAND,
        primaryColorHover: BRAND_HOVER,
        primaryColorPressed: BRAND_PRESSED,
        primaryColorSuppl: BRAND_HOVER,
        borderRadius: "4px",
    },
    Button: {
        borderRadiusLarge: "4px",
        heightLarge: "48px",
        fontSizeLarge: "15px",
    },
    Input: {
        borderRadius: "4px",
        heightLarge: "48px",
        caretColor: BRAND,
        border: "1px solid #CBD5E1",
        borderHover: `1px solid ${BRAND}`,
        borderFocus: `1px solid ${BRAND}`,
        boxShadowFocus: "0 0 0 3px rgba(42, 18, 108, 0.12)",
    },
    Form: {
        labelFontSizeTopLarge: "14px",
        labelTextColor: "#334155",
    },
};

const highlights = [
    "Pantau keluar-masuk stok barang secara langsung",
    "Catat kondisi dan lokasi setiap aset sekolah",
    "Kelola peminjaman inventaris dengan riwayat lengkap",
];

const props = defineProps({
    status: { type: String, required: true },
    version: { type: String, required: true },
});

const year = new Date().getFullYear();
const typewriterContainer = ref<HTMLElement | null>(null);
const loadingLogin = ref(false);
const loginFormRef = ref<FormInst | null>(null);

const loginForm = useForm<LoginForm>({
    username: "",
    password: "",
});

const loginRules: FormRules = {
    username: [
        {
            required: true,
            message: "Username wajib diisi",
            trigger: ["blur", "input"],
        },
    ],
    password: [
        {
            required: true,
            message: "Password wajib diisi",
            trigger: ["blur"],
        },
    ],
};

onMounted(() => {
    // Memastikan Typewriter ada di global scope window object sebelum dipanggil
    if (typewriterContainer.value && typeof Typewriter !== "undefined") {
        new Typewriter(typewriterContainer.value, {
            loop: true,
            delay: 60,
        })
            .typeString("Solusi penataan logistik modern")
            .pauseFor(2500)
            .deleteAll()
            .typeString("Kelola keluar-masuk stok instan")
            .pauseFor(2500)
            .deleteAll()
            .typeString("Transparan, Akurat, & Sistematis.")
            .pauseFor(3000)
            .deleteAll()
            .start();
    }
});

const swalPopup = "rounded-md border border-slate-200 shadow-xl p-6";

function handleSubmitLogin() {
    loginFormRef.value?.validate((errors) => {
        if (errors) return;

        loadingLogin.value = true;

        // 1. Tampilkan Modal Loading segera setelah validasi sisi klien lolos
        Swal.fire({
            title: "Memverifikasi Akun",
            html: `
                <div class="text-center space-y-2 mt-2">
                    <p class="text-slate-500 text-xs">Menghubungkan ke server dan memeriksa kredensial...</p>
                </div>
            `,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            background: "#ffffff",
            customClass: {
                popup: `${swalPopup} max-w-xs`,
                title: "text-lg font-bold text-slate-800 tracking-tight",
            },
            didOpen: () => {
                Swal.showLoading();
            },
        });

        // 2. Jalankan request POST menggunakan Inertia Form
        loginForm.post(route("login"), {
            onSuccess: (page: any) => {
                loadingLogin.value = false;

                if (props.status !== "test") {
                    Swal.fire({
                        title: "Berhasil Login!",
                        html: `
                            <div class="text-center space-y-1 mt-2">
                                <p class="text-slate-500 text-sm">Selamat datang kembali,</p>
                                <p class="text-slate-800 font-bold text-base tracking-tight">${page.props.auth.user.fullname}</p>
                                <p class="text-[11px] font-medium pt-2" style="color:${BRAND}">Membuka dashboard inventaris Anda...</p>
                            </div>
                        `,
                        icon: "success",
                        iconColor: BRAND,
                        showConfirmButton: false,
                        timer: 2000,
                        background: "#ffffff",
                        customClass: {
                            popup: `${swalPopup} max-w-sm`,
                            title: "text-xl font-bold text-slate-900 tracking-tight pt-2",
                            htmlContainer: "m-0",
                        },
                    });
                } else {
                    Swal.fire({
                        title: "Login Berhasil (Mode Pengembangan)",
                        text: "Sistem mendeteksi lingkungan uji coba. Mutasi perubahan basis data bersifat temporer sebelum masuk fase production.",
                        icon: "info",
                        iconColor: BRAND,
                        confirmButtonColor: BRAND,
                        customClass: {
                            popup: swalPopup,
                        },
                    });
                }
            },
            onError: (err: any) => {
                loadingLogin.value = false;

                const serverErrorMessage =
                    err.username ||
                    err.password ||
                    "Kredensial akun tidak cocok dengan data kami.";

                Swal.fire({
                    title: "Autentikasi Gagal",
                    html: `
                        <div class="text-center space-y-1 mt-1">
                            <p class="text-slate-500 text-sm">${serverErrorMessage}</p>
                            <p class="text-[11px] text-slate-400 pt-1">Periksa kembali penulisan huruf besar dan kecil.</p>
                        </div>
                    `,
                    icon: "error",
                    iconColor: "#DC2626",
                    confirmButtonText: "Coba Lagi",
                    buttonsStyling: false,
                    background: "#ffffff",
                    customClass: {
                        popup: `${swalPopup} max-w-sm`,
                        title: "text-xl font-bold text-slate-800 tracking-tight pt-2",
                        htmlContainer: "m-0",
                        confirmButton:
                            "w-full mt-4 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-2.5 px-4 rounded transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-red-400/50",
                    },
                });
            },
        });
    });
}
</script>

<style scoped>
.bg-brand {
    background-color: #2a126c;
}
.text-brand {
    color: #2a126c;
}
.bg-brand-accent {
    background-color: #f5d90a;
}
.text-brand-accent {
    color: #f5d90a;
}

:deep(.n-form-item-blank) {
    width: 100%;
}
</style>
