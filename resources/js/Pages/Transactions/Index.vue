<template>
    <div class="flex flex-col gap-3">
        <TitlePage title="List transaksi" subTitle="List keluar masuknya barang" />

        <n-card hoverable :bordered="false" class="border border-gray-300 rounded-2xl min-h-[34rem]">
            <!-- content card -->
            <div class="flex flex-col gap-3">
                <!-- header content card -->
                <div class="flex">
                    <span class="text-xl font-bold">List Transaksi</span>
                    <div class="ms-auto flex gap-2">
                        <n-button tertiary @click="active = true">
                            <template #icon>
                                <n-icon :component="Filter16Filled" />
                            </template>
                            Filter
                        </n-button>
                        <n-button type="info" @click="handleExport">
                            <template #icon>
                                <n-icon :component="ArrowDownload16Filled"></n-icon>
                            </template>
                            Download Report
                        </n-button>

                        <n-button type="primary" @click="visitRoute('transaction.create')"
                            v-if="user_role !== 'SUPER'">
                            <template #icon>
                                <n-icon :component="Add24Filled"></n-icon>
                            </template>
                            Buat Transaksi
                        </n-button>
                    </div>
                </div>

                <n-data-table :bordered="false" :columns :data="(transactions as any).data">
                    <template #empty>
                        <Empty />
                    </template>
                </n-data-table>

                <div class="flex justify-end">
                    <n-pagination v-model:page="currentPage" :page-count="transactions.last_page" :page-size="transactions.per_page"
                        @update:page="handlePageChange" @update:page-count="transactions.last_page = transactions.last_page" />
                </div>

                <n-drawer v-model:show="active" :width="502" placement="right">
                    <n-drawer-content title="Filter data transaksi">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="col-span-1">
                                <label for="">Nama Peminjam</label>
                                <n-input placeholder="" size="large"
                                    v-model:value="filterSearch.search_borrower_name"></n-input>
                            </div>
                            <div class="col-span-1">
                                <label for="">Ruangan Peminjam</label>
                                <n-input placeholder="" size="large"
                                    v-model:value="filterSearch.search_room_name"></n-input>
                            </div>
                            <div class="col-span-1">
                                <label for="">Nomor Peminjam</label>
                                <n-input placeholder="" size="large"
                                    v-model:value="filterSearch.search_phone_borrower"></n-input>
                            </div>
                            <div class="col-span-1">
                                <label for="">Pengawas Ruangan</label>
                                <n-input placeholder="" size="large"
                                    v-model:value="filterSearch.search_teacher_name"></n-input>
                            </div>
                            <div class="col-span-2 flex gap-2 ms-auto">
                                <n-button color="black" ghost @click="removeFilter">Hapus Filter</n-button>
                                <n-button color="black" @click="applyFilter">Terapkan Filter</n-button>
                            </div>
                        </div>
                    </n-drawer-content>
                </n-drawer>
            </div>
        </n-card>
    </div>
</template>

