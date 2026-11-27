import { onBeforeUnmount, onMounted } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { useMessage } from "naive-ui";

/**
 * Tampilkan flash `success` / `failed` dari server sebagai toast.
 * Dipakai per halaman (bukan global) agar tidak dobel dengan halaman lama
 * yang sudah menampilkan toast sendiri.
 */
export function useFlash() {
    const message = useMessage();
    const page = usePage();
    let last = "";
    let lastAt = 0;

    function show(flash?: Record<string, unknown> | null) {
        const success = flash?.success as string | undefined;
        const failed = flash?.failed as string | undefined;
        const text = failed || success;
        if (!text) return;

        // cegah toast ganda (mount + event success pada kunjungan yang sama)
        const now = Date.now();
        if (text === last && now - lastAt < 1500) return;
        last = text;
        lastAt = now;

        failed ? message.error(failed) : message.success(success!);
    }

    let off: (() => void) | undefined;

    onMounted(() => {
        show(page.props.flash as Record<string, unknown>);
        off = router.on("success", (event) => {
            show((event.detail.page.props as any).flash);
        });
    });

    onBeforeUnmount(() => off?.());
}
