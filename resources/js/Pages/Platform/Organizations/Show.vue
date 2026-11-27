<template>
    <div class="flex flex-col gap-5">
        <TitlePage eyebrow="Organisasi" :title="organization.name" :subTitle="subtitle">
            <template #action>
                <div class="flex w-full flex-wrap gap-2 sm:w-auto">
                    <n-button class="flex-1 sm:flex-none" @click="router.visit(route('platform.organizations.index'))">
                        <template #icon><n-icon :component="ArrowLeft20Regular" /></template>
                        Kembali
                    </n-button>
                    <n-button class="flex-1 sm:flex-none" @click="showEdit = true">
                        <template #icon><n-icon :component="Edit20Regular" /></template>
                        Ubah profil
                    </n-button>
                    <n-dropdown trigger="click" :options="statusActions" @select="openStatus">
                        <n-button class="flex-1 sm:flex-none">
                            Ubah status
                            <template #icon><n-icon :component="ChevronDown20Regular" /></template>
                        </n-button>
                    </n-dropdown>
                </div>
            </template>
        </TitlePage>

        <!-- Banner status -->
        <div
            v-if="organization.status === 'suspended' || organization.status === 'cancelled' || billing.outstanding"
            class="flex flex-col gap-1 rounded border px-4 py-3 text-sm sm:flex-row sm:items-center sm:justify-between"
            :class="organization.status === 'suspended' || organization.status === 'cancelled'
                ? 'border-rose-200 bg-rose-50 text-rose-800'
                : 'border-amber-200 bg-amber-50 text-amber-800'"
        >
            <span v-if="organization.status === 'suspended'">
                Organisasi <b>ditangguhkan</b> sejak {{ formatDateTime(organization.suspended_at) }}.
            </span>
            <span v-else-if="organization.status === 'cancelled'">Organisasi sudah <b>berhenti</b> berlangganan.</span>
            <span v-else>Ada tagihan belum dibayar sebesar <b class="tabular-nums">{{ formatIDR(billing.outstanding) }}</b>.</span>
            <n-button
                v-if="organization.status === 'suspended' || organization.status === 'cancelled'"
                size="small"
                type="primary"
                @click="openStatus('active')"
            >
                Aktifkan kembali
            </n-button>
        </div>

        <div
            v-if="!hasOwner"
            class="flex flex-col gap-2 rounded border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900 sm:flex-row sm:items-center sm:justify-between"
        >
            <span>Organisasi ini belum punya <b>akun pemilik</b>, sehingga belum ada yang bisa login.</span>
            <n-button size="small" type="primary" @click="openOwner">
                <template #icon><n-icon :component="PersonAdd20Regular" /></template>
                Buat akun pemilik
            </n-button>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <div class="flex flex-col gap-5 lg:col-span-2">
                <!-- Langganan -->
                <section class="rounded border border-slate-200 bg-white">
                    <header class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">Langganan</p>
                            <h2 class="text-sm font-bold text-slate-900">{{ subscription?.plan.name ?? "Belum berlangganan" }}</h2>
                        </div>
                        <n-button type="primary" size="small" @click="showPlan = true">
                            <template #icon><n-icon :component="ArrowSwap20Regular" /></template>
                            {{ subscription ? "Ganti paket" : "Pilih paket" }}
                        </n-button>
                    </header>

                    <div v-if="subscription" class="grid grid-cols-2 gap-px bg-slate-100 sm:grid-cols-4">
                        <div v-for="s in subStats" :key="s.label" class="bg-white px-5 py-3">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-400">{{ s.label }}</p>
                            <p class="mt-0.5 text-sm font-semibold tabular-nums text-slate-900"><component :is="s.render" /></p>
                        </div>
                    </div>
                    <p v-else class="px-5 py-6 text-sm text-slate-500">
                        Organisasi ini belum memiliki paket. Pilih paket untuk mulai menagih.
                    </p>

                    <div v-if="subscription" class="border-t border-slate-100 px-5 py-4">
                        <p class="mb-2 text-xs font-semibold text-slate-600">Pemakaian</p>
                        <div class="flex items-center gap-3 text-xs">
                            <span class="w-20 text-slate-600">Pengguna</span>
                            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                                <div
                                    class="h-full rounded-full"
                                    :class="usagePct >= 100 ? 'bg-rose-500' : usagePct >= 80 ? 'bg-amber-500' : 'bg-[#2A126C]'"
                                    :style="{ width: `${Math.min(100, usagePct)}%` }"
                                ></div>
                            </div>
                            <span class="w-20 text-right tabular-nums text-slate-700">
                                {{ usage.users }} / {{ subscription.plan.max_users ?? "∞" }}
                            </span>
                        </div>
                        <div v-for="u in usageRows" :key="u.label" class="mt-2 flex items-center gap-3 text-xs">
                            <span class="w-20 text-slate-600">{{ u.label }}</span>
                            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                                <div
                                    class="h-full rounded-full"
                                    :class="u.pct >= 100 ? 'bg-rose-500' : u.pct >= 80 ? 'bg-amber-500' : 'bg-[#2A126C]'"
                                    :style="{ width: `${Math.min(100, u.pct)}%` }"
                                ></div>
                            </div>
                            <span class="w-20 text-right tabular-nums text-slate-700">{{ u.used }} / {{ u.max ?? "∞" }}</span>
                        </div>
                    </div>
                </section>

                <!-- Tagihan -->
                <section class="rounded border border-slate-200 bg-white">
                    <header class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Tagihan</h2>
                            <p class="text-[11px] text-slate-500">Total dibayar {{ formatIDR(billing.paid_total) }}</p>
                        </div>
                        <n-button size="small" @click="showInvoice = true">
                            <template #icon><n-icon :component="Add20Regular" /></template>
                            Buat tagihan
                        </n-button>
                    </header>
                    <p v-if="!invoices.length" class="px-5 py-8 text-center text-sm text-slate-400">Belum ada tagihan.</p>
                    <ul v-else class="divide-y divide-slate-100">
                        <li v-for="i in invoices" :key="i.id">
                            <button
                                type="button"
                                class="flex w-full items-center gap-3 px-5 py-3 text-left hover:bg-slate-50/60"
                                @click="router.visit(route('platform.invoices.show', i.id))"
                            >
                                <div class="min-w-0 flex-1">
                                    <p class="font-mono text-[13px] font-semibold text-slate-900">{{ i.number }}</p>
                                    <p class="text-[11px] text-slate-400">Terbit {{ formatDay(i.issued_at) }} · jatuh tempo {{ formatDay(i.due_at) }}</p>
                                </div>
                                <StatusBadge kind="invoice" :status="i.display_status" />
                                <span class="w-28 text-right text-sm font-bold tabular-nums text-slate-900">{{ formatIDR(i.total) }}</span>
                            </button>
                        </li>
                    </ul>
                </section>

                <!-- Unit -->
                <section class="rounded border border-slate-200 bg-white">
                    <header class="border-b border-slate-100 px-5 py-4">
                        <h2 class="text-sm font-bold text-slate-900">{{ terms.unit_plural }} ({{ units.length }})</h2>
                        <p class="text-[11px] text-slate-500">Disebut "{{ terms.unit }}" di organisasi ini.</p>
                    </header>
                    <ul class="divide-y divide-slate-100">
                        <li v-for="u in units" :key="u.id" class="flex items-center justify-between gap-3 px-5 py-2.5 text-sm">
                            <span class="font-medium text-slate-900">{{ u.name }} <span class="font-mono text-[11px] text-slate-400">{{ u.code }}</span></span>
                            <span class="text-xs tabular-nums text-slate-500">{{ u.members_count }} anggota</span>
                        </li>
                        <li v-if="!units.length" class="px-5 py-6 text-center text-xs text-slate-400">Belum ada unit.</li>
                    </ul>
                </section>

                <!-- Pengguna -->
                <section class="rounded border border-slate-200 bg-white">
                    <header class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                        <h2 class="text-sm font-bold text-slate-900">Pengguna ({{ users.length }})</h2>
                        <button
                            class="text-xs font-semibold text-[#2A126C] hover:underline"
                            @click="router.visit(route('platform.users.index', { organization_id: organization.id }))"
                        >
                            Kelola akun
                        </button>
                    </header>
                    <p v-if="!users.length" class="px-5 py-8 text-center text-sm text-slate-400">Belum ada pengguna.</p>
                    <ul v-else class="divide-y divide-slate-100">
                        <li v-for="u in users" :key="u.id" class="flex items-center gap-3 px-5 py-2.5">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-[11px] font-semibold text-slate-600">
                                {{ initials(u.fullname) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-slate-900">{{ u.fullname }}</p>
                                <p class="truncate text-[11px] text-slate-400">@{{ u.username }} · {{ u.email || "-" }}</p>
                            </div>
                            <span class="flex shrink-0 flex-col items-end gap-0.5">
                                <span
                                    class="rounded border px-1.5 py-0.5 text-[11px] font-semibold"
                                    :class="u.organization_role === 'member' ? 'border-slate-200 bg-slate-50 text-slate-600' : 'border-[#2A126C]/20 bg-[#2A126C]/[0.06] text-[#2A126C]'"
                                >
                                    {{ u.role }}
                                </span>
                                <span v-if="u.units.length" class="text-[10px] text-slate-400">{{ u.units.join(", ") }}</span>
                            </span>
                        </li>
                    </ul>
                </section>
            </div>

            <!-- Sidebar -->
            <div class="flex flex-col gap-5">
                <section class="rounded border border-slate-200 bg-white">
                    <header class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                        <h2 class="text-sm font-bold text-slate-900">Profil</h2>
                        <StatusBadge kind="organization" :status="organization.status" />
                    </header>
                    <dl class="divide-y divide-slate-100 px-5 text-sm">
                        <div v-for="r in profileRows" :key="r.label" class="flex justify-between gap-3 py-2.5">
                            <dt class="shrink-0 text-xs text-slate-500">{{ r.label }}</dt>
                            <dd class="min-w-0 break-words text-right text-slate-900">{{ r.value || "-" }}</dd>
                        </div>
                    </dl>
                    <div v-if="organization.notes" class="border-t border-slate-100 px-5 py-4">
                        <p class="mb-1 text-xs font-semibold text-slate-600">Catatan internal</p>
                        <p class="whitespace-pre-line text-xs leading-relaxed text-slate-600">{{ organization.notes }}</p>
                    </div>
                </section>

                <section class="rounded border border-slate-200 bg-white">
                    <header class="border-b border-slate-100 px-5 py-4">
                        <h2 class="text-sm font-bold text-slate-900">Pembayaran terakhir</h2>
                    </header>
                    <p v-if="!payments.length" class="px-5 py-6 text-center text-xs text-slate-400">Belum ada pembayaran.</p>
                    <ul v-else class="divide-y divide-slate-100">
                        <li v-for="p in payments" :key="p.id" class="flex items-center justify-between gap-2 px-5 py-2.5 text-xs">
                            <div class="min-w-0">
                                <p class="font-mono text-slate-700">{{ p.invoice_number }}</p>
                                <p class="text-slate-400">{{ formatDay(p.paid_at) }} · {{ labelOf(PAYMENT_METHODS, p.method) }}</p>
                            </div>
                            <span class="font-bold tabular-nums text-emerald-700">+{{ formatIDR(p.amount) }}</span>
                        </li>
                    </ul>
                </section>

                <section class="rounded border border-slate-200 bg-white">
                    <header class="border-b border-slate-100 px-5 py-4">
                        <h2 class="text-sm font-bold text-slate-900">Riwayat paket</h2>
                    </header>
                    <ul class="divide-y divide-slate-100">
                        <li v-for="s in subscriptions" :key="s.id" class="flex items-center justify-between gap-2 px-5 py-2.5 text-xs">
                            <div>
                                <p class="font-semibold text-slate-800">{{ s.plan }} · {{ labelOf(BILLING_CYCLES, s.billing_cycle) }}</p>
                                <p class="text-slate-400">{{ formatDay(s.period_start) }} – {{ formatDay(s.period_end) }}</p>
                            </div>
                            <StatusBadge kind="subscription" :status="s.status" />
                        </li>
                        <li v-if="!subscriptions.length" class="px-5 py-6 text-center text-xs text-slate-400">Belum ada riwayat.</li>
                    </ul>
                </section>
            </div>
        </div>

        <!-- Modal -->
        <OrganizationFormModal v-model:show="showEdit" :plans="plans" :organization="organization" />
        <ChangePlanModal
            v-model:show="showPlan"
            :organization-id="organization.id"
            :plans="plans"
            :current-plan-id="subscription?.plan.id"
            :current-cycle="subscription?.billing_cycle"
        />
        <IssueInvoiceModal v-model:show="showInvoice" :organization-id="organization.id" />

        <n-modal
            v-model:show="showOwner"
            preset="card"
            :bordered="false"
            title="Buat akun pemilik"
            class="!w-[calc(100vw-2rem)] max-w-md !rounded"
            :segmented="{ content: true, footer: true }"
        >
            <div class="flex flex-col gap-3">
                <label class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-slate-600">Nama lengkap</span>
                    <n-input v-model:value="ownerForm.fullname" :status="ownerForm.errors.fullname ? 'error' : undefined" />
                    <span v-if="ownerForm.errors.fullname" class="text-xs text-rose-600">{{ ownerForm.errors.fullname }}</span>
                </label>
                <label class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-slate-600">Email (untuk login & tagihan)</span>
                    <n-input v-model:value="ownerForm.email" inputmode="email" :status="ownerForm.errors.email ? 'error' : undefined" />
                    <span v-if="ownerForm.errors.email" class="text-xs text-rose-600">{{ ownerForm.errors.email }}</span>
                </label>
                <label class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-slate-600">Username <span class="font-normal text-slate-400">(opsional, otomatis dari email)</span></span>
                    <n-input v-model:value="ownerForm.username" placeholder="cth: budi.smkx" :status="ownerForm.errors.username ? 'error' : undefined" />
                    <span v-if="ownerForm.errors.username" class="text-xs text-rose-600">{{ ownerForm.errors.username }}</span>
                </label>
                <p class="text-xs text-slate-500">Password acak dibuat otomatis dan ditampilkan sekali setelah disimpan.</p>
            </div>
            <template #footer>
                <div class="flex justify-end gap-2">
                    <n-button @click="showOwner = false">Batal</n-button>
                    <n-button type="primary" :loading="ownerForm.processing" @click="submitOwner">Buat akun</n-button>
                </div>
            </template>
        </n-modal>

        <n-modal
            v-model:show="showPassword"
            preset="card"
            :bordered="false"
            title="Password akun"
            class="!w-[calc(100vw-2rem)] max-w-md !rounded"
            :segmented="{ footer: true }"
        >
            <p class="mb-3 text-sm text-slate-600">Berikan ke pemilik organisasi. Password <b>hanya ditampilkan sekali</b>.</p>
            <code class="block select-all rounded border border-slate-200 bg-slate-50 px-3 py-2 font-mono text-base font-bold tracking-wider text-slate-900">{{ generatedPassword }}</code>
            <template #footer>
                <div class="flex justify-end"><n-button type="primary" @click="showPassword = false">Selesai</n-button></div>
            </template>
        </n-modal>

        <n-modal
            v-model:show="showStatus"
            preset="card"
            :bordered="false"
            :title="statusTitle"
            class="!w-[calc(100vw-2rem)] max-w-md !rounded"
            :segmented="{ footer: true }"
        >
            <p class="mb-3 text-sm text-slate-600">{{ statusHelp }}</p>
            <n-input
                v-model:value="statusForm.reason"
                type="textarea"
                :autosize="{ minRows: 2 }"
                :placeholder="statusForm.status === 'active' ? 'Catatan (opsional)' : 'Alasan (wajib)'"
                :status="statusForm.errors.reason ? 'error' : undefined"
            />
            <p v-if="statusForm.errors.reason" class="mt-1 text-xs text-rose-600">{{ statusForm.errors.reason }}</p>
            <template #footer>
                <div class="flex justify-end gap-2">
                    <n-button @click="showStatus = false">Batal</n-button>
                    <n-button
                        :type="statusForm.status === 'active' ? 'primary' : 'error'"
                        :loading="statusForm.processing"
                        @click="submitStatus"
                    >
                        {{ statusTitle }}
                    </n-button>
                </div>
            </template>
        </n-modal>
    </div>
</template>

<script setup lang="ts">
import { computed, h, ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import { NButton, NDropdown, NIcon, NInput, NModal } from "naive-ui";
import { Add20Regular, ArrowLeft20Regular, ArrowSwap20Regular, ChevronDown20Regular, Edit20Regular, PersonAdd20Regular } from "@vicons/fluent";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TitlePage from "@/Components/TitlePage.vue";
import StatusBadge from "@/Components/Feature/Platform/StatusBadge.vue";
import OrganizationFormModal from "@/Components/Feature/Platform/OrganizationFormModal.vue";
import ChangePlanModal from "@/Components/Feature/Platform/ChangePlanModal.vue";
import IssueInvoiceModal from "@/Components/Feature/Platform/IssueInvoiceModal.vue";
import {
    BILLING_CYCLES,
    PAYMENT_METHODS,
    TEMPLATES,
    formatDateTime,
    formatDay,
    formatIDR,
    initials,
    labelOf,
} from "@/Components/Feature/Platform/platform-meta";
import { useFlash } from "@/Composables/useFlash";

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps<{
    organization: Record<string, any>;
    subscription: null | {
        id: number;
        status: string;
        billing_cycle: string;
        price: number;
        period_start: string;
        period_end: string;
        days_left: number;
        plan: { id: number; name: string; max_units: number | null; max_users: number | null; max_products: number | null };
    };
    usage: { users: number; units: number; products: number };
    billing: { outstanding: number; paid_total: number };
    users: { id: number; fullname: string; username: string; email?: string; role: string; organization_role?: string; units: string[] }[];
    units: { id: number; name: string; code: string; members_count: number }[];
    terms: { unit: string; unit_plural: string };
    invoices: { id: number; number: string; display_status: string; total: number; issued_at: string; due_at: string }[];
    subscriptions: { id: number; plan: string; status: string; billing_cycle: string; period_start: string; period_end: string }[];
    payments: { id: number; invoice_number: string; amount: number; method: string; paid_at: string }[];
    plans: { id: number; name: string; price_monthly: number; price_yearly: number }[];
}>();

useFlash();

const showEdit = ref(false);
const showPlan = ref(false);
const showInvoice = ref(false);

const subtitle = computed(
    () => `${labelOf(TEMPLATES, props.organization.template)} · terdaftar ${formatDay(props.organization.created_at)}`,
);

const usagePct = computed(() => {
    const max = props.subscription?.plan.max_users;
    return max ? Math.round((props.usage.users / max) * 100) : 0;
});

const usageRows = computed(() => {
    const plan = props.subscription?.plan;
    const row = (label: string, used: number, max: number | null | undefined) => ({
        label,
        used,
        max,
        pct: max ? Math.round((used / max) * 100) : 0,
    });
    return [
        row(props.terms.unit_plural, props.usage.units, plan?.max_units),
        row("Barang", props.usage.products, plan?.max_products),
    ];
});

/* ---------------- Akun pemilik ---------------- */
const hasOwner = computed(() => props.users.some((u) => u.organization_role === "owner"));
const showOwner = ref(false);
const showPassword = ref(false);
const generatedPassword = ref("");
const ownerForm = useForm({ fullname: "", email: "", username: "" });

function openOwner() {
    ownerForm.clearErrors();
    ownerForm.fullname = props.organization.owner_name ?? "";
    ownerForm.email = props.organization.owner_email ?? "";
    ownerForm.username = "";
    showOwner.value = true;
}

function submitOwner() {
    ownerForm.post(route("platform.organizations.owner", props.organization.id), {
        preserveScroll: true,
        onSuccess: (p) => {
            showOwner.value = false;
            const pwd = (p.props as any).flash?.generated_password;
            if (pwd) {
                generatedPassword.value = pwd;
                showPassword.value = true;
            }
        },
    });
}

const subStats = computed(() => {
    const s = props.subscription!;
    const left = s.days_left;
    return [
        { label: "Status", render: () => h(StatusBadge, { kind: "subscription", status: s.status }) },
        { label: "Siklus", render: () => labelOf(BILLING_CYCLES, s.billing_cycle) },
        { label: "Harga", render: () => (s.price ? formatIDR(s.price) : "Gratis") },
        {
            label: "Periode berakhir",
            render: () =>
                h("span", {}, [
                    formatDay(s.period_end),
                    h(
                        "span",
                        { class: `ml-1 text-[11px] font-normal ${left < 0 ? "text-rose-600" : left <= 7 ? "text-amber-600" : "text-slate-400"}` },
                        left < 0 ? `(lewat ${-left} hr)` : `(${left} hr lagi)`,
                    ),
                ]),
        },
    ];
});

const profileRows = computed(() => [
    { label: "Jenis", value: labelOf(TEMPLATES, props.organization.template) },
    { label: "Slug", value: props.organization.slug },
    { label: "Pemilik", value: props.organization.owner_name },
    { label: "Email", value: props.organization.owner_email },
    { label: "Telepon", value: props.organization.phone },
    { label: "Alamat", value: props.organization.address },
    { label: "Masa coba s.d.", value: props.organization.trial_ends_at ? formatDay(props.organization.trial_ends_at) : null },
]);

/* ---------------- Ubah status ---------------- */
const STATUS_TEXT: Record<string, { title: string; help: string }> = {
    active: { title: "Aktifkan", help: "Organisasi dapat digunakan kembali secara normal." },
    past_due: { title: "Tandai menunggak", help: "Gunakan bila tagihan sudah lewat jatuh tempo. Status kembali aktif otomatis saat tagihan dilunasi." },
    suspended: { title: "Tangguhkan", help: "Akses organisasi dihentikan sementara (mis. tagihan tidak dibayar). Data tetap tersimpan." },
    cancelled: { title: "Hentikan langganan", help: "Langganan berjalan dibatalkan. Data tetap tersimpan dan organisasi bisa diaktifkan kembali." },
};

const statusActions = computed(() =>
    Object.entries(STATUS_TEXT)
        .filter(([key]) => key !== props.organization.status)
        .map(([key, v]) => ({ key, label: v.title })),
);

const showStatus = ref(false);
const statusForm = useForm({ status: "active", reason: "" });
const statusTitle = computed(() => STATUS_TEXT[statusForm.status]?.title ?? "");
const statusHelp = computed(() => STATUS_TEXT[statusForm.status]?.help ?? "");

function openStatus(key: string) {
    statusForm.status = key;
    statusForm.reason = "";
    statusForm.clearErrors();
    showStatus.value = true;
}

function submitStatus() {
    statusForm.patch(route("platform.organizations.status", props.organization.id), {
        preserveScroll: true,
        onSuccess: () => (showStatus.value = false),
    });
}
</script>
