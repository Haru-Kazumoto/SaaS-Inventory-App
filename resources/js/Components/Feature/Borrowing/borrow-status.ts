export interface BorrowStatusMeta {
    label: string;
    badge: string;
    dot: string;
}

const STATUS_META: Record<string, BorrowStatusMeta> = {
    POSTED: {
        label: "Dipinjam",
        badge: "border-amber-200 bg-amber-50 text-amber-700",
        dot: "bg-amber-500",
    },
    PARTIAL_RETURNED: {
        label: "Kembali Sebagian",
        badge: "border-sky-200 bg-sky-50 text-sky-700",
        dot: "bg-sky-500",
    },
    COMPLETED: {
        label: "Selesai",
        badge: "border-emerald-200 bg-emerald-50 text-emerald-700",
        dot: "bg-emerald-500",
    },
};

const FALLBACK: BorrowStatusMeta = {
    label: "-",
    badge: "border-slate-200 bg-slate-50 text-slate-600",
    dot: "bg-slate-400",
};

export function borrowStatusMeta(status?: string | null): BorrowStatusMeta {
    const key = (status || "").toUpperCase();
    if (key === "RETURNED") return STATUS_META.COMPLETED;
    return STATUS_META[key] ?? FALLBACK;
}

export const BORROW_STATUS_FILTERS = [
    { label: "Semua", value: null },
    { label: "Dipinjam", value: "POSTED" },
    { label: "Kembali Sebagian", value: "PARTIAL_RETURNED" },
    { label: "Selesai", value: "COMPLETED" },
] as const;

export function getInitials(name?: string | null) {
    if (!name) return "?";
    return name
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((word) => word[0])
        .join("")
        .toUpperCase();
}

export function formatDateParts(dateStr?: string | null) {
    if (!dateStr) return { date: "-", time: "" };
    const d = new Date(dateStr);
    if (Number.isNaN(d.getTime())) return { date: dateStr, time: "" };
    return {
        date: d.toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "short",
            year: "numeric",
        }),
        time: d.toLocaleTimeString("id-ID", {
            hour: "2-digit",
            minute: "2-digit",
        }),
    };
}

export function formatPhone(phone?: string | null) {
    if (!phone) return "-";
    return phone
        .replace(/\D/g, "")
        .replace(/(.{4})/g, "$1 ")
        .trim();
}
