<template>
    <div class="flex flex-col gap-5">
        <!-- HERO / SAPAAN -->
        <section
            class="relative overflow-hidden rounded border border-[#23105a] bg-[#2A126C] text-white"
        >
            <img
                src="/images/logo-yatindo-hd.png"
                alt=""
                aria-hidden="true"
                class="pointer-events-none select-none absolute -right-20 -bottom-24 w-80 opacity-[0.06]"
            />

            <div class="relative p-6 sm:p-7">
                <div
                    class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between"
                >
                    <div class="min-w-0 max-w-2xl">
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="h-1 w-8 bg-[#F5D90A]"
                                aria-hidden="true"
                            ></span>
                            <p
                                class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#F5D90A]"
                            >
                                Dashboard Superadmin
                            </p>

                            <span
                                class="rounded border border-white/15 bg-white/5 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-white/70"
                            >
                                Yayasan Tinta Emas
                            </span>
                        </div>

                        <h1
                            class="mt-3 text-2xl sm:text-3xl font-bold tracking-tight"
                        >
                            Halo, {{ userName }}
                        </h1>

                        <p
                            class="mt-2 max-w-xl text-sm leading-relaxed text-white/70"
                        >
                            Pantau pergerakan logistik dan perputaran aset
                            antar-jurusan dengan data yang real-time.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-stretch gap-3">
                        <div
                            class="flex items-center gap-3 rounded border border-white/15 bg-white/[0.06] px-4 py-2.5"
                        >
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded bg-white/10 text-[#F5D90A]"
                            >
                                <component
                                    :is="CalendarLtr20Regular"
                                    class="h-5 w-5"
                                />
                            </div>
                            <div>
                                <p
                                    class="text-[10px] font-semibold uppercase tracking-[0.16em] text-white/50"
                                >
                                    Tanggal
                                </p>
                                <p
                                    class="mt-1 text-sm font-semibold text-white"
                                >
                                    {{ todayLabel }}
                                </p>
                            </div>
                        </div>

                        <RealtimeClock variant="dark" />
                    </div>
                </div>

                <!-- AKSI CEPAT -->
                <div
                    class="mt-6 flex flex-wrap items-center gap-2 border-t border-white/10 pt-4"
                >
                    <Link
                        v-for="action in quickActions"
                        :key="action.label"
                        :href="action.href"
                        class="inline-flex items-center gap-2 rounded border px-3 py-2 text-xs font-semibold transition-colors duration-150"
                        :class="
                            action.primary
                                ? 'border-[#F5D90A] bg-[#F5D90A] text-[#2A126C] hover:bg-[#e8cc09]'
                                : 'border-white/20 bg-white/5 text-white hover:border-white/40 hover:bg-white/10'
                        "
                    >
                        <component :is="action.icon" class="h-4 w-4" />
                        {{ action.label }}
                    </Link>
                </div>
            </div>
        </section>

        <!-- RINGKASAN ANGKA -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <StatisticCard
                v-for="item in summaryCards"
                :key="item.title"
                v-bind="item"
            />
        </div>

        <!-- RINGKASAN PER JURUSAN -->
        <section class="rounded border border-slate-200 bg-white">
            <div
                class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <p
                        class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400"
                    >
                        Distribusi Aset
                    </p>
                    <h2
                        class="text-base font-bold text-slate-900 tracking-tight"
                    >
                        Ringkasan Barang Per Jurusan
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Pilih salah satu jurusan untuk memantau rincian
                        penempatan aset secara spesifik.
                    </p>
                </div>

                <span
                    class="shrink-0 self-start rounded border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-semibold tabular-nums text-slate-600 sm:self-auto"
                >
                    {{ majorList.length }} Jurusan
                </span>
            </div>

            <div
                class="grid grid-cols-1 gap-3 p-5 sm:grid-cols-2 lg:grid-cols-3"
            >
                <Link
                    v-for="major in majorList"
                    :key="major.key"
                    class="block no-underline"
                    :href="major.href"
                >
                    <ItemCard
                        class="h-full"
                        :iconColor="major.iconColor"
                        :borderColor="major.borderColor"
                        :backgroundColor="major.backgroundColor"
                        :icon="BoxCheckmark24Regular"
                        :title="`Jurusan ${major.label}`"
                        :count="count_data[major.key as keyof CountDataObject]"
                        unit="Item"
                        show-arrow
                        clickable
                    />
                </Link>
            </div>
        </section>

        <!-- RIWAYAT PENGAJUAN -->
        <section class="rounded border border-slate-200 bg-white">
            <div
                class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <p
                        class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400"
                    >
                        Aktivitas
                    </p>
                    <h2
                        class="text-base font-bold text-slate-900 tracking-tight"
                    >
                        10 Riwayat Pengajuan Terbaru
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Daftar mutasi pengeluaran dan permintaan peminjaman
                        barang teranyar dari sistem.
                    </p>
                </div>

                <n-button
                    size="medium"
                    secondary
                    strong
                    class="shrink-0 self-start rounded text-xs font-semibold px-4 text-slate-600 hover:text-slate-800 sm:self-auto"
                    @click="router.visit(requestIndexHref)"
                >
                    Lihat Semua Riwayat
                </n-button>
            </div>

            <div class="p-5">
                <div class="overflow-hidden rounded border border-slate-200">
                    <n-data-table
                        :bordered="false"
                        :columns="detailTransactionColumns"
                        :data="request_items.data"
                        size="medium"
                        :single-line="true"
                        striped
                    >
                        <template #empty>
                            <div class="py-8">
                                <Empty />
                            </div>
                        </template>
                    </n-data-table>
                </div>
            </div>
        </section>
    </div>
</template>

<script lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ItemCard from "@/Components/InformationCard.vue";
import StatisticCard from "@/Components/Feature/Dashboard/StatisticCard.vue";
import RealtimeClock from "@/Components/Feature/Dashboard/RealtimeClock.vue";
import Empty from "@/Components/Empty.vue";

import { NButton, NDataTable, NTag } from "naive-ui";
import { defineComponent, h } from "vue";
import type { Component } from "vue";
import {
    ArrowTrending20Regular,
    BoxArrowUp24Regular,
    BoxCheckmark24Regular,
    BoxMultiple24Regular,
    BoxToolbox24Regular,
    CalendarLtr20Regular,
    CheckmarkCircle20Regular,
    Clock20Regular,
    Home24Regular,
    PeopleSettings20Regular,
} from "@vicons/fluent";

import { router, Link, usePage } from "@inertiajs/vue3";
import { PaginationResponse } from "@/types/global-object";

interface CountDataObject {
    all: number;
    request_item: number;
    tjkt: number;
    te: number;
    to: number;
    ak: number;
    tu: number;
    kkpi: number;
}

interface RequestItem {
    id: number;
    brand_name: string;
    quantity: number;
    unit: string;
    reason_request: string;
    request_status: string;
    major_request: string;
}

interface StatisticInfo {
    total: number;
    title: string;
    unit?: string;
    href?: string;
    icon_title: Component;
    icon_status: Component;
    title_status: string;
    base_color: string;
}

interface QuickAction {
    label: string;
    href: string;
    icon: Component;
    primary?: boolean;
}

export default defineComponent({
    props: {
        count_data: { type: Object as () => CountDataObject, required: true },
        request_items: {
            type: Object as () => PaginationResponse<RequestItem>,
            required: true,
        },
    },
    setup(props) {
        const page = usePage();
        const userName = page.props.auth.user.fullname;
        const requestIndexHref = route("request-item.index");

        const todayLabel = new Intl.DateTimeFormat("id-ID", {
            weekday: "long",
            day: "numeric",
            month: "short",
            year: "numeric",
        }).format(new Date());

        const quickActions: QuickAction[] = [
            {
                label: "Permintaan Barang",
                href: requestIndexHref,
                icon: BoxToolbox24Regular,
                primary: true,
            },
            {
                label: "Manajemen Pengguna",
                href: route("user.index"),
                icon: PeopleSettings20Regular,
            },
            {
                label: "Barang Masuk",
                href: route("lines.restocks"),
                icon: BoxArrowUp24Regular,
            },
        ];

        // Data konfigurasi skema warna tiap jurusan agar template ringkas
        const majorList = [
            {
                key: "tjkt",
                label: "TJKT",
                href: route("products.major", "tjkt"),
                iconColor: "text-orange-600",
                borderColor: "border-orange-500",
                backgroundColor: "bg-orange-50",
            },
            {
                key: "te",
                label: "TE",
                href: route("products.major", "te"),
                iconColor: "text-violet-600",
                borderColor: "border-violet-500",
                backgroundColor: "bg-violet-50",
            },
            {
                key: "to",
                label: "TO",
                href: route("products.major", "to"),
                iconColor: "text-indigo-600",
                borderColor: "border-indigo-500",
                backgroundColor: "bg-indigo-50",
            },
            {
                key: "ak",
                label: "AK",
                href: route("products.major", "ak"),
                iconColor: "text-rose-600",
                borderColor: "border-rose-500",
                backgroundColor: "bg-rose-50",
            },
            {
                key: "tu",
                label: "TU",
                href: route("products.major", "tu"),
                iconColor: "text-teal-600",
                borderColor: "border-teal-500",
                backgroundColor: "bg-teal-50",
            },
            {
                key: "kkpi",
                label: "KKPI",
                href: route("products.major", "kkpi"),
                iconColor: "text-sky-600",
                borderColor: "border-sky-500",
                backgroundColor: "bg-sky-50",
            },
        ];

        const summaryCards: StatisticInfo[] = [
            {
                base_color: "emerald",
                title: "Jumlah Keseluruhan Gudang",
                total: props.count_data.all,
                unit: "item",
                icon_title: BoxCheckmark24Regular,
                icon_status: ArrowTrending20Regular,
                title_status: "Total aset tercatat",
            },
            {
                base_color: "amber",
                title: "Jumlah Permintaan Barang",
                total: props.count_data.request_item,
                unit: "pengajuan",
                href: requestIndexHref,
                icon_title: BoxMultiple24Regular,
                icon_status: Clock20Regular,
                title_status: "Menunggu ditinjau",
            },
            {
                base_color: "blue",
                title: "Jurusan Terpantau",
                total: majorList.length,
                unit: "jurusan",
                icon_title: Home24Regular,
                icon_status: CheckmarkCircle20Regular,
                title_status: "Gudang aktif",
            },
        ];

        function createColumnsDetailTransactions() {
            return [
                {
                    title: "#",
                    key: "index",
                    width: 60,
                    align: "center" as const,
                    render: (_row: any, index: number) => index + 1,
                },
                {
                    title: "Nama Barang",
                    key: "brand_name",
                    minWidth: 160,
                    className: "font-semibold text-slate-700",
                },
                {
                    title: "Jumlah Ajuan",
                    key: "quantity",
                    width: 130,
                    render(row: RequestItem) {
                        return h("div", { class: "font-medium" }, [
                            h("span", { class: "font-bold" }, row.quantity),
                            h(
                                "span",
                                {
                                    class: "text-xs text-slate-400 ml-1 lowercase",
                                },
                                row.unit,
                            ),
                        ]);
                    },
                },
                {
                    title: "Alasan Pengajuan",
                    key: "reason_request",
                    minWidth: 200,
                    className: "text-slate-500 text-xs",
                },
                {
                    title: "Status",
                    key: "request_status",
                    width: 140,
                    align: "center" as const,
                    render(row: RequestItem) {
                        const status = row.request_status.toUpperCase();
                        let type: "success" | "warning" | "error" | "info" =
                            "info";

                        if (status === "APPROVED" || status === "SETUJU")
                            type = "success";
                        else if (status === "PENDING" || status === "PROSES")
                            type = "warning";
                        else if (status === "REJECTED" || status === "TOLAK")
                            type = "error";

                        return h(
                            NTag,
                            {
                                size: "small",
                                type,
                                bordered: false,
                                class: "font-bold rounded text-[11px] tracking-wide px-2",
                            },
                            { default: () => status },
                        );
                    },
                },
                {
                    title: "Jurusan",
                    key: "major_request",
                    width: 140,
                    align: "center" as const,
                    render(row: RequestItem) {
                        return h(
                            NTag,
                            {
                                size: "small",
                                bordered: true,
                                class: "font-bold rounded text-[11px] uppercase tracking-wider",
                            },
                            { default: () => row.major_request },
                        );
                    },
                },
            ];
        }

        return {
            userName,
            todayLabel,
            quickActions,
            summaryCards,
            majorList,
            requestIndexHref,
            BoxCheckmark24Regular,
            BoxArrowUp24Regular,
            BoxMultiple24Regular,
            CalendarLtr20Regular,
            detailTransactionColumns: createColumnsDetailTransactions(),
            router,
        };
    },
    components: {
        ItemCard,
        StatisticCard,
        RealtimeClock,
        NDataTable,
        NButton,
        Link,
        Empty,
    },
    layout: AuthenticatedLayout,
});
</script>
