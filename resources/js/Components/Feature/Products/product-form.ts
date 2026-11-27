/** Data yang dikirim ProductForm saat submit (create & edit) */
export interface ProductFormPayload {
    unit_id: number | null;
    name: string | null;
    code: string | null;
    brand_name: string;
    type: string;
    price: number | null;
    labeling_date: string | null;
    category_id: number | null;
    uom_id: number | null;
    image: File | null;
    /** target stok per gudang & kondisi (stok awal saat buat / penyesuaian saat ubah) */
    stock: Array<{ warehouse_id: number | null; condition: string | null; quantity: number }>;
}

/** Props bersama halaman Create & Edit barang (dari ProductFormOptionsQuery) */
export interface ProductFormOptions {
    units: Array<{ id: number; name: string }>;
    categories: Array<{ id: number; name: string; unit_id: number | null }>;
    uoms: Array<{ id: number; name: string; unit_id: number | null }>;
    warehouses: Array<{ id: number; name: string; unit_id: number | null }>;
}
