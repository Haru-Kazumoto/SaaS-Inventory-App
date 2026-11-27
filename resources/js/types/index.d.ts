export interface User {
    id: number;
    username: string;
    fullname: string;
    phone: string;
    profile_image_url?: string;
    role_id: number;
    major_id: number;
    role: {
        id: number;
        name: string;
        code: string;
    },
    major: {
        id: number;
        name: string;
        code: string;
    }
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    },
    flash: {
        success: string;
        error: string;
    };
};
