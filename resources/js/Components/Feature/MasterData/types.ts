/** Baris data master sederhana (gudang, sub kategori, satuan) */
export interface MasterRow {
    id: number | string;
    name: string;
    code: string;
    description?: string | null;
}
