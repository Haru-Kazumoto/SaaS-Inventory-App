import type { RowData } from "@tanstack/vue-table";

/**
 * Metadata tambahan per kolom yang dibaca oleh <DataTable>.
 * Pakai lewat `meta` di definisi kolom TanStack:
 *   { accessorKey: "name", header: "Nama", meta: { align: "right", width: "120px" } }
 */
declare module "@tanstack/vue-table" {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    interface ColumnMeta<TData extends RowData, TValue> {
        /** Perataan isi & header kolom */
        align?: "left" | "center" | "right";
        /** Lebar kolom, mis. "120px" */
        width?: string;
        /** Lebar minimum kolom, mis. "200px" */
        minWidth?: string;
        /** Class tambahan untuk <td> */
        class?: string;
        /** Class tambahan untuk <th> */
        headerClass?: string;
        /** Kunci kolom di kanan saat tabel di-scroll horizontal (cocok untuk kolom aksi) */
        sticky?: boolean;
    }
}

/** Bentuk pagination dari Laravel paginator yang dibutuhkan DataTable */
export interface DataTablePagination {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from?: number | null;
    to?: number | null;
}
