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
                                Dashboard Inventaris
                            </p>

                            <span
                                v-if="majorName"
                                class="rounded border border-white/15 bg-white/5 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-white/70"
                            >
                                {{ majorName }}
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
                            Atur barang dalam gudang dengan mudah, cepat, dan
                            terstruktur. Pantau stok, peminjaman, serta
                            pengajuan barang dari satu tempat.
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
                v-for="item in statistics_info"
                :key="item.title"
                v-bind="item"
            />
        </div>

        <!-- KONTEN UTAMA -->
        <div class="grid grid-cols-1 items-stretch gap-4 xl:grid-cols-3">
            <div class="xl:col-span-2">
                <StatisticChart
                    :gradual_statistics="gradual_statistics"
                    :period="period"
                />
            </div>

            <div class="xl:col-span-1">
                <TodaysBorrower
                    :borrowers="todays_borrower"
                    :view-all-href="borrowingHref"
                />
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { NDatePicker, NSpin } from "naive-ui";
import { defineComponent } from "vue";
import type { Component } from "vue";
import {
    Add24Regular,
    ArrowTrending20Regular,
    BoxArrowUp24Regular,
    BoxCheckmark24Regular,
    BoxMultiple24Regular,
    CalendarLtr20Regular,
    Clock20Regular,
    Warning20Regular,
} from "@vicons/fluent";
import { Link, usePage } from "@inertiajs/vue3";
import RealtimeClock from "@/Components/Feature/Dashboard/RealtimeClock.vue";
import StatisticChart from "@/Components/Feature/Dashboard/StatisticChart.vue";
import { Statistic } from "@/types/global-object";
import StatisticCard from "@/Components/Feature/Dashboard/StatisticCard.vue";
import TodaysBorrower from "@/Components/Feature/Dashboard/TodaysBorrower.vue";

interface CountObject {
    total_products: number;
    total_borrowed: number;
    total_request: number;
}

interface TodaysBorrowerRow {
    fullname: string;
    phone: string;
    created_at: string;
    total_items: number;
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
        count: { type: Object as () => CountObject, required: true },
        gradual_statistics: {
            type: Array as () => Statistic[],
            required: true,
        },
        todays_borrower: {
            type: Object as () => TodaysBorrowerRow[],
            required: true,
        },
        period: { type: Number, required: true },
        transaction: { type: Object, required: false },
    },
    setup(props) {
        const page = usePage();
        const userName = page.props.auth.user.fullname;
        const majorName = page.props.auth.user.major?.name ?? null;
        const borrowingHref = route("borrowing.index");

        const todayLabel = new Intl.DateTimeFormat("id-ID", {
            weekday: "long",
            day: "numeric",
            month: "short",
            year: "numeric",
        }).format(new Date());

        const quickActions: QuickAction[] = [
            {
                label: "Tambah Barang",
                href: route("products.create"),
                icon: Add24Regular,
                primary: true,
            },
            {
                label: "Catat Peminjaman",
                href: route("borrowing.create"),
                icon: BoxArrowUp24Regular,
            },
            {
                label: "Ajukan Barang",
                href: route("request-item.create"),
                icon: BoxMultiple24Regular,
            },
        ];

        const statistics_info: StatisticInfo[] = [
            {
                base_color: "blue",
                title: "Barang dalam Gudang",
                total: props.count.total_products,
                unit: "item",
                href: route("products.index"),
                icon_title: BoxCheckmark24Regular,
                icon_status: ArrowTrending20Regular,
                title_status: "Stok tersedia di gudang",
            },
            {
                base_color: "emerald",
                title: "Dalam Peminjaman",
                total: props.count.total_borrowed,
                unit: "item",
                href: borrowingHref,
                icon_title: BoxArrowUp24Regular,
                icon_status: Clock20Regular,
                title_status: "Sedang dipinjam",
            },
            {
                base_color: "amber",
                title: "Pengajuan Barang",
                total: props.count.total_request,
                unit: "pengajuan",
                href: route("request-item.index"),
                icon_title: BoxMultiple24Regular,
                icon_status: Warning20Regular,
                title_status: "Menunggu persetujuan",
            },
        ];

        return {
            userName,
            majorName,
            todayLabel,
            quickActions,
            statistics_info,
            borrowingHref,
            CalendarLtr20Regular,
            Add24Regular,
            BoxArrowUp24Regular,
            BoxMultiple24Regular,
            BoxCheckmark24Regular,
            ArrowTrending20Regular,
            Clock20Regular,
            Warning20Regular,
        };
    },
    components: {
        NDatePicker,
        NSpin,
        RealtimeClock,
        StatisticChart,
        StatisticCard,
        TodaysBorrower,
        Link,
    },
    layout: AuthenticatedLayout,
});
</script>
