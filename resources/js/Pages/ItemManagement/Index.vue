<template>
    <div class="flex flex-col gap-3" data-scroll-region>
        <TitlePage :title="`Manajemen Barang ${$page.props.auth.user.major.name}`"
            subTitle="Atur barang gudang mu disini" />

        <!-- Content -->
        <n-card hoverable :bordered="false" class="border border-gray-300 rounded-2xl">
            <div class="flex flex-col gap-3">
                <!-- title card -->
                <span class="text-xl font-bold">
                    Semua Barang
                    <span class="text-xl font-bold text-gray-400">({{ item_count }})</span>
                </span>
                <div class="flex items-center gap-3">
                    <n-input placeholder="Cari barang..." @keyup.enter="handleApplyFilter" @clear="handleResetFilter"
                        v-model:value="filterData.search_name" clearable>
                        <template #prefix>
                            <n-icon :component="Search12Filled" />
                        </template>
                    </n-input>
                    <n-select clearable @clear="handleResetFilter" size="medium" placeholder="TIPE BARANG"
                        :options="itemTypeOptions" v-model:value="filterData.search_category"
                        class="w-[20rem]"></n-select>
                    <n-button type="info" :strong="true" @click="handleExport">
                        <template #icon>
                            <n-icon :component="DocumentBulletList24Filled"></n-icon>
                        </template>
                        Export Barang
                    </n-button>
                    <n-button type="primary" :strong="true" @click="handleCreateItem">
                        <template #icon>
                            <n-icon :component="Add12Filled" />
                        </template>
                        Tambah Barang
                    </n-button>
                </div>

                <!-- Table -->
                <n-data-table :columns :data="items.data" :bordered="false" striped>
                    <template #empty>
                        <Empty />
                    </template>
                </n-data-table>

                <div class="flex justify-end">
                    <n-pagination v-model:page="currentPage" :page-count="items.last_page" :page-size="items.per_page"
                        @update:page="handlePageChange" @update:page-count="items.last_page = items.last_page" />
                </div>
            </div>

            <n-drawer v-model:show="active" :width="502" placement="right">
                <n-drawer-content title="Sorting">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2">
                            <label class="text-sm font-bold">Kategori Barang</label>

                        </div>
                        <div class="col-span-1 col-start-2 flex ms-auto gap-2">
                            <n-button ghost colot="black" @click="handleResetFilter">HAPUS FILTER</n-button>
                            <n-button color="black" @click="handleApplyFilter">FILTER</n-button>
                        </div>
                    </div>
                </n-drawer-content>
            </n-drawer>
        </n-card>
    </div>
</template>

<script lang="ts">
import TitlePage from '@/Components/TitlePage.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { NButton, NCard, NDataTable, NDrawer, NDrawerContent, NIcon, NInput, NModal, NSelect, NTag, NPagination, useNotification, NDropdown, NTooltip } from 'naive-ui';
import { defineComponent, h, PropType, ref, watch } from 'vue';
import { Add12Filled, Filter16Filled, Search12Filled, DocumentBulletList24Filled, ReOrderDotsVertical24Filled } from '@vicons/fluent';
import { router } from '@inertiajs/vue3';
import { FilterItemRequest, PaginationResponse } from '@/types/global-object';
import Swal from 'sweetalert2';
import Empty from '@/Components/Empty.vue';

interface UtilsResponse {
    warehouses: any[];
    item_types: any[];
}

