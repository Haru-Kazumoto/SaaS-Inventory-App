<template>
    <div class="flex flex-col gap-5">
        <TitlePage eyebrow="Manajemen Data" :title="title" :subTitle="subtitle">
            <template #action>
                <n-button v-if="canCreate" type="primary" class="w-full sm:w-auto" @click="openCreate">
                    <template #icon>
                        <n-icon :component="Add20Regular" />
                    </template>
                    Tambah {{ itemLabel }}
                </n-button>
            </template>
        </TitlePage>

        <DataTable
            :data="filtered"
            :columns="columns"
            :get-row-id="(row) => String(row.id)"
            min-width="720px"
            :empty-title="q ? 'Tidak ditemukan' : `Belum ada ${itemLabel.toLowerCase()}`"
            :empty-description="
                q
                    ? `Tidak ada ${itemLabel.toLowerCase()} yang cocok dengan “${q}”.`
                    : canCreate
                      ? `Klik “Tambah ${itemLabel}” untuk membuat data pertama.`
                      : 'Belum ada data.'
            "
        >
            <template #toolbar>
                <n-input
                    v-model:value="q"
                    clearable
                    :placeholder="`Cari nama atau kode ${itemLabel.toLowerCase()}...`"
                    class="w-full lg:!w-80"
                >
                    <template #prefix>
                        <n-icon :component="Search20Regular" class="text-slate-400" />
                    </template>
                </n-input>
                <span class="text-xs tabular-nums text-slate-500">
                    <template v-if="q">{{ filtered.length }} dari </template>{{ rows.length }}
                    {{ itemLabel.toLowerCase() }}
                </span>
            </template>

            <template #mobile-row="{ row }">
                <div class="flex flex-col gap-3">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded bg-[#2A126C]/[0.06] text-[#2A126C]"
                        >
                            <n-icon :component="icon" size="18" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ row.name }}</p>
                            <p class="truncate text-[11px] text-slate-500">
                                <span class="font-mono">{{ row.code }}</span>
                                <template v-if="ownerColumn"> · {{ row.unit_name ?? "Bersama" }}</template>
                            </p>
                            <p v-if="row.description" class="mt-1 line-clamp-2 text-xs text-slate-600">
                                {{ row.description }}
                            </p>
                        </div>
                    </div>
                    <div v-if="row.can_edit" class="grid grid-cols-2 gap-2">
                        <n-button @click="openEdit(row)">
                            <template #icon><n-icon :component="Edit20Regular" /></template>
                            Edit
                        </n-button>
                        <n-button type="error" ghost @click="openDelete(row)">
                            <template #icon><n-icon :component="Delete20Regular" /></template>
                            Hapus
                        </n-button>
                    </div>
                </div>
            </template>
        </DataTable>

        <!-- ============ FORM TAMBAH / UBAH ============ -->
        <n-modal
            v-model:show="showForm"
            preset="card"
            :bordered="false"
            :title="isEdit ? `Ubah ${itemLabel}` : `Tambah ${itemLabel}`"
            class="!w-[calc(100vw-2rem)] max-w-lg !rounded"
            :segmented="{ content: true, footer: true }"
            :mask-closable="!form.processing"
        >
            <p v-if="$slots.hint" class="mb-4 text-xs leading-relaxed text-slate-500">
                <slot name="hint" />
            </p>

            <n-form
                ref="formRef"
                :model="form"
                :rules="rules"
                label-placement="top"
                require-mark-placement="right-hanging"
                @submit.prevent="submitForm"
            >
                <n-form-item label="Nama" path="name" v-bind="fieldError('name')">
                    <n-input
                        ref="nameInput"
                        :value="form.name"
                        :placeholder="`cth: ${examples.name}`"
                        maxlength="100"
                        @update:value="onNameInput"
                        @keyup.enter="submitForm"
                    />
                </n-form-item>

                <n-form-item label="Kode" path="code" v-bind="fieldError('code')">
                    <div class="flex w-full flex-col gap-1">
                        <n-input
                            :value="form.code"
                            :placeholder="`cth: ${examples.code}`"
                            maxlength="50"
                            @update:value="onCodeInput"
                            @keyup.enter="submitForm"
                        >
                            <template #suffix>
                                <span
                                    v-if="codeAuto && form.code"
                                    class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold text-slate-500"
                                >
                                    otomatis
                                </span>
                            </template>
                        </n-input>
                        <span class="text-[11px] text-slate-400">
                            Dibuat otomatis dari nama. Huruf kapital, spasi diganti “_”. Harus unik.
                        </span>
                    </div>
                </n-form-item>

                <n-form-item
                    v-if="ownerOptions.length"
                    label="Pemilik data"
                    path="unit_id"
                    v-bind="fieldError('unit_id')"
                >
                    <n-select
                        :value="form.unit_id"
                        :options="ownerOptions as any"
                        :disabled="isEdit"
                        @update:value="(v) => (form.unit_id = v)"
                    />
                </n-form-item>

                <n-form-item label="Deskripsi" path="description" v-bind="fieldError('description')">
                    <n-input
                        v-model:value="form.description"
                        type="textarea"
                        :autosize="{ minRows: 2, maxRows: 5 }"
                        :placeholder="`Opsional${examples.description ? `, cth: ${examples.description}` : ''}`"
                        maxlength="255"
                        show-count
                    />
                </n-form-item>
            </n-form>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <n-button :disabled="form.processing" @click="showForm = false">Batal</n-button>
                    <n-button type="primary" :loading="form.processing" @click="submitForm">
                        {{ isEdit ? "Simpan Perubahan" : `Simpan ${itemLabel}` }}
                    </n-button>
                </div>
            </template>
        </n-modal>

        <!-- ============ KONFIRMASI HAPUS ============ -->
        <n-modal
            v-model:show="showDelete"
            preset="card"
            :bordered="false"
            :title="`Hapus ${itemLabel}?`"
            class="!w-[calc(100vw-2rem)] max-w-md !rounded"
            :segmented="{ footer: true }"
            :mask-closable="!deleting"
        >
            <div class="flex gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded bg-rose-50 text-rose-600">
                    <n-icon :component="Warning20Regular" size="20" />
                </div>
                <div class="text-sm leading-relaxed text-slate-600">
                    <p>
                        <b class="text-slate-900">{{ selected?.name }}</b>
                        <span class="ml-1 font-mono text-xs text-slate-400">({{ selected?.code }})</span>
                        akan dihapus permanen.
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        Data yang masih dipakai tidak bisa dihapus.
                    </p>
                    <p v-if="deleteError" class="mt-2 text-xs font-medium text-rose-600">{{ deleteError }}</p>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <n-button :disabled="deleting" @click="showDelete = false">Batal</n-button>
                    <n-button type="error" :loading="deleting" @click="confirmDelete">Ya, hapus</n-button>
                </div>
            </template>
        </n-modal>
    </div>
