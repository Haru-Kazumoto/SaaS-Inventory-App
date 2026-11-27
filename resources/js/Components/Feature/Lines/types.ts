/** Baris barang keluar (tidak habis pakai & habis pakai) */
export interface OutgoingLine {
    borrower_name: string;
    product_name: string;
    product_code: string;
    warehouse_name?: string | null;
    outstanding_qty: number;
    unit_name: string;
    sub_type_name: string;
    borrowed_at?: string;
    created_at?: string;
}
