<template>
    <div class="flex flex-col gap-5">
        <TitlePage eyebrow="Manajemen Data" :title="title" :subTitle="subtitle">
            <template #action>
                <n-button type="primary" class="w-full sm:w-auto" @click="openCreate">
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
            :min-width="withDescription ? '760px' : '560px'"
            :empty-title="q ? 'Tidak ditemukan' : `Belum ada ${itemLabel.toLowerCase()}`"
            :empty-description="
                q
                    ? `Tidak ada ${itemLabel.toLowerCase()} yang cocok dengan “${q}”.`
                    : `Klik “Tambah ${itemLabel}” untuk membuat data pertama.`
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
                            <p class="truncate font-mono text-[11px] text-slate-500">{{ row.code }}</p>
                            <p
                                v-if="withDescription && row.description"
                                class="mt-1 line-clamp-2 text-xs text-slate-600"
                            >
                                {{ row.description }}
                            </p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
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
            :mask-closable="!processing"
        >
            <p class="mb-4 text-xs leading-relaxed text-slate-500">
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
                            maxlength="100"
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
                    v-if="withDescription"
                    label="Deskripsi"
                    path="description"
                    v-bind="fieldError('description')"
                >
                    <n-input
                        v-model:value="form.description"
                        type="textarea"
                        :autosize="{ minRows: 2, maxRows: 5 }"
                        :placeholder="`Opsional, cth: ${examples.description ?? ''}`"
                        maxlength="255"
                        show-count
                    />
                </n-form-item>
            </n-form>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <n-button :disabled="processing" @click="showForm = false">Batal</n-button>
                    <n-button type="primary" :loading="processing" @click="submitForm">
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
            :mask-closable="!processing"
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
                        Pastikan tidak ada barang yang masih memakai {{ itemLabel.toLowerCase() }} ini.
                    </p>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <n-button :disabled="processing" @click="showDelete = false">Batal</n-button>
                    <n-button type="error" :loading="processing" @click="confirmDelete">
                        Ya, hapus
                    </n-button>
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
    useMessage,
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
        title: string;
        subtitle: string;
        /** Nama singkat entitas, mis. "Satuan" */
        itemLabel: string;
        icon: Component;
        rows: MasterRow[];
        /** Nama route Ziggy: { store, update, delete } */
        routes: { store: string; update: string; delete: string };
        withDescription?: boolean;
        examples: { name: string; code: string; description?: string };
    }>(),
    { withDescription: false },
);

const message = useMessage();

/* ---------------- Pencarian ---------------- */
const q = ref("");
const filtered = computed(() => {
    const s = q.value.trim().toLowerCase();
    if (!s) return props.rows;
    return props.rows.filter((r) =>
        [r.name, r.code, r.description].some((v) => (v ?? "").toLowerCase().includes(s)),
    );
});

/* ---------------- Form ---------------- */
const form = useForm({ name: "", code: "", description: "" });
const processing = computed(() => form.processing);
const formRef = ref<FormInst | null>(null);
const nameInput = ref<InputInst | null>(null);
const showForm = ref(false);
const isEdit = ref(false);
const selected = ref<MasterRow | null>(null);
/** kode mengikuti nama sampai user mengubah kode sendiri */
const codeAuto = ref(true);

const toCode = (v: string) =>
    v
        .toUpperCase()
        .replace(/[^A-Z0-9]+/g, "_")
        .replace(/^_+/, "");

function onNameInput(v: string) {
    form.name = v.toUpperCase();
    if (codeAuto.value) form.code = toCode(v).replace(/_+$/, "");
}
function onCodeInput(v: string) {
    form.code = toCode(v);
    codeAuto.value = form.code === "" ? true : false;
}

const rules: FormRules = {
    name: { required: true, message: "Nama wajib diisi", trigger: ["input", "blur"] },
    code: { required: true, message: "Kode wajib diisi", trigger: ["input", "blur"] },
};

