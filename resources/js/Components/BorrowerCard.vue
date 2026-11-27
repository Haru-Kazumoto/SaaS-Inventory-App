<template>
    <n-card
        hoverable
        :bordered="false"
        class="flex rounded-xl border border-blue-100 bg-blue-50/80 transition duration-200 hover:shadow-md hover:-translate-y-[1px] cursor-pointer"
    >
        <div class="flex items-center gap-3 w-full">
            <!-- Avatar inisial -->
            <div
                class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-500 text-white font-semibold text-sm shrink-0"
            >
                {{ initialLetter }}
            </div>

            <!-- Info utama -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex flex-col min-w-0">
                        <span class="text-sm font-semibold text-slate-900 truncate">
                            {{ borrowerInfo.borrower_name }}
                        </span>
                        <span class="text-xs text-slate-500 truncate">
                            {{ borrowerInfo.borrower_phone || '-' }}
                        </span>
                    </div>

                    <!-- Status tag -->
                    <n-tag
                        round
                        size="small"
                        :type="statusType"
                        class="shrink-0 text-[11px]"
                    >
                        {{ statusLabel }}
                    </n-tag>
                </div>

                <div class="mt-1 flex items-center justify-between text-[11px] text-slate-500">
                    <span class="truncate">
                        Dibuat: {{ formattedDate }}
                    </span>
                    <span class="font-medium text-slate-700">
                        {{ borrowerInfo.products_count }} barang
                    </span>
                </div>
            </div>
        </div>
    </n-card>
</template>

<script lang="ts">
import { defineComponent, computed, PropType } from "vue";
import { NCard, NTag } from "naive-ui";

interface TodaysBorrower {
    borrower_name: string;
    borrower_phone: string;
    created_at: string;
    products_count: number;
    computed_status: string;
}

export default defineComponent({
    name: "BorrowerCard",
    props: {
        borrowerInfo: {
            type: Object as PropType<TodaysBorrower>,
            required: true,
        },
    },
    setup(props) {
        const initialLetter = computed(() =>
            (props.borrowerInfo.borrower_name || "?")
                .charAt(0)
                .toUpperCase()
        );

        const formattedDate = computed(() => {
            if (!props.borrowerInfo.created_at) return "-";
            const d = new Date(props.borrowerInfo.created_at);
            if (Number.isNaN(d.getTime())) return props.borrowerInfo.created_at;
            return d.toLocaleString("id-ID", {
                day: "2-digit",
                month: "short",
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            });
        });

        const statusType = computed(() => {
            const s = props.borrowerInfo.computed_status?.toUpperCase();
            if (s === "COMPLETED") return "success";
            if (s === "PARTIAL_RETURNED") return "info";
            if (s === "POSTED") return "warning";
            return "default";
        });

        const statusLabel = computed(() => {
            const s = props.borrowerInfo.computed_status?.toUpperCase();
            if (s === "COMPLETED") return "SELESAI";
            if (s === "PARTIAL_RETURNED") return "BERTAHAP";
            if (s === "POSTED") return "DIPINJAM";
            return props.borrowerInfo.computed_status || "Status tidak diketahui";
        });

        return {
            initialLetter,
            formattedDate,
            statusType,
            statusLabel,
        };
    },
    components: {
        NTag,
        NCard,
    },
});
</script>

<style scoped>
/* opsional: sedikit haluskan hover */
</style>
