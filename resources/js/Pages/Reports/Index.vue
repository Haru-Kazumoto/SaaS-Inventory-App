<template>
    <div class="flex flex-col gap-3">
        <TitlePage title="List Laporan" subTitle="Kumpulan laporan bugs yang telah di laporkan" />

        <div class="grid grid-cols-2 gap-4">
            <ItemCard iconColor="text-blue-600" borderColor="border-blue-400" backgroundColor="bg-blue-100"
                titleColor="text-gray-700" subTitleColor="text-blue-700" title="Report Terkirim" :count="count.sent" />
            <ItemCard iconColor="text-green-600" borderColor="border-green-400" backgroundColor="bg-green-100"
                titleColor="text-gray-700" subTitleColor="text-green-700" title="Report Terproses"
                :count="count.proceed" />
        </div>

        <n-card hoverable :bordered="false" class="border border-gray-300 rounded-2xl">
            <div class="flex justify-between mb-2">
                <span class="text-lg font-bold">List pengajuan</span>
                <n-button type="primary" strong @click="visitRoute('reports.create')">
                    <template #icon>
                        <n-icon :component="Add12Filled"></n-icon>
                    </template>
                    Buat Laporan Baru
                </n-button>
            </div>

            <n-data-table :bordered="false" :columns :data="reports" size="small">
                <template #empty>
                    <Empty />
                </template>
            </n-data-table>
        </n-card>
    </div>
</template>

<script lang="ts">
import TitlePage from '@/Components/TitlePage.vue';
import ItemCard from '@/Components/InformationCard.vue';
import Empty from '@/Components/Empty.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { formatDate } from '@/Utils/helper-function';
import { NButton, NCard, NDataTable, NIcon, NTag } from 'naive-ui';
import { defineComponent, h } from 'vue';
import { router } from '@inertiajs/vue3';
import { Add12Filled } from '@vicons/fluent';
import { RowData } from 'naive-ui/es/data-table/src/interface';

export default defineComponent({
    props: {
        reports: { type: Array as () => RowData[], required: true },
        count: { type: Object, required: true }
    },
    setup() {
        function createColumns() {
            return [
                {
                    key: "action",
                    width: 70,
                    render(row: any) {
                        return h(NButton, {
                            type: 'info',
                            size: 'medium',
                            ghost: true,
                            onClick: () => {
                                router.visit(route('reports.show', row.id));
                            }
                        }, {
                            default: () => 'Detail'
                        });
                    }
                },
                {
                    title: "#",
                    key: "index",
                    width: 50,
                    render(row: any, index: number) {
                        return index + 1;
                    }
                },
                {
                    title: "Judul",
                    key: "title",
                    width: 100
                },
                {
                    title: "Deskripsi",
                    key: "description",
                    width: 150,
                },
                {
                    title: "Waktu Lapor",
                    key: "created_at",
                    width: 100,
                    render(row: any) {
                        return formatDate(row.created_at, false);
                    }
                },
                {
                    title: "Status",
                    key: 'status',
                    width: 100,
                    render(row: any) {
                        return h(NTag, {
                            strong: true,
                            type: row.status === 'PENDING' ? 'warning' : 'success',
                            round: true
                        }, row.status);
                    }
                },
                {
                    title: "Balasan",
                    key: 'proceed_description',
                    width: 150
                }

            ];
        }

        return {
            visitRoute: (name: string, params?: any) => router.visit(route(name, params)),
            columns: createColumns(),
            router,
            Add12Filled
        }
    },
    components: {
        TitlePage,
        NDataTable,
        NButton,
        NCard,
        ItemCard,
        NIcon,
        Empty
    },
    layout: AuthenticatedLayout,
})
</script>