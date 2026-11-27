<template>
    <OutgoingLines
        kind="durable"
        :lines="lines"
        :warehouses="warehouses"
        :major="major"
    />
</template>

<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import OutgoingLines from "@/Components/Feature/Lines/OutgoingLines.vue";
import type { OutgoingLine } from "@/Components/Feature/Lines/types";
import type { DataTablePagination } from "@/Components/DataTable/types";

defineOptions({ layout: AuthenticatedLayout });

defineProps<{
    lines: DataTablePagination & { data: OutgoingLine[] };
    warehouses?: Array<{ id: number; name: string }>;
    major?: string;
}>();
</script>
