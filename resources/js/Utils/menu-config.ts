import { Link } from "@inertiajs/vue3";
import { ArrowSwap24Regular, BoxDismiss20Regular, BoxMultiple24Regular, BoxToolbox24Regular, Bug24Regular, DatabaseLink24Regular, Home24Regular, ArrowNext20Regular, MailInboxAll24Regular, PeopleSettings20Regular, ReceiptAdd20Regular, Settings24Regular, TableStackLeft24Regular, TrayItemRemove24Filled, DocumentToolbox24Regular, MailInboxAdd20Regular, ArrowSort20Regular, ArrowTrendingLines20Filled, ArrowStepInRight16Regular, BoxArrowUp24Regular } from "@vicons/fluent";
import { NIcon } from "naive-ui";
import { Component, h } from "vue";

function renderLabel(route: string, labelName: string) {
    return h(Link, { href: route }, { default: () => h('span', null, labelName) });
}

function renderIcon(icon: Component) {
    return () => h(NIcon, null, { default: () => h(icon) })
}

export const menuOptions = {
    SUPER: [
        {
            label: "Dashboard",
            key: "dashboard",
            icon: renderIcon(TableStackLeft24Regular),
            href: '/dashboard/superadmin'
        },
        // {
        //     label: "Daftar Barang Dipinjam",
        //     key: "list-items",
        //     icon: renderIcon(BoxDismiss20Regular),
        //     href: "/transactions/items"
        // },
        // {
        //     label: "Log Peminjaman",
        //     key: "transactions",
        //     icon: renderIcon(ReceiptAdd20Regular),
        //     href: '/transactions',
        // },
        {
            label: "Daftar Barang Gudang",
            key: "item-data",
            icon: renderIcon(BoxMultiple24Regular),
            children: [
                {
                    label: "Gudang TJKT",
                    icon: renderIcon(ArrowNext20Regular),
                    key: "tjkt-items",
                    href: '/products/tjkt',
                },
                {
                    label: "Gudang TO",
                    icon: renderIcon(ArrowNext20Regular),
                    key: "to-items",
                    href: '/products/to',
                },
                {
                    label: "Gudang TE",
                    icon: renderIcon(ArrowNext20Regular),
                    key: "te-items",
                    href: '/products/te',
                },
                {
                    label: "Gudang AK",
                    icon: renderIcon(ArrowNext20Regular),
                    key: "ak-items",
                    href: '/products/ak',
                },
                {
                    label: "Gudang TU",
                    icon: renderIcon(ArrowNext20Regular),
                    key: "tu-items",
                    href: '/products/tu',
                },
                {
                    label: "Gudang KKPI",
                    icon: renderIcon(ArrowNext20Regular),
                    key: "kkpi-warehouseitems",
                    href: '/products/kkpi',
                }
            ]
        },
        {
            label: "Pergerakan Barang",
            key: 'products-line',
            icon: renderIcon(ArrowTrendingLines20Filled),
            children: [
                {
                    label: "Tidak Habis Pakai",
                    key: "durable",
                    icon: renderIcon(ArrowSwap24Regular),
                    href: '/lines/durable'
                },
                {
                    label: "Habis Pakai",
                    key: 'consumable',
                    icon: renderIcon(ArrowStepInRight16Regular),
                    href: "/lines/consumable"
                },
                {
                    label: "Barang masuk",
                    key: 'restock-items',
                    icon: renderIcon(BoxArrowUp24Regular),
                    href: '/lines/restocks'
                }
            ]
        },
        {
            label: "Permintaan Barang",
            key: "item-request",
            icon: renderIcon(BoxToolbox24Regular),
            href: "/request-item"
        },
        {
            label: "Manajemen Data Utama",
            key: "master-data",
            icon: renderIcon(Settings24Regular),
            children: [
                {
                    label: "Manajemen Pengguna",
                    key: "user-management",
                    icon: renderIcon(PeopleSettings20Regular),
                    href: "/user",
                },
            ]
        },
        // {
        //     label: "Lapor Issue",
        //     key: "report-bugs",
        //     icon: renderIcon(Bug24Regular),
        //     href: "/reports"
        // }
    ],
    ADMIN: [
        {
            label: "Dashboard",
            key: "dashboard",
            icon: renderIcon(TableStackLeft24Regular),
            href: '/dashboard'
        },
        {
            label: "Manajemen Barang",
            key: 'product-management',
            icon: renderIcon(MailInboxAll24Regular),
            href: '/products'
        },
        {
            label: "Barang Keluar Masuk",
            key: 'transactions',
            icon: renderIcon(ArrowSort20Regular),
            href: '/borrowing'
        },
        {
            label: "Pergerakan Barang",
            key: 'products-line',
            icon: renderIcon(ArrowTrendingLines20Filled),
            children: [
                {
                    label: "Tidak Habis Pakai",
                    key: "durable",
                    icon: renderIcon(ArrowSwap24Regular),
                    href: '/lines/durable'
                },
                {
                    label: "Habis Pakai",
                    key: 'consumable',
                    icon: renderIcon(ArrowStepInRight16Regular),
                    href: "/lines/consumable"
                },
                {
                    label: "Barang masuk",
                    key: 'restock-items',
                    icon: renderIcon(BoxArrowUp24Regular),
                    href: '/lines/restocks'
                }
            ]
        },
        // {
        //     label: "Manajemen Barang",
        //     key: 'item-management',
        //     icon: renderIcon(MailInboxAll24Regular),
        //     href: '/item/index'
        // },
        // {
        //     label: "Manajemen Peminjaman",
        //     key: 'item-transaction',
        //     icon: renderIcon(ArrowSwap24Regular),
        //     // href: "/transactions",
        //     children: [
        //         {
        //             label: "Daftar Peminjaman",
        //             key: "transactions",
        //             icon: renderIcon(ReceiptAdd20Regular),
        //             href: '/transactions',
        //         },
        //         {
        //             label: "Daftar Barang Dipinjam",
        //             key: "list-items",
        //             icon: renderIcon(BoxDismiss20Regular),
        //             href: "/transactions/items"
        //         }
        //     ]
        // },
        {
            label: "Pengajuan Barang",
            key: "item-request",
            icon: renderIcon(BoxToolbox24Regular),
            href: "/request-item"
        },
        {
            label: "Barang Dihapuskan",
            key: "item-off",
            icon: renderIcon(BoxDismiss20Regular),
            href: "/products/off"
        },
        {
            label: "Manajemen Data",
            key: 'master-data',
            icon: renderIcon(DatabaseLink24Regular),
            children: [
                {
                    label: "Penyimpanan",
                    key: 'master-data',
                    icon: renderIcon(Home24Regular),
                    href: '/warehouse',
                },
                {
                    label: "Sub Kategori Barang",
                    key: 'subcategory-data',
                    icon: renderIcon(DocumentToolbox24Regular),
                    href: '/sub-type',
                },
                {
                    label: "Satuan Barang",
                    key: 'unit-data',
                    icon: renderIcon(MailInboxAdd20Regular),
                    href: '/unit-product',
                }
            ]
        },
        // {
        //     label: "Lapor Issue",
        //     key: "report-bugs",
        //     icon: renderIcon(Bug24Regular),
        //     href: "/reports"
        // },
    ],
    DEVELOPER: [
        {
            label: "Dashboard",
            key: "dashboard",
            icon: renderIcon(TableStackLeft24Regular),
            href: '/dashboard/internal'
        },

    ]
}