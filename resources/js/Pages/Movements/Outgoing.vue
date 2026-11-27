<template>
    <OutgoingLines :kind="kind" :lines="lines" :warehouses="warehouses" />
</template>

<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import OutgoingLines from "@/Components/Feature/Lines/OutgoingLines.vue";
import type { OutgoingLine } from "@/Components/Feature/Lines/types";
import type { DataTablePagination } from "@/Components/DataTable/types";

defineOptions({ layout: AuthenticatedLayout });

/** Barang keluar: kind=durable → sedang dipinjam, kind=consumable → pemakaian habis pakai. */
defineProps<{
    kind: "durable" | "consumable";
    lines: DataTablePagination & { data: OutgoingLine[] };
    warehouses: Array<{ id: number; name: string }>;
}>();
</script>
