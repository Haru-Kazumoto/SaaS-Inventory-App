/** Baris data master (unit, gudang, kategori, satuan) */
export interface MasterRow {
    id: number;
    name: string;
    code: string;
    description?: string | null;
    unit_id?: number | null;
    unit_name?: string | null;
    members_count?: number | null;
    can_edit: boolean;
}
