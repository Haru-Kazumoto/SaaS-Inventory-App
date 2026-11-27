/** Baris barang keluar (tidak habis pakai & habis pakai) */
export interface OutgoingLine {
    borrower_name: string;
    product_name: string;
    product_code: string;
    warehouse_name?: string | null;
    outstanding_qty: number;
    uom_name?: string | null;
    category_name?: string | null;
    borrowed_at?: string;
    created_at?: string;
}
