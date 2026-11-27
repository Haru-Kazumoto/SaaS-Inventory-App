<template>
    <div class="flex flex-col gap-3">
        <TitlePage title="Barang Keluar"
            subTitle="List barang yang sedang keluar gudang" />

        <n-card hoverable :bordered="false" class="border border-gray-300 rounded-2xl min-h-[34rem]">
            <!-- content card -->
            <div class="flex flex-col gap-3">
                <!-- header content card -->
                <span class="text-xl font-bold">List Transaksi</span>
                <div class="flex gap-2">
                    <n-input clearable placeholder="Cari nama barang" @keyup.enter="handleFilterItemName" v-model:value="item_name">
                        <template #prefix>
                            <n-icon :component="Search12Filled" size="17"></n-icon>
                        </template>
                    </n-input>
                    <n-button type="info">
                        <template #icon>
                            <n-icon :component="ArrowDownload16Filled"></n-icon>
                        </template>
                        Download Report
                    </n-button>
                </div>

                <n-data-table :columns :bordered="false" :data="items_out.data">
                    <template #empty>
                        <Empty />
                    </template>
                </n-data-table>

                <div class="flex justify-end">
                    <n-pagination v-model:page="currentPage" :page-count="items_out.last_page"
                        :page-size="items_out.per_page" @update:page="handlePageChange"
                        @update:page-count="items_out.last_page = items_out.last_page" />
                </div>
            </div>
        </n-card>
    </div>
</template>

<script lang="ts">
import TitlePage from '@/Components/TitlePage.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Filter16Filled, ArrowDownload16Filled, Add24Filled, Search12Filled } from '@vicons/fluent';
import { NButton, NCard, NDataTable, NDrawer, NIcon, NInput, NPagination, NTag, useNotification } from 'naive-ui';
import { defineComponent, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Empty from '@/Components/Empty.vue';
import { formatDate } from '@/Utils/helper-function';
import { PropType } from 'vue';
import { PaginationResponse } from '@/types/global-object';

export default defineComponent({
    props: {
        items_out: { type: Object as PropType<PaginationResponse>, required: true }
    },
    setup(props) {
        const showFilterDrawer = ref<Boolean>(false);
        const notification = useNotification();
        const currentPage = ref(props.items_out.current_page);
        const item_name = ref('');

        function createColumns() {
            return [
                {
                    title: "#",
                    key: "id",
                    width: 50,
                    render(row: any, index: number) {
                        return index + 1;
                    }
                },
                {
                    title: "Nomor Transaksi",
                    key: 'transaction_number',
                    width: 100,
                },
                {
                    title: "Nama Barang",
                    key: "item_name",
                    width: 200,
                },
                {
                    title: "Kode Barang",
                    key: "code_item",
                    width: 150,
                },
                {
                    title: "Waktu Dipinjam",
                    key: "out_date",
                    width: 150,
                    render(row: any) {
                        return formatDate(row.out_date, true);
                    }
                },
                {
                    title: "Ruangan Dipinjam",
                    key: "room_name",
                    width: 200,
                },
            ]
        }

        function handlePageChange(page: number) {
            router.get(route('transaction.items'), {
                ...route().queryParams, page
            }, {
                preserveState: true,
                replace: true
            }); // Request data for the selected page
        }

        function handleFilterItemName() {
            router.get(route('transaction.items'), {item_name: item_name.value}, {
                preserveState: true,
                onSuccess: () => {
                    notification.success({
                        title: "Difilter!",
                        duration: 2000,
                        closable: false
                    });
                },
                onError: () => {
                    notification.error({
                        title: "Server sedang sibuk :(",
                        closable: true
                    });
                }
            })
        }

        return {
            Filter16Filled,
            ArrowDownload16Filled,
            Add24Filled,
            columns: createColumns(),
            showFilterDrawer,
            router,
            Search12Filled,
            handlePageChange,
            currentPage,
            item_name,
            handleFilterItemName
        }
    },
    layout: AuthenticatedLayout,
    components: {
        TitlePage,
        NDataTable,
        NButton,
        NInput,
        NDrawer,
        NIcon,
        NCard,
        NTag,
        Empty,
        NPagination
    }
})
</script>