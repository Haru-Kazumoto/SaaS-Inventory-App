<template>
    <div class="flex flex-col gap-5">
        <!-- HERO / SAPAAN -->
        <section class="relative overflow-hidden rounded border border-[#23105a] bg-[#2A126C] text-white">
            <div class="relative p-6 sm:p-7">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0 max-w-2xl">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="h-1 w-8 bg-[#F5D90A]" aria-hidden="true"></span>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#F5D90A]">Dashboard Organisasi</p>
                            <span
                                v-if="organizationName"
                                class="rounded border border-white/15 bg-white/5 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-white/70"
                            >
                                {{ organizationName }}
                            </span>
                        </div>
                        <h1 class="mt-3 text-2xl font-bold tracking-tight sm:text-3xl">Halo, {{ userName }}</h1>
                        <p class="mt-2 max-w-xl text-sm leading-relaxed text-white/70">
                            Pantau persediaan dan perputaran barang di seluruh {{ t("unit_plural").toLowerCase() }}.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-stretch gap-3">
                        <div class="flex items-center gap-3 rounded border border-white/15 bg-white/[0.06] px-4 py-2.5">
                            <div class="flex h-9 w-9 items-center justify-center rounded bg-white/10 text-[#F5D90A]">
                                <n-icon :component="CalendarLtr20Regular" size="20" />
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-white/50">Tanggal</p>
                                <p class="mt-1 text-sm font-semibold text-white">{{ todayLabel }}</p>
                            </div>
                        </div>
                        <RealtimeClock variant="dark" />
                    </div>
                </div>

                <!-- AKSI CEPAT -->
                <div class="mt-6 flex flex-wrap items-center gap-2 border-t border-white/10 pt-4">
                    <button
                        v-for="action in quickActions"
                        :key="action.label"
                        type="button"
                        class="inline-flex items-center gap-2 rounded border px-3 py-2 text-xs font-semibold transition-colors duration-150"
                        :class="
                            action.primary
                                ? 'border-[#F5D90A] bg-[#F5D90A] text-[#2A126C] hover:bg-[#e8cc09]'
                                : 'border-white/20 bg-white/5 text-white hover:border-white/40 hover:bg-white/10'
                        "
                        @click="router.visit(action.href)"
                    >
                        <n-icon :component="action.icon" size="16" />
                        {{ action.label }}
                    </button>
                </div>
            </div>
        </section>

        <!-- RINGKASAN ANGKA -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <StatisticCard v-for="item in summaryCards" :key="item.title" v-bind="item" />
        </div>

        <!-- RINGKASAN PER UNIT -->
        <section class="rounded border border-slate-200 bg-white">
            <div class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Distribusi Barang</p>
                    <h2 class="text-base font-bold tracking-tight text-slate-900">Ringkasan per {{ t("unit") }}</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Pilih salah satu {{ t("unit").toLowerCase() }} untuk melihat daftar barangnya.</p>
                </div>
                <span
                    class="shrink-0 self-start rounded border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-semibold tabular-nums text-slate-600 sm:self-auto"
                >
                    {{ units.length }} {{ t("unit_plural") }}
                </span>
            </div>

            <div v-if="units.length" class="grid grid-cols-1 gap-3 p-5 sm:grid-cols-2 lg:grid-cols-3">
                <button
                    v-for="(unit, i) in units"
                    :key="unit.id"
                    type="button"
                    class="group flex items-center gap-3 rounded border border-slate-200 p-4 text-left transition-colors duration-150 hover:border-[#2A126C]/50"
                    @click="openUnit(unit.id)"
                >
                    <span
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded text-xs font-bold"
                        :class="UNIT_PALETTE[i % UNIT_PALETTE.length]"
                    >
                        {{ (unit.code || unit.name).slice(0, 4).toUpperCase() }}
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-semibold text-slate-900">{{ unit.name }}</span>
                        <span class="text-xs text-slate-500">
                            <b class="tabular-nums text-slate-700">{{ unit.products_count.toLocaleString("id-ID") }}</b> barang
                        </span>
                    </span>
                    <n-icon :component="ArrowRight20Regular" class="text-slate-300 group-hover:text-[#2A126C]" />
                </button>
            </div>
            <div v-else class="py-8"><Empty /></div>
        </section>

        <!-- PENGAJUAN TERBARU -->
        <section class="rounded border border-slate-200 bg-white">
            <div class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Aktivitas</p>
                    <h2 class="text-base font-bold tracking-tight text-slate-900">Pengajuan Barang Terbaru</h2>
                    <p class="mt-0.5 text-xs text-slate-500">10 pengajuan terakhir dari seluruh {{ t("unit_plural").toLowerCase() }}.</p>
                </div>
                <n-button secondary class="shrink-0 self-start sm:self-auto" @click="goRequests">Lihat semua</n-button>
            </div>

            <!-- desktop -->
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-slate-100 bg-slate-50/60 text-[11px] uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-2.5 font-semibold">Nama barang</th>
                            <th class="px-3 py-2.5 text-right font-semibold">Jumlah</th>
                            <th class="px-3 py-2.5 font-semibold">Alasan</th>
                            <th class="px-3 py-2.5 font-semibold">{{ t("unit") }}</th>
                            <th class="px-5 py-2.5 text-right font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="row in request_items.data" :key="row.id">
                            <td class="px-5 py-3 font-semibold text-slate-800">{{ row.item_name }}</td>
                            <td class="whitespace-nowrap px-3 py-3 text-right tabular-nums">
                                <b>{{ row.quantity }}</b> <span class="text-xs lowercase text-slate-400">{{ row.uom }}</span>
                            </td>
                            <td class="max-w-xs truncate px-3 py-3 text-xs text-slate-500" :title="row.reason ?? ''">{{ row.reason || "-" }}</td>
                            <td class="whitespace-nowrap px-3 py-3 text-xs font-medium text-slate-700">{{ row.unit_name }}</td>
                            <td class="px-5 py-3 text-right"><RequestStatusBadge :status="row.status" /></td>
                        </tr>
                        <tr v-if="!request_items.data.length">
                            <td colspan="5" class="py-8"><Empty /></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- mobile -->
            <ul class="divide-y divide-slate-100 md:hidden">
                <li v-for="row in request_items.data" :key="row.id" class="flex flex-col gap-1 px-4 py-3">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-sm font-semibold text-slate-900">{{ row.item_name }}</p>
                        <RequestStatusBadge :status="row.status" />
                    </div>
                    <p class="text-xs text-slate-500">
                        <b class="tabular-nums text-slate-700">{{ row.quantity }}</b> {{ row.uom }} · {{ row.unit_name }}
                    </p>
                </li>
                <li v-if="!request_items.data.length" class="py-8"><Empty /></li>
            </ul>
        </section>
    </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { NButton, NIcon } from "naive-ui";
import {
    ArrowRight20Regular,
    ArrowTrending20Regular,
    BoxArrowUp24Regular,
    BoxCheckmark24Regular,
    BoxMultiple24Regular,
    BoxToolbox24Regular,
    CalendarLtr20Regular,
    Clock20Regular,
    HandRight24Regular,
    PeopleSettings20Regular,
} from "@vicons/fluent";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import StatisticCard from "@/Components/Feature/Dashboard/StatisticCard.vue";
import RealtimeClock from "@/Components/Feature/Dashboard/RealtimeClock.vue";
import RequestStatusBadge from "@/Components/Feature/Procurement/RequestStatusBadge.vue";
import Empty from "@/Components/Empty.vue";
import { useTerms } from "@/Composables/useTerms";
import type { PaginationResponse } from "@/types/global-object";

defineOptions({ layout: AuthenticatedLayout });

interface UnitSummary {
    id: number;
    name: string;
    code: string | null;
    products_count: number;
}

interface RequestRow {
    id: number;
    item_name: string;
    quantity: number;
    uom: string;
    reason: string | null;
    status: "PENDING" | "APPROVED" | "REJECTED";
    unit_name: string;
    created_at: string;
}

const props = defineProps<{
    count_data: { all: number; request_item: number; borrowed: number };
    units: UnitSummary[];
    request_items: PaginationResponse<RequestRow>;
}>();

const { t } = useTerms();
const page = usePage();
const userName = (page.props as any).auth.user.fullname;
const organizationName = computed(() => (page.props as any).organization?.name ?? null);

const todayLabel = new Intl.DateTimeFormat("id-ID", { weekday: "long", day: "numeric", month: "short", year: "numeric" }).format(new Date());

const UNIT_PALETTE = [
    "bg-orange-50 text-orange-700",
    "bg-violet-50 text-violet-700",
    "bg-indigo-50 text-indigo-700",
    "bg-rose-50 text-rose-700",
    "bg-teal-50 text-teal-700",
    "bg-sky-50 text-sky-700",
];

const quickActions = [
    { label: "Pengajuan Barang", href: route("procurement.index"), icon: BoxToolbox24Regular, primary: true },
    { label: "Anggota", href: route("members.index"), icon: PeopleSettings20Regular },
    { label: "Barang Masuk", href: route("movements.restocks"), icon: BoxArrowUp24Regular },
];

function openUnit(id: number) {
    router.visit(route("products.index", { unit_id: id }));
}

function goRequests() {
    router.visit(route("procurement.index"));
}

const summaryCards = computed(() => [
    {
        base_color: "emerald",
        title: "Total Barang",
        total: props.count_data.all,
        unit: "barang",
        href: route("products.index"),
        icon_title: BoxCheckmark24Regular,
        icon_status: ArrowTrending20Regular,
        title_status: `Di ${props.units.length} ${t("unit_plural").toLowerCase()}`,
    },
    {
        base_color: "blue",
        title: "Sedang Dipinjam",
        total: props.count_data.borrowed,
        unit: "unit",
        href: route("movements.loaned"),
        icon_title: HandRight24Regular,
        icon_status: Clock20Regular,
        title_status: "Belum dikembalikan",
    },
    {
        base_color: "amber",
        title: "Pengajuan Barang",
        total: props.count_data.request_item,
        unit: "pengajuan",
        href: route("procurement.index"),
        icon_title: BoxMultiple24Regular,
        icon_status: Clock20Regular,
        title_status: "Seluruh pengajuan",
    },
]);
</script>