</template>

<script setup lang="ts">
import { computed, h, nextTick, ref, type Component } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import { createColumnHelper, type ColumnDef } from "@tanstack/vue-table";
import {
    NButton,
    NForm,
    NFormItem,
    NIcon,
    NInput,
    NModal,
    NSelect,
    type FormInst,
    type FormRules,
    type InputInst,
} from "naive-ui";
import {
    Add20Regular,
    Delete20Regular,
    Edit20Regular,
    Search20Regular,
    Warning20Regular,
} from "@vicons/fluent";
import TitlePage from "@/Components/TitlePage.vue";
import DataTable from "@/Components/DataTable/DataTable.vue";
import type { MasterRow } from "./types";

const props = withDefaults(
    defineProps<{
        /** jenis data master: units | warehouses | categories | uoms */
        type: string;
        title: string;
        subtitle: string;
        itemLabel: string;
        icon: Component;
        rows: MasterRow[];
        /** pilihan pemilik data (kosong = jenis tanpa pemilik, mis. unit) */
        ownerOptions?: { label: string; value: number | null }[];
        canCreate?: boolean;
        examples: { name: string; code: string; description?: string };
    }>(),
    { ownerOptions: () => [], canCreate: true },
);

/* ---------------- Pencarian ---------------- */
const q = ref("");
const filtered = computed(() => {
    const s = q.value.trim().toLowerCase();
    if (!s) return props.rows;
    return props.rows.filter((r) =>
        [r.name, r.code, r.description, r.unit_name].some((v) => (v ?? "").toLowerCase().includes(s)),
    );
});

const ownerColumn = computed(() => props.ownerOptions.length > 0 || props.rows.some((r) => r.unit_name));

/* ---------------- Form ---------------- */
const form = useForm({ name: "", code: "", description: "", unit_id: null as number | null });
const formRef = ref<FormInst | null>(null);
const nameInput = ref<InputInst | null>(null);
const showForm = ref(false);
const isEdit = ref(false);
const selected = ref<MasterRow | null>(null);
const codeAuto = ref(true);

const toCode = (v: string) => v.toUpperCase().replace(/[^A-Z0-9]+/g, "_").replace(/^_+/, "");

function onNameInput(v: string) {
    form.name = v;
    if (codeAuto.value) form.code = toCode(v).replace(/_+$/, "");
}
function onCodeInput(v: string) {
    form.code = toCode(v);
    codeAuto.value = form.code === "";
}

const rules: FormRules = {
    name: { required: true, message: "Nama wajib diisi", trigger: ["input", "blur"] },
    code: { required: true, message: "Kode wajib diisi", trigger: ["input", "blur"] },
};

function fieldError(field: string) {
    const msg = (form.errors as Record<string, string>)[field];
    return msg ? { validationStatus: "error" as const, feedback: msg } : {};
}

function openCreate() {
    isEdit.value = false;
    selected.value = null;
    form.reset();
    form.clearErrors();
    form.unit_id = (props.ownerOptions[0]?.value ?? null) as number | null;
    codeAuto.value = true;
    showForm.value = true;
    nextTick(() => nameInput.value?.focus());
}

