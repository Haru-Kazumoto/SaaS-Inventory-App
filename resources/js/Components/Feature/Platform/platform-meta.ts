/** Label & warna untuk area Platform (status organisasi, tagihan, pembayaran, dll.) */

export interface BadgeMeta {
    label: string;
    badge: string;
    dot: string;
}

const tone = {
    brand: { badge: "border-[#2A126C]/20 bg-[#2A126C]/[0.06] text-[#2A126C]", dot: "bg-[#2A126C]" },
    green: { badge: "border-emerald-200 bg-emerald-50 text-emerald-700", dot: "bg-emerald-500" },
    amber: { badge: "border-amber-200 bg-amber-50 text-amber-700", dot: "bg-amber-500" },
    rose: { badge: "border-rose-200 bg-rose-50 text-rose-700", dot: "bg-rose-500" },
    sky: { badge: "border-sky-200 bg-sky-50 text-sky-700", dot: "bg-sky-500" },
    slate: { badge: "border-slate-200 bg-slate-50 text-slate-600", dot: "bg-slate-400" },
};

const make = (label: string, t: keyof typeof tone): BadgeMeta => ({ label, ...tone[t] });

export const ORGANIZATION_STATUS: Record<string, BadgeMeta> = {
    trial: make("Masa coba", "sky"),
    active: make("Aktif", "green"),
    past_due: make("Menunggak", "amber"),
    suspended: make("Ditangguhkan", "rose"),
    cancelled: make("Berhenti", "slate"),
};

export const SUBSCRIPTION_STATUS: Record<string, BadgeMeta> = {
    trialing: make("Masa coba", "sky"),
    active: make("Aktif", "green"),
    past_due: make("Menunggak", "amber"),
    cancelled: make("Dibatalkan", "slate"),
    expired: make("Berakhir", "slate"),
};

/** Pakai `display_status` (unpaid yang lewat jatuh tempo = overdue). */
export const INVOICE_STATUS: Record<string, BadgeMeta> = {
    unpaid: make("Belum dibayar", "amber"),
    overdue: make("Terlambat", "rose"),
    paid: make("Lunas", "green"),
    void: make("Dibatalkan", "slate"),
};

export const PAYMENT_STATUS: Record<string, BadgeMeta> = {
    pending: make("Menunggu", "amber"),
    success: make("Berhasil", "green"),
    failed: make("Gagal", "rose"),
    refunded: make("Dikembalikan", "slate"),
};

export const PAYMENT_METHODS = [
    { label: "Transfer bank", value: "bank_transfer" },
    { label: "Virtual account", value: "virtual_account" },
    { label: "QRIS", value: "qris" },
    { label: "Tunai", value: "cash" },
    { label: "Lainnya", value: "other" },
];

export const TEMPLATES = [
    { label: "Sekolah", value: "school" },
    { label: "Kampus", value: "campus" },
    { label: "Kantor", value: "office" },
    { label: "Komunitas / Rental", value: "community" },
];

export const BILLING_CYCLES = [
    { label: "Bulanan", value: "monthly" },
    { label: "Tahunan", value: "yearly" },
];

export const labelOf = (list: { label: string; value: string }[], v?: string | null) =>
    list.find((o) => o.value === v)?.label ?? v ?? "-";

export function metaOf(map: Record<string, BadgeMeta>, v?: string | null): BadgeMeta {
    return map[v ?? ""] ?? make(v ?? "-", "slate");
}

const idr = new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", maximumFractionDigits: 0 });
export const formatIDR = (n?: number | null) => idr.format(Number(n ?? 0));

/** Rp 1,2 jt / Rp 350 rb, untuk kartu angka. */
export function formatIDRShort(n?: number | null) {
    const v = Number(n ?? 0);
    if (Math.abs(v) >= 1_000_000_000) return `Rp ${(v / 1_000_000_000).toLocaleString("id-ID", { maximumFractionDigits: 1 })} M`;
    if (Math.abs(v) >= 1_000_000) return `Rp ${(v / 1_000_000).toLocaleString("id-ID", { maximumFractionDigits: 1 })} jt`;
    if (Math.abs(v) >= 1_000) return `Rp ${(v / 1_000).toLocaleString("id-ID", { maximumFractionDigits: 0 })} rb`;
    return idr.format(v);
}

export function formatDay(value?: string | null) {
    if (!value) return "-";
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return value;
    return d.toLocaleDateString("id-ID", { day: "2-digit", month: "short", year: "numeric" });
}

export function formatDateTime(value?: string | null) {
    if (!value) return "-";
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return value;
    return d.toLocaleString("id-ID", { day: "2-digit", month: "short", year: "numeric", hour: "2-digit", minute: "2-digit" });
}

export function initials(name?: string | null) {
    return (name || "?").trim().split(/\s+/).slice(0, 2).map((w) => w[0]).join("").toUpperCase();
}