<script lang="ts">
import TitlePage from '@/Components/TitlePage.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Empty from '@/Components/Empty.vue';
import { Filter16Filled, ArrowDownload16Filled, Add24Filled, Search12Filled, BoxArrowUp24Regular, ReOrderDotsVertical24Filled } from '@vicons/fluent';
import { NButton, NCard, NDataTable, NDrawer, NDrawerContent, NIcon, NInput, NTag, useNotification, NDropdown, NTooltip, NPagination } from 'naive-ui';
import { defineComponent, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { h } from 'vue';
import Swal from 'sweetalert2';
import { PropType } from 'vue';
import { PaginationResponse } from '@/types/global-object';

export default defineComponent({
    props: {
        transactions: { type: Object as PropType<PaginationResponse>, required: true }
    },
    setup(props) {
        const page = usePage();
        const user_role = page.props.auth.user.role.code;
        const active = ref<boolean>(false);
        const notification = useNotification();
        const currentPage = ref(props.transactions.current_page);

        const filterSearch = ref({
            search_borrower_name: "",
            search_room_name: "",
            search_phone_borrower: "",
            search_teacher_name: ""
        });

        function applyFilter() {
            router.get(route('transaction.index'), filterSearch.value, {
                preserveState: true,
                onSuccess: () => {
                    active.value = false;
                    notification.success({
                        title: "Difilter!",
                        duration: 2000,
                        closable: false
                    });
                },
                onError: () => {
                    notification.error({
                        title: "Server sedang sibuk :(",
                        duration: 2000,
                        closable: false
                    });
                }
            });
        }

        function removeFilter() {
            router.get(route('transaction.index'));
        }

        function handleExport() {
            const url = route('transaction.export');

            window.location.href = url;
        }

        function handlePageChange(page: number) {
            router.get(route('tranasction.index'), {
                ...route().queryParams, page
            }, {
                preserveState: true,
                replace: true
            }); // Request data for the selected page
        }

        function createColumns(userRole: string) {
            const columns = [
                {
                    key: "actions",
                    width: 70,
                    render(row: any, index: number) {
                        return h(NDropdown, {
                            options: [
                                { label: "Detail", key: "detail" },
                                { label: "Edit", key: "edit" },
                                { label: "Hapus", key: "delete" }
                            ],
                            trigger: 'click',
                            onSelect: (key: string) => handleClickDropdown(key, row)
                        },
                            () =>
                                h(
                                    NTooltip,
                                    { placement: 'top' },
                                    {
                                        trigger: () =>
                                            h(
                                                NButton,
                                                {
                                                    type: 'info',
                                                    quaternary: true,
                                                },
                                                {
                                                    icon: () =>
                                                        h(NIcon, null, {
                                                            default: () => h(ReOrderDotsVertical24Filled),
                                                        }),
                                                }
                                            ),
                                        default: () => 'Aksi',
                                    }
                                ));
                    }
                },
                {
                    title: "#",
                    key: "id",
                    width: 50,
                    render(row: any, index: number) {
                        return (props.transactions.current_page - 1) * props.transactions.per_page + index + 1;
                    }
                },
                ...(userRole !== "SUPER"
                    ? [
                        {
                            title: () => {
                                return h(
                                    NTooltip,
                                    { placement: 'top' },
                                    {
                                        trigger: () => h('span', 'Proses'),
                                        default: () => 'Memproses transaksi seperti memasukan data kembali dan mengecek perlengkapan'
                                    }
                                )
                            },
                            key: "process",
                            width: 100,
                            render(row: any) {
                                return h(
                                    NButton,
                                    {
                                        type: 'info',
                                        strong: true,
                                        disabled: row.in_date ? true : false,
                                        onClick: () => router.visit(route('transaction.restore', row.transaction_id))
                                    },
                                    { default: () => h(NIcon, { size: 18 }, { default: () => h(BoxArrowUp24Regular) }) }
                                );
                            }
                        }
                    ]
                    : []
                ),
                {
                    title: "Status",
                    key: "in_date",
                    width: 150,
                    render(row: any) {
                        return h(
                            NTag,
                            {
                                type: row.in_date ? 'success' : 'warning',
                                strong: true,
                                round: true
                            },
                            { default: () => (row.in_date ? "SELESAI" : "DIPINJAM") }
                        );
                    }
                },
                {
                    title: "Nomor Transaksi",
                    key: "transaction_number",
                    width: 150,
                },
                {
                    title: "Nama Peminjam",
                    key: "borrower_name",
                    width: 200,
                },
                {
                    title: "Ruangan peminjam",
                    key: "room_borrower",
                    width: 150,
                },
                {
                    title: "Nomor Peminjam",
                    key: "phone_borrower",
                    width: 150,
                },
                {
                    title: "Jumlah Barang Dipinjam",
                    key: "total_items_out",
                    width: 200,
                    render(row: any) {
                        return `${row.total_items_out} BARANG`
                    }
                },

            ];

            return columns;
        }

        function handleClickDropdown(key: string, row: any) {
            switch (key) {
                case "detail":
                    router.visit(route('transaction.show', row.transaction_id));
                    break;
                case "edit":
                    router.visit(route('transaction.edit', row.transaction_id));
                    break;
                case "delete":
                    Swal.fire({
                        title: "Hapus transaksi ini?",
                        text: "Transaksi ini tidak dapat dipulihkan akan dihapus secara permanen dan akan memengaruhi chart dashboard",
                        icon: "warning",
                        showCancelButton: true,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            router.delete(route('transaction.destroy', row.id), {
                                onSuccess: (page) => {
                                    notification.success({
                                        title: page.props.flash.success,
                                        duration: 2000,
                                        closable: false
                                    });
                                }
                            });
                        }
                    });
                    break;
                default:
                    break;
            }
        }

        return {
            visitRoute: (name: string, params?: any) => router.visit(route(name, params)),
            Filter16Filled,
            ArrowDownload16Filled,
            Add24Filled,
            columns: createColumns(user_role),
            user_role,
            active,
            router,
            filterSearch,
            applyFilter,
            removeFilter,
            handleExport,
            ReOrderDotsVertical24Filled,
            handlePageChange,
            currentPage,
        }
    },
    layout: AuthenticatedLayout,
    components: {
        TitlePage,
        NDataTable,
        NButton,
        NInput,
        NDrawer,
        NDrawerContent,
        NIcon,
        NCard,
        NTag,
        Empty,
        NPagination
    }
})
</script>