function openEdit(row: MasterRow) {
    isEdit.value = true;
    selected.value = row;
    form.clearErrors();
    form.name = row.name ?? "";
    form.code = row.code ?? "";
    form.description = row.description ?? "";
    form.unit_id = row.unit_id ?? null;
    codeAuto.value = false;
    showForm.value = true;
    nextTick(() => nameInput.value?.focus());
}

async function submitForm() {
    if (form.processing) return;
    try {
        await formRef.value?.validate();
    } catch {
        return;
    }
    form.code = form.code.replace(/_+$/, "");

    const options = { preserveScroll: true, onSuccess: () => (showForm.value = false) };

    if (isEdit.value && selected.value) {
        form.put(route("master-data.update", { type: props.type, id: selected.value.id }), options);
    } else {
        form.post(route("master-data.store", props.type), options);
    }
}

/* ---------------- Hapus ---------------- */
const showDelete = ref(false);
const deleting = ref(false);
const deleteError = ref<string | null>(null);

function openDelete(row: MasterRow) {
    selected.value = row;
    deleteError.value = null;
    showDelete.value = true;
}

function confirmDelete() {
    if (!selected.value) return;
    router.delete(route("master-data.destroy", { type: props.type, id: selected.value.id }), {
        preserveScroll: true,
        onStart: () => (deleting.value = true),
        onFinish: () => (deleting.value = false),
        onSuccess: () => (showDelete.value = false),
        onError: (errors) => (deleteError.value = (errors as Record<string, string>).delete ?? "Gagal menghapus data."),
    });
}

/* ---------------- Kolom ---------------- */
const col = createColumnHelper<MasterRow>();

const columns = computed(() => {
    const list: ColumnDef<MasterRow, any>[] = [
        col.display({
            id: "index",
            header: "#",
            meta: { width: "52px", align: "center" },
            cell: ({ row }) => h("span", { class: "text-xs tabular-nums text-slate-400" }, row.index + 1),
        }),
        col.accessor("name", {
            header: "Nama",
            meta: { minWidth: "220px" },
            enableSorting: true,
            cell: ({ getValue }) =>
                h("div", { class: "flex items-center gap-2.5" }, [
                    h(
                        "div",
                        { class: "flex h-8 w-8 shrink-0 items-center justify-center rounded bg-[#2A126C]/[0.06] text-[#2A126C]" },
                        [h(NIcon, { component: props.icon, size: 16 })],
                    ),
                    h("span", { class: "truncate text-[13px] font-semibold text-slate-900" }, getValue()),
                ]),
        }),
        col.accessor("code", {
            header: "Kode",
            meta: { width: "180px" },
            enableSorting: true,
            cell: ({ getValue }) =>
                h(
                    "span",
                    { class: "whitespace-nowrap rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 font-mono text-[11px] text-slate-600" },
                    getValue(),
                ),
        }),
    ];

    if (ownerColumn.value) {
        list.push(
            col.accessor("unit_name", {
                header: "Pemilik",
                meta: { width: "150px" },
                enableSorting: true,
                cell: ({ getValue }) =>
                    getValue()
                        ? h("span", { class: "text-xs font-medium text-slate-700" }, getValue())
                        : h("span", { class: "rounded bg-slate-100 px-1.5 py-0.5 text-[11px] font-semibold text-slate-500" }, "Bersama"),
            }),
        );
    }

    if (props.rows.some((r) => r.members_count !== null && r.members_count !== undefined)) {
        list.push(
            col.accessor("members_count", {
                header: "Anggota",
                meta: { width: "100px", align: "right" },
                cell: ({ getValue }) => h("span", { class: "text-sm tabular-nums text-slate-700" }, getValue() ?? 0),
            }),
        );
    }

    list.push(
        col.accessor("description", {
            header: "Deskripsi",
            meta: { minWidth: "200px" },
            cell: ({ getValue }) =>
                h("span", { class: "line-clamp-2 text-xs text-slate-600", title: getValue() || undefined }, getValue() || "-"),
        }),
        col.display({
            id: "action",
            header: "Aksi",
            meta: { width: "180px", align: "right", sticky: true },
            cell: ({ row }) =>
                row.original.can_edit
                    ? h("div", { class: "flex justify-end gap-1.5" }, [
                          h(
                              NButton,
                              { size: "small", onClick: () => openEdit(row.original) },
                              { icon: () => h(NIcon, null, { default: () => h(Edit20Regular) }), default: () => "Edit" },
                          ),
                          h(
                              NButton,
                              { size: "small", type: "error", ghost: true, onClick: () => openDelete(row.original) },
                              { icon: () => h(NIcon, null, { default: () => h(Delete20Regular) }), default: () => "Hapus" },
                          ),
                      ])
                    : h("span", { class: "text-[11px] text-slate-400" }, "Hanya lihat"),
        }),
    );

    return list;
});
</script>
