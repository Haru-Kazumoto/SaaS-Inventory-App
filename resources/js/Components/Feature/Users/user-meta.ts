/** Helper tampilan pengguna (role & avatar) */

export function roleLabel(name?: string | null) {
    if (!name) return "-";
    if (name === "SUPERADMIN") return "Superadmin";
    if (name.startsWith("ADMIN_")) return `Admin ${name.slice(6)}`;
    return name.replace(/_/g, " ");
}

/** Jurusan yang tersirat dari nama role, mis. ADMIN_TJKT → TJKT */
export function majorFromRole(name?: string | null) {
    return name?.startsWith("ADMIN_") ? name.slice(6) : null;
}

export function roleBadgeClass(name?: string | null) {
    return name === "SUPERADMIN"
        ? "border-[#2A126C] bg-[#2A126C] text-white"
        : "border-[#2A126C]/15 bg-[#2A126C]/[0.05] text-[#2A126C]";
}
