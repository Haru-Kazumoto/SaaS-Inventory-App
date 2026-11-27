<template>
    <n-config-provider :theme-overrides="themeConfig">
        <n-message-provider>
            <n-notification-provider>
                <n-layout has-sider position="absolute">
                    <!-- SIDEBAR -->
                    <n-layout-sider
                        class="app-sider"
                        :native-scrollbar="false"
                        bordered
                        collapse-mode="width"
                        :width="280"
                        :collapsed-width="72"
                    >
                        <div class="h-screen flex flex-col bg-white">
                            <!-- BRAND / LOGO AREA -->
                            <div
                                class="px-4 py-5 bg-[#2A126C] border-b-2 border-[#F5D90A] flex items-center gap-3"
                            >
                                <n-image
                                    width="65"
                                    src="/images/logo-yatindo-hd.png"
                                />

                                <div class="flex flex-col min-w-0">
                                    <span
                                        class="text-sm font-semibold tracking-wide uppercase text-white truncate"
                                        title="Sistem Inventaris"
                                    >
                                        Sistem Inventaris
                                    </span>
                                    <span
                                        class="text-xs text-white/60 truncate"
                                        title="Yayasan Tinta Emas Indonesia"
                                    >
                                        Yayasan Tinta Emas Indonesia
                                    </span>
                                </div>
                            </div>

                            <!-- MENU -->
                            <div class="flex-1 overflow-auto px-2 pt-2 pb-4">
                                <n-menu
                                    :collapsed-width="72"
                                    :collapsed-icon-size="22"
                                    :options="menu"
                                    :value="activeMenu"
                                    :render-label="renderMenuLabel"
                                    class="mt-1"
                                />
                            </div>

                            <!-- FOOTER SIDEBAR (INFO USER) -->
                            <div
                                class="px-4 py-3 border-t border-white/10 flex items-center gap-3 text-xs"
                            >
                                <div
                                    class="h-8 w-8 shrink-0 flex items-center justify-center rounded bg-[#2A126C] text-white text-[11px] font-bold"
                                >
                                    {{ userInitials }}
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span
                                        class="font-semibold text-slate-800 truncate"
                                        :title="userName"
                                    >
                                        {{ userName }}
                                    </span>
                                    <span
                                        class="text-[10px] text-slate-500 uppercase tracking-[0.14em] truncate"
                                        :title="userRoleName"
                                    >
                                        {{ userRoleName }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </n-layout-sider>

                    <!-- MAIN LAYOUT -->
                    <n-layout>
                        <!-- HEADER -->
                        <n-layout-header position="absolute" class="z-50">
                            <div
                                class="h-16 w-full flex items-center justify-between gap-4 px-4 sm:px-6 bg-[#2A126C] border-b-2 border-[#F5D90A]"
                            >
                                <!-- LEFT: MENU TOGGLE + ACTIVE MODULE -->
                                <div class="flex items-center gap-3 min-w-0">
                                    <button
                                        @click="drawerActive = true"
                                        type="button"
                                        aria-label="Buka menu navigasi"
                                        class="lg:hidden h-9 w-9 shrink-0 flex items-center justify-center rounded border border-white/25 text-white hover:bg-white/10 hover:border-white/50 transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-white/30"
                                    >
                                        <n-icon
                                            :component="Navigation24Regular"
                                            size="18"
                                        />
                                    </button>

                                    <div
                                        class="flex flex-col min-w-0 border-l-2 border-[#F5D90A] pl-3"
                                    >
                                        <span
                                            class="text-[10px] font-semibold uppercase tracking-[0.18em] text-white/60 leading-none"
                                        >
                                            Modul Aktif
                                        </span>
                                        <span
                                            class="mt-1 text-base sm:text-lg font-bold text-white tracking-tight leading-tight truncate"
                                        >
                                            {{ activeModule }}
                                        </span>
                                    </div>
                                </div>

                                <!-- RIGHT: USER + LOGOUT -->
                                <div class="flex items-center gap-3 shrink-0">
                                    <div
                                        class="hidden sm:flex flex-col text-right"
                                    >
                                        <span
                                            class="text-sm font-semibold text-white leading-tight"
                                        >
                                            {{ userName }}
                                        </span>
                                        <span
                                            class="text-[10px] font-medium text-white/60 uppercase tracking-[0.14em] mt-0.5"
                                        >
                                            {{ userRoleName }}
                                        </span>
                                    </div>

                                    <div
                                        class="h-9 w-9 shrink-0 flex items-center justify-center rounded bg-white text-[#2A126C] text-xs font-bold tracking-wide"
                                        :title="userName"
                                    >
                                        {{ userInitials }}
                                    </div>

                                    <span
                                        role="separator"
                                        aria-hidden="true"
                                        class="hidden sm:block w-px h-6 bg-white/20"
                                    ></span>

                                    <!-- Logout -->
                                    <button
                                        @click="triggerLogoutConfirmation"
                                        type="button"
                                        aria-label="Logout"
                                        class="group h-9 w-9 flex items-center justify-center rounded border border-white/25 text-white hover:bg-white/10 hover:border-white/50 transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-white/30"
                                    >
                                        <n-icon
                                            :component="SignOut24Filled"
                                            size="18"
                                            class="block"
                                        />
                                    </button>
                                </div>
                            </div>
                        </n-layout-header>

                        <!-- CONTENT -->
                        <!-- CONTENT -->
                        <n-layout-content>
                            <div
                                class="relative min-h-screen px-4 sm:px-6 lg:px-8 pt-20 pb-8 bg-slate-50"
                            >
                                <!-- Global Loading Overlay -->
                                <Transition
                                    enter-active-class="transition-opacity duration-200"
                                    leave-active-class="transition-opacity duration-200"
                                    enter-from-class="opacity-0"
                                    leave-to-class="opacity-0"
                                >
                                    <div
                                        v-if="isLoading"
                                        class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-[2px] bg-white/50"
                                    >
                                        <div
                                            class="flex flex-col items-center gap-3 bg-white px-6 py-5 rounded border border-slate-200 shadow-sm"
                                        >
                                            <n-spin size="large" />

                                            <div class="text-center">
                                                <p
                                                    class="text-sm font-semibold text-slate-700"
                                                >
                                                    Memuat Data
                                                </p>

                                                <p
                                                    class="text-xs text-slate-400 mt-1"
                                                >
                                                    Mohon tunggu sebentar...
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </Transition>

                                <!-- Content Wrapper -->
                                <div class="max-w-7xl mx-auto">
                                    <!-- Testing Warning -->
                                    <div
                                        v-if="$page.props.is_test"
                                        class="mb-4 flex items-center gap-2 rounded border border-yellow-300 bg-yellow-50 px-4 py-3 text-yellow-800"
                                    >
                                        <svg
                                            class="h-5 w-5 text-yellow-500 shrink-0"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 9v2m0 4h.01M5.455 19h13.09c1.36 0 2.22-1.48 1.54-2.67L13.54 4.67c-.68-1.19-2.4-1.19-3.08 0L3.915 16.33C3.235 17.52 4.095 19 5.455 19z"
                                            />
                                        </svg>

                                        <span class="text-sm font-medium">
                                            Aplikasi masih dalam tahap
                                            <strong>Testing</strong>. Data dan
                                            fitur dapat berubah sewaktu-waktu.
                                        </span>
                                    </div>

                                    <!-- Documentation Banner -->
                                    <div
                                        v-if="$page.props.is_test"
                                        class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded border border-blue-300 bg-blue-50 px-4 py-3 text-blue-800"
                                    >
                                        <div class="flex items-center gap-2">
                                            <n-button
                                                type="info"
                                                size="small"
                                                @click="downloadModule"
                                            >
                                                <template #icon>
                                                    <n-icon
                                                        :component="
                                                            ArrowDownload16Filled
                                                        "
                                                    />
                                                </template>

                                                Download
                                            </n-button>

                                            <span class="text-sm font-medium">
                                                Unduh laporan pengujian dan
                                                dokumentasi aplikasi
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Page Content -->
                                    <slot />
                                </div>
                            </div>
                        </n-layout-content>

                        <n-layout-footer>
                            <footer
                                class="bg-white border-t border-slate-200"
                            >
                                <div
                                    class="w-full mx-auto max-w-screen-xl px-6 py-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <span
                                        class="text-xs text-slate-500 tracking-wide"
                                    >
                                        © {{ year }}
                                        <a
                                            href="https://haru-kazumoto.github.io/ziaurrahman/"
                                            class="text-slate-700 hover:text-[#2A126C] transition-colors duration-150 font-semibold"
                                            target="_blank"
                                        >
                                            Ziaurrahman
                                        </a>
                                        . All Rights Reserved.
                                    </span>

                                    <span
                                        class="text-xs text-slate-400 flex items-center gap-2"
                                    >
                                        <span
                                            class="h-1 w-4 bg-[#F5D90A]"
                                            aria-hidden="true"
                                        ></span>
                                        Sistem Inventaris · Yayasan Tinta Emas
                                        Indonesia
                                    </span>
                                </div>
                            </footer>
                        </n-layout-footer>
                    </n-layout>

                    <!-- DRAWER NAVIGASI MOBILE -->
                    <n-drawer
                        v-model:show="drawerActive"
                        placement="left"
                        :width="288"
                    >
                        <n-drawer-content
                            :native-scrollbar="false"
                            closable
                            body-content-style="padding: 0;"
                            :header-style="{
                                backgroundColor: '#2A126C',
                                borderBottom: '1px solid #F5D90A',
                            }"
                        >
                            <template #header>
                                <div class="flex items-center gap-3 pr-8">
                                    <img
                                        src="/images/logo-yatindo-hd.png"
                                        alt="Logo Yayasan Tinta Emas Indonesia"
                                        class="h-9 w-9 object-contain select-none"
                                    />
                                    <div class="flex flex-col min-w-0">
                                        <span
                                            class="text-sm font-semibold tracking-wide uppercase text-white truncate"
                                        >
                                            Sistem Inventaris
                                        </span>
                                        <span
                                            class="text-[11px] text-white/60 truncate"
                                        >
                                            Yayasan Tinta Emas Indonesia
                                        </span>
                                    </div>
                                </div>
                            </template>

                            <div class="flex flex-col h-full bg-white">
                                <div class="flex-1 overflow-auto px-2 py-2">
                                    <n-menu
                                        :options="menu"
                                        :value="activeMenu"
                                        :render-label="renderMenuLabel"
                                    />
                                </div>

                                <div
                                    class="px-4 py-3 border-t border-slate-200 flex items-center gap-3 text-xs"
                                >
                                    <div
                                        class="h-8 w-8 shrink-0 flex items-center justify-center rounded bg-[#2A126C] text-white text-[11px] font-bold"
                                    >
                                        {{ userInitials }}
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span
                                            class="font-semibold text-slate-800 truncate"
                                            :title="userName"
                                        >
                                            {{ userName }}
                                        </span>
                                        <span
                                            class="text-[10px] text-slate-500 uppercase tracking-[0.14em] truncate"
                                            :title="userRoleName"
                                        >
                                            {{ userRoleName }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </n-drawer-content>
                    </n-drawer>

                    <!-- Modal Konfirmasi -->
                    <n-modal
                        v-model:show="showLogoutModal"
                        preset="card"
                        class="logout-modal"
                        :style="{ maxWidth: '400px', borderRadius: '4px' }"
                    >
                        <template #header>
                            <div class="flex items-center gap-2">
                                <n-icon
                                    color="#ef4444"
                                    size="24"
                                    :component="Warning24Filled"
                                ></n-icon>
                                <span class="font-semibold text-lg"
                                    >Konfirmasi Logout</span
                                >
                            </div>
                        </template>

                        <div class="text-gray-600 mt-2">
                            Apakah kamu yakin ingin keluar dari akun ini?
                            <br />
                            Semua sesi aktif akan berakhir.
                        </div>

                        <template #footer>
                            <div class="flex justify-end gap-3 mt-4">
                                <n-button
                                    quaternary
                                    @click="showLogoutModal = false"
                                >
                                    Batal
                                </n-button>
                                <n-button
                                    type="error"
                                    secondary
                                    @click="handleLogout"
                                >
                                    <template #icon>
                                        <n-icon
                                            :component="SignOut24Filled"
                                        ></n-icon>
                                    </template>
                                    Logout
                                </n-button>
                            </div>
                        </template>
                    </n-modal>
                </n-layout>
            </n-notification-provider>
        </n-message-provider>
    </n-config-provider>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Sidebar tetap tampil di desktop, diganti drawer di layar kecil */
@media (max-width: 1023px) {
    .app-sider {
        display: none !important;
    }
}

/* Tombol tutup drawer di atas latar brand */
:deep(.n-drawer-header__close) {
    color: #ffffff;
}
:deep(.n-drawer-header__close:hover) {
    background-color: rgba(255, 255, 255, 0.12);
}
</style>

<script lang="ts">
import {
    NConfigProvider,
    NDrawer,
    NDrawerContent,
    NDropdown,
    NIcon,
    NImage,
    NLayout,
    NLayoutFooter,
    NLayoutHeader,
    NLayoutSider,
    NMenu,
    NNotificationProvider,
    NInput,
    NButton,
    NMessageProvider,
    NTag,
    NLayoutContent,
    NModal,
    NAffix,
    NDivider,
    NSpin,
} from "naive-ui";
import {
    Component,
    defineComponent,
    h,
    ref,
    computed,
    watch,
    onMounted,
    onUnmounted,
} from "vue";
import { themeConfig } from "@/Utils/global-theme-config";
import {
    ChevronDown48Filled,
    Person20Filled,
    Person28Filled,
    SignOut24Filled,
    Warning24Filled,
    ArrowDownload16Filled,
    Navigation24Regular,
} from "@vicons/fluent";
import { Link, router, usePage } from "@inertiajs/vue3";
import { menuFor } from "@/Utils/menu-config";
import Swal from "sweetalert2";
import LoadingOverlay from "@/Components/LoadingOverlay.vue";

export default defineComponent({
    setup() {
        // dummy ajahh
        const showMotivation = ref(true);
        let timer: any = null;

        const checkTime = () => {
            const now = new Date();
            const hours = now.getHours();
            const minutes = now.getMinutes();

            // Kalau sudah lewat 13:30 → hilang
            if (hours > 15 || (hours === 13 && minutes >= 30)) {
                showMotivation.value = false;
            } else {
                showMotivation.value = true;
            }
        };

        onMounted(() => {
            checkTime();
            timer = setInterval(checkTime, 60000); // cek tiap 1 menit
        });

        onUnmounted(() => {
            clearInterval(timer);
        });

        function renderIcon(icon: Component) {
            return () => h(NIcon, null, { default: () => h(icon) });
        }

        const page = usePage();
        const year = new Date().getFullYear();
        const user = computed(() => page.props.auth.user);
        const showLogoutModal = ref(false);
        const userRole = computed(() => user.value?.role?.code);
        const userRoleName = computed(
            () => user.value?.role?.name || user.value?.role?.code || "User",
        );
        const userName = computed(
            () => user.value?.fullname || user.value?.name || "User",
        );

        const userInitials = computed(() => {
            const name = userName.value || "";
            const parts = name.trim().split(" ");
            if (parts.length === 0) return "?";
            if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
            return (
                parts[0].charAt(0).toUpperCase() +
                parts[parts.length - 1].charAt(0).toUpperCase()
            );
        });

        const menu = computed(() =>
            menuFor(userRole.value as string, {
                unit: (page.props as any).organization?.terms?.unit ?? "Unit",
                unit_plural: (page.props as any).organization?.terms?.unit_plural ?? "Unit",
            }),
        );

        const activeMenu = ref(
            localStorage.getItem("activeMenu") || "dashboard",
        );
        const activeModule = ref(
            localStorage.getItem("activeModule") || "Dashboard",
        );
        const drawerActive = ref(false);
        const isLoading = ref<boolean>(false);

        // Dengarkan event navigasi Inertia
        // onMounted(() => {
        //     router.on("start", (event) => {
        //         if (event.detail.visit.method === "get") {
        //             isLoading.value = true;
        //         }
        //     });

        //     router.on("finish", (event) => {
        //         if (event.detail.visit.method === "get") {
        //             isLoading.value = false;
        //         }
        //     });
        // });

        function saveMenuState(key: string, label: string) {
            localStorage.setItem("activeMenu", key);
            localStorage.setItem("activeModule", label);
        }

        function findActiveOption(menuList: any[], path: string): any {
            for (const menu of menuList) {
                if (menu.href === path) return menu;

                if (menu.children) {
                    const found = findActiveOption(menu.children, path);
                    if (found) return found;
                }
            }

            return null;
        }

        watch(
            () => page.url,
            (newUrl) => {
                const allMenuOptions = menu.value;
                const activeOption = findActiveOption(allMenuOptions, newUrl);

                if (activeOption) {
                    activeMenu.value = activeOption.key;
                    activeModule.value = activeOption.label;
                    saveMenuState(activeOption.key, activeOption.label);
                }
            },
            { immediate: true, deep: true },
        );

        const profileOptions = ref([
            {
                label: "Profile",
                key: "profile",
                icon: renderIcon(Person20Filled),
            },
            {
                label: "Logout",
                key: "logout",
                icon: renderIcon(SignOut24Filled),
            },
        ]);

        function triggerLogoutConfirmation() {
            Swal.fire({
                title: "Konfirmasi Keluar",
                html: `
            <div class="text-center space-y-1 mt-1">
                <p class="text-slate-500 text-sm">Apakah kamu yakin ingin keluar dari akun ini?</p>
                <p class="text-[11px] text-red-400 font-medium italic pt-1">Semua sesi aktif Anda akan diakhiri secara aman.</p>
            </div>
        `,
                icon: "warning",
                iconColor: "#ef4444",
                showCancelButton: true,
                confirmButtonText: "Ya, Keluar Sesi",
                cancelButtonText: "Batal",
                buttonsStyling: false,
                reverseButtons: true,
                background: "#ffffff",
                customClass: {
                    popup: "rounded border border-slate-200 shadow-xl p-6 max-w-sm",
                    title: "text-xl font-bold text-slate-800 tracking-tight pt-2",
                    htmlContainer: "m-0",
                    confirmButton:
                        "flex-1 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-2.5 px-4 rounded transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-red-400/50",
                    cancelButton:
                        "flex-1 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium py-2.5 px-4 rounded transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-slate-300/50",
                    actions: "flex w-full gap-3 mt-5 justify-stretch",
                },
                // Blok penting: Cegah menutup modal saat proses konfirmasi diklik
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    // Trik visual: Ubah teks tombol menjadi loading bawaan Swal
                    Swal.showLoading(Swal.getConfirmButton());

                    // Eksekusi fungsi logout Anda di sini
                    return new Promise((resolve) => {
                        executeLogoutSesi(resolve);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading(), // Kunci modal agar tidak bisa ditutup sembarangan saat loading
            });
        }

        // 2. Fungsi kedua untuk mengeksekusi request POST ke backend Laravel
        function executeLogoutSesi(resolvePopup?: () => void) {
            router.post(
                route("logout"),
                {},
                {
                    onSuccess: () => {
                        // Tutup loader preConfirm jika ada
                        if (resolvePopup) resolvePopup();

                        Swal.fire({
                            title: "Sesi Berakhir",
                            html: '<p class="text-xs text-slate-400">Anda telah keluar dengan aman. Mengalihkan...</p>',
                            icon: "success",
                            iconColor: "#10b981",
                            timer: 1500,
                            showConfirmButton: false,
                            background: "#ffffff",
                            customClass: {
                                popup: "rounded border border-slate-200 shadow-xl p-5 max-w-xs",
                                title: "text-lg font-bold text-slate-800 tracking-tight",
                            },
                        });
                    },
                    onError: () => {
                        if (resolvePopup) resolvePopup();
                        // Opsional: Handle jika gagal logout
                    },
                },
            );
        }
        
        function handleSelect(key: string | number) {
            if (key === "profile") {
                drawerActive.value = true;
            } else if (key === "logout") {
                handleLogout();
            }
        }

        function downloadModule() {
            window.open("/docs/modul-penggunaan.docx", "_blank");
        }

        return {
            showMotivation,
            year,
            activeMenu,
            menu,
            handleSelect,
            themeConfig,
            profileOptions,
            ChevronDown48Filled,
            Person28Filled,
            SignOut24Filled,
            Warning24Filled,
            ArrowDownload16Filled,
            Navigation24Regular,
            drawerActive,
            isLoading,
            showLogoutModal,
            activeModule,
            userName,
            userRoleName,
            userInitials,
            downloadModule,
            triggerLogoutConfirmation,
            renderMenuLabel(option: any) {
                if (option.href) {
                    return h(
                        Link,
                        {
                            href: option.href,
                            onClick: () => {
                                isLoading.value = true;
                                drawerActive.value = false;
                            },
                            onFinish: () => (isLoading.value = false),
                        },
                        { default: () => option.label },
                    );
                }

                return option.label;
            },
        };
    },
    components: {
        NConfigProvider,
        NNotificationProvider,
        NMessageProvider,
        NLayout,
        NMenu,
        NLayoutSider,
        NLayoutHeader,
        NImage,
        NIcon,
        NDropdown,
        NLayoutFooter,
        NDrawer,
        NButton,
        NDrawerContent,
        NInput,
        LoadingOverlay,
        NTag,
        NLayoutContent,
        NAffix,
        NDivider,
        NModal,
        NSpin,
    },
});
</script>
