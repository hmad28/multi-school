export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

export type School = {
    id: string;
    name: string;
    slug: string;
    status: string;
};

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    appName: string;
    auth: {
        user: User;
        roles: string[];
        permissions: string[];
    };
    school: School | null;
    flash: {
        success?: string;
        error?: string;
    };
};
