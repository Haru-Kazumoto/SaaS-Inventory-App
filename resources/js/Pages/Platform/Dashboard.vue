<template>
    <div class="flex flex-col gap-5">
        <TitlePage
            eyebrow="Platform"
            title="Ringkasan Bisnis"
            subTitle="Pantau pelanggan, pendapatan, dan tagihan seluruh organisasi."
        >
            <template #action>
                <div class="flex w-full gap-2 sm:w-auto">
                    <n-button class="flex-1 sm:flex-none" @click="go('platform.invoices.index', { status: 'overdue' })">
                        <template #icon><n-icon :component="Warning20Regular" /></template>
                        Tagihan terlambat
                    </n-button>
                    <n-button type="primary" class="flex-1 sm:flex-none" @click="go('platform.organizations.index')">
                        <template #icon><n-icon :component="BuildingMultiple20Regular" /></template>
                        Kelola organisasi
                    </n-button>
                </div>
            </template>
        </TitlePage>

        <!-- KPI -->
        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
            <div v-for="k in kpis" :key="k.label" class="relative overflow-hidden rounded border border-slate-200 bg-white p-4 pl-5">
                <span class="absolute inset-y-0 left-0 w-[3px]" :class="k.accent" aria-hidden="true"></span>
                <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500">{{ k.label }}</p>
                <p class="mt-1.5 text-2xl font-bold leading-tight tabular-nums text-slate-900" :title="k.full">{{ k.value }}</p>
                <p class="mt-1 text-[11px] text-slate-500">
                    <span v-if="k.delta !== undefined" :class="k.delta >= 0 ? 'text-emerald-600' : 'text-rose-600'" class="font-semibold">
                        {{ k.delta >= 0 ? "▲" : "▼" }} {{ Math.abs(k.delta) }}%
                    </span>
                    {{ k.hint }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <!-- Tren pendapatan -->
            <section class="rounded border border-slate-200 bg-white lg:col-span-2">
                <header class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">6 bulan terakhir</p>
                        <h2 class="text-sm font-bold text-slate-900">Pendapatan</h2>
                    </div>
                    <span class="text-xs tabular-nums text-slate-500">Total {{ formatIDR(trendTotal) }}</span>
                </header>
                <div class="flex h-56 items-end gap-2 px-5 pb-4 pt-6 sm:gap-4">
                    <div v-for="m in revenue_trend" :key="m.month" class="flex h-full flex-1 flex-col items-center justify-end gap-1.5">
                        <span class="text-[10px] font-semibold tabular-nums text-slate-500">{{ m.total ? formatIDRShort(m.total) : "" }}</span>
                        <div
                            class="w-full max-w-12 rounded-t transition-all duration-300"
                            :class="m.month === currentMonth ? 'bg-[#2A126C]' : 'bg-[#2A126C]/25'"
                            :style="{ height: `${Math.max(2, (m.total / trendMax) * 100)}%` }"
                            :title="`${m.label}: ${formatIDR(m.total)}`"
                        ></div>
                        <span class="text-[10px] text-slate-400">{{ m.label }}</span>
                    </div>
                </div>
            </section>

            <!-- Organisasi per status & paket -->
            <section class="rounded border border-slate-200 bg-white">
                <header class="border-b border-slate-100 px-5 py-4">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">Pelanggan</p>
                    <h2 class="text-sm font-bold text-slate-900">{{ stats.organizations_total }} organisasi</h2>
                </header>
                <div class="px-5 py-4">
                    <div class="flex h-2 overflow-hidden rounded-full bg-slate-100">
                        <div
                            v-for="s in statusBreakdown"
                            :key="s.key"
                            :class="s.meta.dot"
                            :style="{ width: `${(s.count / Math.max(1, stats.organizations_total)) * 100}%` }"
                        ></div>
                    </div>
                    <ul class="mt-3 grid grid-cols-2 gap-x-3 gap-y-1.5">
                        <li v-for="s in statusBreakdown" :key="s.key">
                            <button
                                type="button"
                                class="flex w-full items-center gap-1.5 text-left text-xs text-slate-600 hover:text-[#2A126C]"
                                @click="go('platform.organizations.index', { status: s.key })"
                            >
                                <span class="h-2 w-2 rounded-full" :class="s.meta.dot"></span>
                                {{ s.meta.label }}
                                <b class="ml-auto tabular-nums text-slate-900">{{ s.count }}</b>
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="border-t border-slate-100 px-5 py-4">
                    <p class="mb-2 text-xs font-semibold text-slate-600">Paket berjalan</p>
                    <p v-if="!plan_distribution.length" class="text-xs text-slate-400">Belum ada langganan.</p>
                    <ul class="flex flex-col gap-2">
                        <li v-for="p in plan_distribution" :key="p.name" class="flex items-center gap-2 text-xs">
                            <span class="w-20 truncate text-slate-600">{{ p.name }}</span>
                            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-[#2A126C]" :style="{ width: `${(p.total / planMax) * 100}%` }"></div>
                            </div>
                            <b class="w-6 text-right tabular-nums text-slate-900">{{ p.total }}</b>
                        </li>
                    </ul>
                </div>
            </section>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <!-- Tagihan terlambat -->
            <section class="rounded border border-slate-200 bg-white">
                <header class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <h2 class="text-sm font-bold text-slate-900">Tagihan terlambat</h2>
                    <button class="text-xs font-semibold text-[#2A126C] hover:underline" @click="go('platform.invoices.index', { status: 'overdue' })">
                        Lihat semua
                    </button>
                </header>
                <p v-if="!overdue_invoices.length" class="px-5 py-8 text-center text-sm text-slate-400">
                    Tidak ada tagihan terlambat. 🎉
                </p>
                <ul v-else class="divide-y divide-slate-100">
                    <li v-for="i in overdue_invoices" :key="i.id">
                        <button type="button" class="flex w-full items-center gap-3 px-5 py-3 text-left hover:bg-slate-50/60" @click="go('platform.invoices.show', i.id)">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-900">{{ i.organization }}</p>
                                <p class="font-mono text-[11px] text-slate-400">{{ i.number }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold tabular-nums text-slate-900">{{ formatIDR(i.total) }}</p>
                                <p class="text-[11px] font-semibold text-rose-600">terlambat {{ i.days_late }} hari</p>
                            </div>
                        </button>
                    </li>
                </ul>
            </section>

            <!-- Pembayaran terbaru -->
            <section class="rounded border border-slate-200 bg-white">
                <header class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <h2 class="text-sm font-bold text-slate-900">Pembayaran terbaru</h2>
                    <button class="text-xs font-semibold text-[#2A126C] hover:underline" @click="go('platform.payments.index')">
                        Lihat semua
                    </button>
                </header>
                <p v-if="!recent_payments.length" class="px-5 py-8 text-center text-sm text-slate-400">Belum ada pembayaran.</p>
                <ul v-else class="divide-y divide-slate-100">
                    <li v-for="p in recent_payments" :key="p.id">
                        <button type="button" class="flex w-full items-center gap-3 px-5 py-3 text-left hover:bg-slate-50/60" @click="go('platform.invoices.show', p.invoice_id)">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded bg-emerald-50 text-emerald-700">
                                <n-icon :component="Checkmark20Regular" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-900">{{ p.organization }}</p>
                                <p class="text-[11px] text-slate-400">
                                    <span class="font-mono">{{ p.invoice_number }}</span> · {{ labelOf(PAYMENT_METHODS, p.method) }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold tabular-nums text-emerald-700">+{{ formatIDR(p.amount) }}</p>
                                <p class="text-[11px] text-slate-400">{{ formatDay(p.paid_at) }}</p>
                            </div>
                        </button>
                    </li>
                </ul>
            </section>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { router } from "@inertiajs/vue3";
import { NButton, NIcon } from "naive-ui";
import { BuildingMultiple20Regular, Checkmark20Regular, Warning20Regular } from "@vicons/fluent";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TitlePage from "@/Components/TitlePage.vue";
import {
    ORGANIZATION_STATUS,
    PAYMENT_METHODS,
    formatDay,
    formatIDR,
    formatIDRShort,
    labelOf,
    metaOf,
} from "@/Components/Feature/Platform/platform-meta";
import { useFlash } from "@/Composables/useFlash";

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps<{
    stats: {
        organizations_total: number;
        organizations_by_status: Record<string, number>;
        users_total: number;
        mrr: number;
        revenue_this_month: number;
        revenue_last_month: number;
        unpaid_count: number;
        unpaid_amount: number;
        overdue_count: number;
        overdue_amount: number;
    };
    revenue_trend: { month: string; label: string; total: number }[];
    plan_distribution: { name: string; total: number }[];
    overdue_invoices: { id: number; number: string; organization: string; total: number; due_at: string; days_late: number }[];
    recent_payments: { id: number; organization: string; invoice_id: number; invoice_number: string; amount: number; method: string; paid_at: string }[];
}>();

useFlash();

function go(name: string, params?: any) {
    router.visit(route(name, params));
}

const revenueDelta = computed(() => {
    const prev = props.stats.revenue_last_month;
    if (!prev) return undefined;
    return Math.round(((props.stats.revenue_this_month - prev) / prev) * 100);
});

const kpis = computed(() => [
    {
        label: "MRR",
        value: formatIDRShort(props.stats.mrr),
        full: formatIDR(props.stats.mrr),
        hint: "pendapatan berulang / bulan",
        accent: "bg-[#2A126C]",
    },
    {
        label: "Pendapatan bulan ini",
        value: formatIDRShort(props.stats.revenue_this_month),
        full: formatIDR(props.stats.revenue_this_month),
        delta: revenueDelta.value,
        hint: "vs bulan lalu",
        accent: "bg-emerald-500",
    },
    {
        label: "Belum dibayar",
        value: formatIDRShort(props.stats.unpaid_amount),
        full: formatIDR(props.stats.unpaid_amount),
        hint: `${props.stats.unpaid_count} tagihan · ${props.stats.overdue_count} terlambat`,
        accent: props.stats.overdue_count ? "bg-rose-500" : "bg-amber-500",
    },
    {
        label: "Organisasi aktif",
        value: String(props.stats.organizations_by_status.active ?? 0),
        full: undefined,
        hint: `${props.stats.organizations_by_status.trial ?? 0} masa coba · ${props.stats.users_total} pengguna`,
        accent: "bg-sky-500",
    },
]);

const statusBreakdown = computed(() =>
    Object.entries(props.stats.organizations_by_status).map(([key, count]) => ({
        key,
        count,
        meta: metaOf(ORGANIZATION_STATUS, key),
    })),
);

const trendMax = computed(() => Math.max(1, ...props.revenue_trend.map((m) => m.total)));
const trendTotal = computed(() => props.revenue_trend.reduce((s, m) => s + m.total, 0));
const planMax = computed(() => Math.max(1, ...props.plan_distribution.map((p) => p.total)));
const currentMonth = new Date().toISOString().slice(0, 7);
</script>
