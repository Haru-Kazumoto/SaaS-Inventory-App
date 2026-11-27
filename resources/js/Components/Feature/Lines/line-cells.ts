import { h } from "vue";
import { formatDateParts, getInitials } from "@/Components/Feature/Borrowing/borrow-status";

/** Helper render sel tabel pergerakan barang (dipakai 3 halaman Lines) */

export const muted = (text?: string | null) =>
    h("span", { class: "text-[11px] text-slate-400" }, text || "-");

export const rowNumberCell = (page: { current_page: number; per_page: number }, index: number) =>
    h(
        "span",
        { class: "text-xs tabular-nums text-slate-400" },
        (page.current_page - 1) * page.per_page + index + 1,
    );

export const personCell = (name?: string | null) =>
    h("div", { class: "flex items-center gap-2.5" }, [
        h(
            "div",
            {
                class: "flex h-8 w-8 shrink-0 items-center justify-center rounded border border-slate-200 bg-slate-50 text-[11px] font-semibold text-slate-600",
            },
            getInitials(name),
        ),
        h("span", { class: "truncate text-[13px] font-semibold text-slate-900" }, name || "-"),
    ]);

export const productCell = (name: string, sub?: string | null) =>
    h("div", { class: "flex min-w-0 flex-col" }, [
        h("span", { class: "truncate text-[13px] font-semibold text-slate-900" }, name),
        muted(sub),
    ]);

export const codeCell = (code?: string | null) =>
    h(
        "span",
        {
            class: "whitespace-nowrap rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 font-mono text-[11px] text-slate-600",
        },
        code || "-",
    );

export const textCell = (text?: string | null) =>
    h("span", { class: "text-xs text-slate-700" }, text || "-");

export const qtyCell = (
    qty: number | string,
    unit?: string | null,
    tone: "amber" | "emerald" | "slate" = "slate",
    prefix = "",
) => {
    const tones = {
        amber: "border-amber-200 bg-amber-50 text-amber-800",
        emerald: "border-emerald-200 bg-emerald-50 text-emerald-800",
        slate: "border-slate-200 bg-slate-50 text-slate-800",
    };
    return h(
        "span",
        {
            class: `inline-flex items-baseline gap-1 whitespace-nowrap rounded border px-2 py-0.5 ${tones[tone]}`,
        },
        [
            h("span", { class: "text-sm font-bold tabular-nums" }, `${prefix}${qty}`),
            h("span", { class: "text-[11px] opacity-70" }, unit ?? ""),
        ],
    );
};

export const dateCell = (value?: string | null) => {
    const p = formatDateParts(value);
    return h("div", { class: "flex flex-col tabular-nums" }, [
        h("span", { class: "text-xs text-slate-700" }, p.date),
        muted(p.time),
    ]);
};
