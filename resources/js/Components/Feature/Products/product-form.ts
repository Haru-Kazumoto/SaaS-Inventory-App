/** Data yang dikirim ProductForm saat submit (create & edit) */
export interface ProductFormPayload {
    name: string | null;
    code: string | null;
    brand_name: string;
    type: string;
    price: number | null;
    labeling_date: string | null;
    warehouse_code: null;
    sub_type_code: string | null;
    unit_code: string | null;
    image_product: File | null;
    quantities: Array<{
        status: string | null;
        warehouse_id: number | null;
        warehouse_name: string | null;
        quantity: number | null;
    }>;
}
