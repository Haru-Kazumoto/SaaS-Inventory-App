/** Helper tampilan anggota (peran organisasi & unit) */

export const ORG_ROLE_LABEL: Record<string, string> = {
    owner: "Pemilik",
    admin: "Admin organisasi",
    member: "Anggota",
};

export const UNIT_ROLE_LABEL: Record<string, string> = {
    admin: "Admin",
    staff: "Staf",
    viewer: "Viewer",
};

export function roleLabel(role?: string | null) {
    return role ? (ORG_ROLE_LABEL[role] ?? role) : "-";
}

export function roleBadgeClass(role?: string | null) {
    return role === "owner"
        ? "border-[#2A126C] bg-[#2A126C] text-white"
        : role === "admin"
          ? "border-[#2A126C]/30 bg-[#2A126C]/[0.08] text-[#2A126C]"
          : "border-slate-200 bg-slate-50 text-slate-600";
}
