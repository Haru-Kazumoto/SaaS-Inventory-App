<template>
    <div
        class="bg-white rounded border border-slate-200 p-6 flex flex-col h-[520px]"
    >
        <!-- Header -->
        <div
            class="flex items-start justify-between gap-3 pb-4 border-b border-slate-100"
        >
            <div class="min-w-0">
                <p
                    class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400"
                >
                    Hari Ini
                </p>
                <p class="text-base font-bold text-slate-900 tracking-tight">
                    Peminjam Hari Ini
                </p>
                <p class="text-xs text-slate-500 mt-0.5">
                    Transaksi yang dibuat hari ini
                </p>
            </div>

            <div
                class="shrink-0 flex items-center gap-1.5 rounded border border-[#2A126C]/15 bg-[#2A126C]/[0.05] px-2 py-1 text-[11px] font-semibold tabular-nums text-[#2A126C]"
            >
                <span>{{ borrowerCount }} orang</span>
                <span class="text-slate-300">·</span>
                <span>{{ totalItems }} item</span>
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto pt-3 min-h-0 custom-scroll">
            <!-- Sticky Search -->
            <div class="sticky top-0 z-10 bg-white pb-2">
                <n-input
                    size="small"
                    class="w-full"
                    placeholder="Cari nama peminjam"
                    v-model:value="search"
                    clearable
                >
                    <template #prefix>
                        <n-icon :component="Person24Regular"></n-icon>
                    </template>
                </n-input>
            </div>

            <!-- List -->
            <template v-if="filteredBorrowers.length > 0">
                <div
                    v-for="(row, idx) in filteredBorrowers"
                    :key="`${row.fullname}-${row.created_at}-${idx}`"
                    class="group flex items-center gap-3 border-b border-slate-100 bg-white p-3 last:border-b-0 hover:border-slate-200 hover:bg-slate-50/60 transition-colors duration-150"
                >
                    <!-- Avatar -->
                    <div
                        class="w-9 h-9 shrink-0 flex items-center justify-center rounded border border-slate-200 bg-slate-50 text-slate-600 text-[11px] font-semibold"
                    >
                        {{ getInitials(row.fullname) }}
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-900 truncate">
                            {{ row.fullname }}
                        </p>

                        <p
                            class="text-[11px] text-slate-500 mt-0.5 tabular-nums truncate"
                        >
                            {{ row.total_items }} item
                            <span class="text-slate-300">·</span>
                            {{ formatWhen(row.created_at) }}
                        </p>
                    </div>

                    <!-- Aksen -->
                    <span
                        class="h-1.5 w-1.5 shrink-0 rounded-full bg-[#2A126C]/[0.35] group-hover:bg-[#2A126C] transition-colors duration-150"
                        aria-hidden="true"
                    ></span>
                </div>
            </template>

            <!-- No result for search -->
            <template v-else-if="props.borrowers.length > 0">
                <div
                    class="flex flex-col items-center justify-center py-10 text-slate-400"
                >
                    <component
                        :is="Search20Regular"
                        class="w-8 h-8 mb-2 opacity-40"
                    />

                    <p class="text-sm">
                        Tidak ditemukan peminjam dengan nama
                        <span class="font-medium text-slate-600">
                            "{{ search }}"
                        </span>
                        .
                    </p>
                </div>
            </template>

            <!-- Empty -->
            <template v-else>
                <div
                    class="flex flex-col items-center justify-center py-10 text-slate-400"
                >
                    <component
                        :is="BoxMultiple24Regular"
                        class="w-8 h-8 mb-2 opacity-30"
                    />

                    <p class="text-sm">Belum ada peminjam hari ini</p>
                    <p class="text-xs text-slate-400 mt-1">
                        Peminjam yang aktif akan muncul di sini.
                    </p>
                </div>
            </template>
        </div>

        <div
            v-if="viewAllHref"
            class="mt-3 border-t border-slate-100 pt-3 shrink-0 flex items-center justify-between"
        >
            <span class="text-[11px] font-semibold text-slate-500">
                Ringkasan hari ini
            </span>

            <Link
                :href="viewAllHref"
                class="group inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500 hover:text-[#2A126C] transition-colors duration-150"
            >
                Lihat semua peminjaman
                <ArrowRight20Regular
                    class="w-3.5 h-3.5 transition-transform duration-150 group-hover:translate-x-0.5"
                />
            </Link>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { Link } from "@inertiajs/vue3";
import {
    ArrowRight20Regular,
    BoxMultiple24Regular,
    Person24Regular,
    Search20Regular,
} from "@vicons/fluent";
import { NInput, NIcon } from "naive-ui";

interface Borrower {
    fullname: string;
    phone: string;
    created_at: string;
    total_items: number;
}

const props = withDefaults(
    defineProps<{
        borrowers: Borrower[];
        viewAllHref?: string;
    }>(),
    {},
);

/**
 * Search
 */
const search = ref("");
const debouncedSearch = ref("");

let debounceTimeout: ReturnType<typeof setTimeout> | null = null;

watch(search, (value) => {
    if (debounceTimeout) {
        clearTimeout(debounceTimeout);
    }

    debounceTimeout = setTimeout(() => {
        debouncedSearch.value = value;
    }, 300);
});

/**
 * Filtered Borrowers
 */
const filteredBorrowers = computed(() => {
    if (!debouncedSearch.value.trim()) {
        return props.borrowers;
    }

    const query = debouncedSearch.value.trim().toLowerCase();

    return props.borrowers.filter((borrower) =>
        borrower.fullname.toLowerCase().includes(query),
    );
});

/**
 * Ringkasan
 */
const borrowerCount = computed(() => props.borrowers.length);
const totalItems = computed(() =>
    props.borrowers.reduce(
        (total, borrower) => total + Number(borrower.total_items || 0),
        0,
    ),
);

/**
 * Utils
 */
const getInitials = (name: string) => {
    return name
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((word) => word[0])
        .join("")
        .toUpperCase();
};

const formatWhen = (date: string) => {
    const value = new Date(date);
    if (Number.isNaN(value.getTime())) return "—";

    const today = new Date();
    const sameDay =
        value.getFullYear() === today.getFullYear() &&
        value.getMonth() === today.getMonth() &&
        value.getDate() === today.getDate();

    return sameDay
        ? value.toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" })
        : value.toLocaleDateString("id-ID", {
              day: "numeric",
              month: "short",
          });
};
</script>

<style scoped>
.custom-scroll::-webkit-scrollbar {
    width: 4px;
}
.custom-scroll::-webkit-scrollbar-thumb {
    border-radius: 2px;
    background-color: rgba(148, 163, 184, 0.5);
}
.custom-scroll::-webkit-scrollbar-track {
    background-color: transparent;
}
</style>
