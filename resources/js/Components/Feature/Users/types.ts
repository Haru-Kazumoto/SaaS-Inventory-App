/** State form anggota (subset dari useForm Inertia) */
export interface MemberFormState {
    fullname: string;
    username: string;
    email: string;
    phone: string;
    password: string;
    organization_role: "owner" | "admin" | "member";
    memberships: Array<{ unit_id: number | null; role: "admin" | "staff" | "viewer" }>;
    avatar: File | null;
    errors?: Record<string, string>;
}

/** Baris anggota dari MemberQuery */
export interface MemberRow {
    id: number;
    fullname: string;
    username: string;
    email?: string | null;
    phone?: string | null;
    profile_image_url?: string | null;
    organization_role: "owner" | "admin" | "member" | null;
    role_label?: string | null;
    units: Array<{ id: number; name: string; role: string }>;
}