export default defineComponent({
    props: {
        item_count: { type: Number },
        items: { type: Object as PropType<PaginationResponse>, required: true },
        filters: { type: Object as PropType<FilterItemRequest>, required: true },
        utils: { type: Object as PropType<UtilsResponse>, required: true }
    },
    setup(props) {
        function createColumns() {
            return [
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
                            onSelect: (key: string) => handleClickAction(key, row)
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
                    key: "index",
                    width: 50,
                    render(row: any, index: number) {
                        return (props.items.current_page - 1) * props.items.per_page + index + 1;
                    }
                },
                {
                    title: "Nama Barang",
                    key: "name",
                    width: 200
                },
                {
                    title: "Merk",
                    key: "brand_name",
                    width: 150
                },
                {
                    title: "Kategori",
                    key: "category",
                    width: 200,
                    render(row: any) {
                        const ctg = row.category;
                        let replace_ctg;
                        let type: any;

                        if (ctg === "DURABLE") {
                            replace_ctg = "TIDAK HABIS PAKAI";
                            type = 'info';
                        } else {
                            replace_ctg = "HABIS PAKAI";
                            type = 'warning';
                        }
                        return h(NTag, {
                            type,
                            strong: true,
                            size: "small",
                            bordered: true,
                            round: true
                        }, replace_ctg);
                    }
                },
                {
                    title: "Stok",
                    key: "quantity",
                    width: 120,
                    render(row: any) {
                        const quantity = row.base_quantity ?? row.quantity
                        return `${quantity} ${row.unit}`
                    },
                    sorter: (rowA: any, rowB: any) => {
                        const qtyA = rowA.base_quantity ?? rowA.quantity
                        const qtyB = rowB.base_quantity ?? rowB.quantity
                        return qtyA - qtyB
                    }
                }
            ]
        }
        const active = ref(false);
        const notification = useNotification();
        const filterData = ref({
            search_name: props.filters?.search_name,
            search_category: props.filters?.search_category,
            search_warehouse: props.filters?.search_warehouse,
            page: props.items?.current_page
        });

        const warehouseOptions = props.utils?.warehouses?.map((data) => ({
            label: data.name,
            value: data.name
        }));

        const itemTypeOptions = props.utils?.item_types.map((data) => ({
            label: data.name,
            value: data.value
        }));

        function handlePageChange(page: number) {
            router.get(route('item-management.index'), {
                ...route().queryParams, page
            }, {
                preserveState: true,
                replace: true
            }); // Request data for the selected page
        }

        function handleCreateItem() {
            router.visit(route("item-management.create"));
        }

        function handleExport() {
            const url = route('item-management.export');
            window.open(url, '_blank');
        }

        function applyFilter() {
            router.get(route('item-management.index'), filterData.value, {
                preserveState: true,
                replace: true,
                onSuccess: () => {
                    active.value = false;
                    notification.success({ title: "Berhasil difilter!", closable: false, duration: 3000 });
                },
                onError: () => {
                    notification.error({ title: "Server sibuk :(", closable: false, duration: 3000 });
                }
            })
        }

        function resetFilter() {
            filterData.value = {
                search_name: null as unknown as string,
                search_category: null as unknown as string,
                search_warehouse: null as unknown as string,
                page: 1,
            };
            router.get(route("item-management.index"), {}, {
                replace: true,
                onSuccess() { active.value = false },
                onError() { notification.error({ title: "Server sibuk :(", duration: 3000, closable: false }); }
            });
        };

        function handleSelectCategory() {
            router.get(route('item-management.index'), {
                search_category: filterData.value.search_category
            }, {
                preserveState: true,
                replace: true,
            });
        }

        function handleClickAction(key: string, row: any) {
            switch (key) {
                case "detail":
                    router.get(route('item-management.show', row.id));
                    break;
                case "edit":
                    router.get(route('item-management.edit', row.id));
                    break;
                case "delete":
                    Swal.fire({
                        title: `Hapus ${row.name}?`,
                        text: "Menghapus barang akan menghapus parsial juga, dan tidak akan bisa dikembalikan lagi",
                        icon: 'warning',
                        showCancelButton: true,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            router.delete(route('item-management.destroy', row.id), {
                                onSuccess: (page) => {
                                    Swal.close();
                                    notification.success({
                                        title: page.props.flash.success,
                                        meta: "Barang berhasil dihapus",
                                        duration: 2500,
                                        closable: false
                                    });
                                },
                                onError: () => {
                                    Swal.close();
                                    notification.error({
                                        title: "Server sibuk",
                                        meta: "Sepertinya server sedang sibuk :(",
                                        duration: 2500,
                                        closable: false
                                    });
                                }
                            })
                        }
                    });
                    break;
                default:
                    break;
            }
        }

        function handleApplyFilter() {
            applyFilter();
        }

        function handleResetFilter() {
            resetFilter();
        }

        watch(() => filterData.value.search_category, (data: string) => {
            handleSelectCategory();
        }, {
            immediate: true
        });

        // page lokal mengikuti props saat pertama kali load
        const currentPage = ref(props.items.current_page)

        watch(
            () => props.items.current_page,
            (val) => {
                currentPage.value = val
            }
        )

        return {
            router,
            columns: createColumns(),
            handleCreateItem,
            Add12Filled,
            Filter16Filled,
            active,
            warehouseOptions,
            DocumentBulletList24Filled,
            itemTypeOptions,
            handleApplyFilter,
            handleResetFilter,
            filterData,
            handleExport,
            handlePageChange,
            handleSelectCategory,
            Search12Filled,
            currentPage
        }
    },
    layout: AuthenticatedLayout,
    components: {
        TitlePage,
        NDataTable,
        NButton,
        NIcon,
        NInput,
        NCard,
        NTag,
        NDrawer,
        NDrawerContent,
        NSelect,
        NModal,
        NPagination,
        Empty
    }
})
</script>