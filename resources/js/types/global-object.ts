export interface FilterItemRequest {
    search_name: string,
    search_category: string,
    search_warehouse: string
}

export interface PaginationResponse<T> {
    current_page: number,
    data: T[],
    first_page_url: string,
    from: number,
    last_page: number,
    last_page_url: string,
    links: any[],
    next_page_url: string,
    path: string,
    per_page: number,
    prev_page_url: string,
    to: number,
    total: number
}

export interface Major {
    id: number;
    name: string;
    code: string;
}

/** Gudang yang bisa dipakai satu barang, beserta saldo barang tersebut di gudang itu. */
export interface Warehouse {
    id: number;
    name: string;
    current_quantity?: number;
    good_quantity?: number;
    damaged_quantity?: number;
}

export interface Item {
    id: number;
    name: string;
    brand_name: string;
    price: number;
    category: string;
    unit: string;
    major: Major;
    created_by: number;
    updated_by?: number;
    partial_items: PartialItem[];
}

export interface PartialItem {
    id: number;
    labeling_date: string;
    code_item: string;
    condition_item: string;
    base_quantity: number;
    item_image: string;
    created_by: number;
    updated_by?: number;
    warehouse: Warehouse;
    is_available: number;
    item: Item;
}

export interface Statistic {
    month_num: number;
    month_name: string;
    restock_total: number;
    out_total: number;
}

export interface Product {
    id: number;
    name: string;
    code: string;
    brand_name?: string | null;
    type: "DURABLE" | "CONSUMABLE";
    price: number;
    labeling_date?: string | null;
    created_at?: string | null;
    unit_id: number;
    /** unit organisasi pemilik (Jurusan/Divisi/...) */
    unit_name?: string | null;
    category_name?: string | null;
    /** satuan barang (PCS, BOX, ...) */
    uom_name?: string | null;
    image_url?: string | null;
    good_qty: number;
    damaged_qty: number;
    out_qty: number;
    off_qty: number;
    usable_qty: number;
}

export interface WarehouseProduct {
    warehouse_id?: number;
    condition: string;
    warehouse_name: string;
    on_hand: number;
}

export interface StockForm {
    warehouse_id: number | null;
    quantity: number | null;
    warehouse_name: string | null;
    current_quantity: number | null;
}