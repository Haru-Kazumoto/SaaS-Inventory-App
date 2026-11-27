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

export interface Warehouse {
    id: number;
    name: string;
    code: string;
    major: Major;
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
    brand_name?: string;
    type: "DURABLE" | "CONSUMABLE";
    price: number;
    labeling_date: string;
    warehouse_name: string;
    sub_type_name: string;
    unit_name: string;
    image_product: string;
    major_name: string;
    created_at: string;
    good_qty: number;
    damaged_qty: number;
    out_qty: number;
    off_qty: number;
    usable_qty: number;
}

export interface WarehouseProduct {
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