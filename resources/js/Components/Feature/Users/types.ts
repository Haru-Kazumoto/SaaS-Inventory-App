/** State form pengguna (subset dari useForm Inertia) */
export interface UserFormState {
    username: string;
    fullname: string;
    password: string;
    email: string;
    role: string;
    major: string;
    phone: string;
    avatar: File | null;
    errors?: Record<string, string>;
}