function fieldError(field: "name" | "code" | "description") {
    const msg = form.errors[field];
    return msg ? { validationStatus: "error" as const, feedback: msg } : {};
}

function openCreate() {
    isEdit.value = false;
    selected.value = null;
    form.reset();
    form.clearErrors();
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
    codeAuto.value = false;
    showForm.value = true;
    nextTick(() => nameInput.value?.focus());
}

async function submitForm() {
    if (processing.value) return;
    try {
        await formRef.value?.validate();
    } catch {
        return;
    }
    form.code = form.code.replace(/_+$/, "");

    const options = {
        preserveScroll: true,
        onSuccess: () => {
            message.success(
                isEdit.value ? `${props.itemLabel} berhasil diperbarui` : `${props.itemLabel} berhasil ditambahkan`,
            );
            showForm.value = false;
        },
        onError: () => message.error("Periksa kembali data yang diisi."),
    };

    if (isEdit.value && selected.value) {
        form.put(route(props.routes.update, selected.value.id), options);
    } else {
        form.post(route(props.routes.store), options);
    }
}

/* ---------------- Hapus ---------------- */
const showDelete = ref(false);

function openDelete(row: MasterRow) {
    selected.value = row;
    showDelete.value = true;
}

function confirmDelete() {
    if (!selected.value) return;
    form.processing = true;
    router.delete(route(props.routes.delete, selected.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDelete.value = false;
            message.success(`${props.itemLabel} berhasil dihapus`);
        },
        onError: () => message.error(`Gagal menghapus ${props.itemLabel.toLowerCase()}.`),
        onFinish: () => (form.processing = false),
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
            cell: ({ row }) =>
                h("span", { class: "text-xs tabular-nums text-slate-400" }, row.index + 1),
        }),
        col.accessor("name", {
            header: "Nama",
            meta: { minWidth: "220px" },
            enableSorting: true,
            cell: ({ getValue }) =>
                h("div", { class: "flex items-center gap-2.5" }, [
                    h(
                        "div",
                        {
                            class: "flex h-8 w-8 shrink-0 items-center justify-center rounded bg-[#2A126C]/[0.06] text-[#2A126C]",
                        },
                        [h(NIcon, { component: props.icon, size: 16 })],
                    ),
                    h("span", { class: "truncate text-[13px] font-semibold text-slate-900" }, getValue()),
                ]),
        }),
        col.accessor("code", {
            header: "Kode",
            meta: { width: "200px" },
            enableSorting: true,
            cell: ({ getValue }) =>
                h(
                    "span",
                    {
                        class: "whitespace-nowrap rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 font-mono text-[11px] text-slate-600",
                    },
                    getValue(),
                ),
        }),
    ];

    if (props.withDescription) {
        list.push(
            col.accessor("description", {
                header: "Deskripsi",
                meta: { minWidth: "220px" },
                cell: ({ getValue }) =>
                    h(
                        "span",
                        { class: "line-clamp-2 text-xs text-slate-600", title: getValue() || undefined },
                        getValue() || "-",
                    ),
            }),
        );
    }

    list.push(
        col.display({
            id: "action",
            header: "Aksi",
            meta: { width: "180px", align: "right", sticky: true },
            cell: ({ row }) =>
                h("div", { class: "flex justify-end gap-1.5" }, [
                    h(
                        NButton,
                        { size: "small", onClick: () => openEdit(row.original) },
                        {
                            icon: () => h(NIcon, null, { default: () => h(Edit20Regular) }),
                            default: () => "Edit",
                        },
                    ),
                    h(
                        NButton,
                        { size: "small", type: "error", ghost: true, onClick: () => openDelete(row.original) },
                        {
                            icon: () => h(NIcon, null, { default: () => h(Delete20Regular) }),
                            default: () => "Hapus",
                        },
                    ),
                ]),
        }),
    );

    return list;
});
</script>
