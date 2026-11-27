import { NIcon } from "naive-ui";
import { Component, h } from "vue";
import {
    ArrowStepInRight16Regular,
    ArrowSwap24Regular,
    ArrowTrendingLines20Filled,
    Box20Regular,
    BoxArrowUp24Regular,
    BoxDismiss20Regular,
    BoxToolbox24Regular,
    BuildingMultiple20Regular,
    DataPie20Regular,
    DatabaseLink24Regular,
    DocumentText20Regular,
    MailInboxAll24Regular,
    Organization20Regular,
    Payment20Regular,
    People20Regular,
    PeopleSettings20Regular,
    Receipt20Regular,
    Ruler20Regular,
    TableStackLeft24Regular,
    Tag20Regular,
    Vault20Regular,
} from "@vicons/fluent";

function renderIcon(icon: Component) {
    return () => h(NIcon, null, { default: () => h(icon) });
}

export interface MenuTerms {
    unit: string;
    unit_plural: string;
}

/**
 * Menu sidebar per peran. Kode peran dari auth.user.role.code:
 * DEVELOPER = pengelola platform, SUPER = admin organisasi, ADMIN = anggota unit.
 * Label unit mengikuti terminologi organisasi (Jurusan / Divisi / ...).
 */
export function menuFor(code: string | undefined, terms: MenuTerms) {
    if (code === "DEVELOPER") return platformMenu;

    const isOrgAdmin = code === "SUPER";

    const menu: any[] = [
        { label: "Dashboard", key: "dashboard", icon: renderIcon(TableStackLeft24Regular), href: "/dashboard" },
        { label: "Barang", key: "products", icon: renderIcon(MailInboxAll24Regular), href: "/products" },
        { label: "Peminjaman", key: "borrowing", icon: renderIcon(ArrowSwap24Regular), href: "/borrowing" },
        {
            label: "Pergerakan Barang",
            key: "movements",
            icon: renderIcon(ArrowTrendingLines20Filled),
            children: [
                { label: "Sedang dipinjam", key: "movements-loaned", icon: renderIcon(ArrowSwap24Regular), href: "/movements/loaned" },
                { label: "Pemakaian habis pakai", key: "movements-consumed", icon: renderIcon(ArrowStepInRight16Regular), href: "/movements/consumed" },
                { label: "Barang masuk", key: "movements-restocks", icon: renderIcon(BoxArrowUp24Regular), href: "/movements/restocks" },
                { label: "Barang dihapuskan", key: "movements-write-offs", icon: renderIcon(BoxDismiss20Regular), href: "/movements/write-offs" },
            ],
        },
        { label: "Pengajuan Barang", key: "procurement", icon: renderIcon(BoxToolbox24Regular), href: "/procurement" },
        {
            label: "Data Master",
            key: "master-data",
            icon: renderIcon(DatabaseLink24Regular),
            children: [
                ...(isOrgAdmin
                    ? [{ label: terms.unit_plural, key: "master-units", icon: renderIcon(Organization20Regular), href: "/master/units" }]
                    : []),
                { label: "Penyimpanan", key: "master-warehouses", icon: renderIcon(Vault20Regular), href: "/master/warehouses" },
                { label: "Kategori", key: "master-categories", icon: renderIcon(Tag20Regular), href: "/master/categories" },
                { label: "Satuan", key: "master-uoms", icon: renderIcon(Ruler20Regular), href: "/master/uoms" },
            ],
        },
    ];

    if (isOrgAdmin) {
        menu.push({ label: "Anggota", key: "members", icon: renderIcon(PeopleSettings20Regular), href: "/members" });
    }

    return menu;
}

const platformMenu = [
    { label: "Dashboard", key: "platform-dashboard", icon: renderIcon(DataPie20Regular), href: "/platform" },
    { label: "Organisasi", key: "platform-organizations", icon: renderIcon(BuildingMultiple20Regular), href: "/platform/organizations" },
    { label: "Pengguna", key: "platform-users", icon: renderIcon(People20Regular), href: "/platform/users" },
    {
        label: "Penagihan",
        key: "platform-billing",
        icon: renderIcon(Receipt20Regular),
        children: [
            { label: "Tagihan", key: "platform-invoices", icon: renderIcon(DocumentText20Regular), href: "/platform/invoices" },
            { label: "Pembayaran", key: "platform-payments", icon: renderIcon(Payment20Regular), href: "/platform/payments" },
            { label: "Paket", key: "platform-plans", icon: renderIcon(Box20Regular), href: "/platform/plans" },
        ],
    },
];
