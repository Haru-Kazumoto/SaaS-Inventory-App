import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

export interface OrganizationTerms {
    unit: string;
    unit_plural: string;
    borrower_id_label: string;
    [key: string]: string;
}

const FALLBACK: OrganizationTerms = {
    unit: "Unit",
    unit_plural: "Unit",
    borrower_id_label: "No. identitas",
};

/**
 * Terminologi organisasi aktif (mis. unit = "Jurusan" untuk sekolah, "Divisi" untuk kantor).
 * Pakai ini, jangan tulis "Jurusan" langsung di komponen.
 *
 *   const { t } = useTerms();  →  t("unit")
 */
export function useTerms() {
    const page = usePage();

    const terms = computed<OrganizationTerms>(() => ({
        ...FALLBACK,
        ...((page.props as any).organization?.terms ?? {}),
    }));

    const t = (key: keyof OrganizationTerms | string) => terms.value[key] ?? String(key);

    return { terms, t };
}
