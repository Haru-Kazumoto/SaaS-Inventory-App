<template>
    <div
        class="bg-white rounded border border-slate-200 p-6 h-[500px] flex flex-col"
    >
        <div
            class="flex items-start justify-between gap-3 pb-4 border-b border-slate-100"
        >
            <div class="min-w-0">
                <p
                    class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400"
                >
                    Statistik
                </p>
                <p class="text-base font-bold text-slate-900 tracking-tight">
                    Pergerakan Barang
                </p>
                <p class="text-xs text-slate-500 mt-0.5">
                    Barang masuk & keluar khusus habis pakai per bulan
                </p>

                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <span
                        class="inline-flex items-center gap-1.5 rounded border border-slate-200 bg-slate-50 px-2 py-1 text-[11px] font-medium text-slate-600"
                    >
                        <span
                            class="h-2 w-2 bg-[#2A126C]"
                            aria-hidden="true"
                        ></span>
                        Masuk
                        <span class="font-bold tabular-nums text-slate-900">
                            {{ totalRestock }}
                        </span>
                    </span>

                    <span
                        class="inline-flex items-center gap-1.5 rounded border border-slate-200 bg-slate-50 px-2 py-1 text-[11px] font-medium text-slate-600"
                    >
                        <span
                            class="h-2 w-2 bg-emerald-600"
                            aria-hidden="true"
                        ></span>
                        Keluar
                        <span class="font-bold tabular-nums text-slate-900">
                            {{ totalOut }}
                        </span>
                    </span>
                </div>
            </div>

            <n-date-picker
                v-model:value="statisticYear"
                type="year"
                @update-value="handleChangePeriod"
                size="small"
                class="shrink-0"
            />
        </div>

        <div class="flex-1 pt-4 min-h-0">
            <n-spin :show="loading">
                <div class="relative w-full h-[320px]">
                    <canvas
                        ref="transactionChartRef"
                        class="w-full h-full"
                    ></canvas>
                </div>
            </n-spin>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { NDatePicker, NSpin } from "naive-ui";
import { Statistic } from "@/types/global-object";

const props = defineProps<{
    gradual_statistics: Statistic[];
    period: number;
}>();

const BRAND = "#2A126C";
const EMERALD = "#059669";

const loading = ref(false);
const getTimePeriod = ref<number | null>(
    props.period ? new Date(props.period, 0, 1).getTime() : null,
);
const statisticYear = ref<number | null>(getTimePeriod.value);
const transactionChartRef = ref<HTMLCanvasElement | null>(null);
const chartInstance = ref<any>(null);

const totalRestock = computed(() =>
    props.gradual_statistics.reduce(
        (total, item) => total + Number(item.restock_total || 0),
        0,
    ),
);

const totalOut = computed(() =>
    props.gradual_statistics.reduce(
        (total, item) => total + Number(item.out_total || 0),
        0,
    ),
);

const months = [
    "Jan",
    "Feb",
    "Mar",
    "Apr",
    "Mei",
    "Jun",
    "Jul",
    "Agt",
    "Sep",
    "Okt",
    "Nov",
    "Des",
];

async function handleChangePeriod() {
    await nextTick();
    loading.value = true;
    router.get(
        route("dashboard") as any,
        { period: new Date(statisticYear.value).getFullYear() },
        { onSuccess: () => (loading.value = false) },
    );
}

onMounted(() => {
    const ctx = transactionChartRef.value;
    if (!ctx) return;

    chartInstance.value = new Chart(ctx, {
        type: "line",
        data: {
            labels: months,
            datasets: [
                {
                    label: "Barang Masuk",
                    data: props.gradual_statistics.map((d) => d.restock_total),
                    borderColor: BRAND,
                    backgroundColor: "rgba(42,18,108,0.06)",
                    borderWidth: 2,
                    tension: 0.35,
                    fill: true,
                    pointRadius: 3,
                    pointBackgroundColor: BRAND,
                    pointBorderColor: "#fff",
                    pointBorderWidth: 1.5,
                },
                {
                    label: "Barang Keluar",
                    data: props.gradual_statistics.map((d) => d.out_total),
                    borderColor: EMERALD,
                    backgroundColor: "rgba(5,150,105,0.05)",
                    borderDash: [5, 3],
                    borderWidth: 2,
                    tension: 0.35,
                    fill: true,
                    pointRadius: 3,
                    pointBackgroundColor: EMERALD,
                    pointBorderColor: "#fff",
                    pointBorderWidth: 1.5,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: "top",
                    align: "end",
                    labels: {
                        color: "#475569",
                        boxWidth: 8,
                        boxHeight: 8,
                        usePointStyle: true,
                        pointStyle: "rectRounded",
                        padding: 16,
                        font: { size: 11, weight: "500" },
                    },
                },
                tooltip: {
                    mode: "index",
                    intersect: false,
                    backgroundColor: "#0F172A",
                    titleColor: "#F1F5F9",
                    bodyColor: "#94A3B8",
                    padding: 12,
                    cornerRadius: 4,
                    displayColors: true,
                    boxWidth: 8,
                    boxHeight: 8,
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        color: "#94A3B8",
                        maxRotation: 0,
                        font: { size: 11 },
                    },
                    border: { display: false },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: "rgba(15,23,42,0.05)" },
                    ticks: {
                        color: "#94A3B8",
                        stepSize: 1,
                        font: { size: 11 },
                        callback: (v: any) => Number(v).toFixed(0),
                    },
                    border: { display: false },
                },
            },
        },
    });
});

onBeforeUnmount(() => {
    chartInstance.value?.destroy();
    chartInstance.value = null;
});
</script>
