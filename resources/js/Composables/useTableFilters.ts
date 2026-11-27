import { computed, reactive, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";

type FilterValues = Record<string, string | null>;

/**
 * Filter tabel server-side berbasis query string (Inertia).
 * - Nilai awal dibaca dari URL.
 * - `textKeys` diterapkan otomatis setelah user berhenti mengetik (debounce).
 * - Filter lain (select, tanggal) panggil `apply()` saat berubah.
 */
export function useTableFilters<T extends FilterValues>(
    routeName: string,
    initial: T,
    options: { textKeys?: (keyof T)[]; debounce?: number } = {},
) {
    const { textKeys = [], debounce = 450 } = options;
    const params = new URLSearchParams(window.location.search);

    const filters = reactive({ ...initial }) as T;
    (Object.keys(initial) as (keyof T)[]).forEach((key) => {
        const v = params.get(String(key));
        if (v !== null && v !== "") (filters as FilterValues)[key as string] = v;
    });

    const loading = ref(false);

    function apply(page = 1) {
        clearTimeout(timer);
        const query: Record<string, string | number> = {};
        Object.entries(filters).forEach(([k, v]) => {
            if (v !== null && v !== "") query[k] = v as string;
        });
        if (page > 1) query.page = page;

        loading.value = true;
        router.get(route(routeName), query, {
            preserveScroll: true,
            preserveState: true,
            replace: true,
            onFinish: () => (loading.value = false),
        });
    }

    let timer: ReturnType<typeof setTimeout> | undefined;
    if (textKeys.length) {
        watch(
            () => textKeys.map((k) => filters[k]),
            () => {
                clearTimeout(timer);
                timer = setTimeout(() => apply(1), debounce);
            },
        );
    }

    const hasActive = computed(() =>
        Object.values(filters).some((v) => v !== null && v !== ""),
    );

    function reset() {
        (Object.keys(filters) as (keyof T)[]).forEach((k) => {
            (filters as FilterValues)[k as string] = null;
        });
        apply(1);
    }

    return { filters, loading, apply, hasActive, reset };
}
