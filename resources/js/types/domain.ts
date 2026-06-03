// Domain types mirroring Inertia props sent from tenant controllers.
// Fields reflect the columns selected in controllers; relations are optional
// because not every page eager-loads them.

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface Paginated<T> {
    data: T[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

export interface AcademicLevel {
    id: string;
    name: string;
    numeric_value: number;
}

export interface Semester {
    id: string;
    academic_year_id: string;
    name: string;
    starts_on: string;
    ends_on: string;
    is_active: boolean;
}

export interface AcademicYear {
    id: string;
    name: string;
    starts_on: string;
    ends_on: string;
    is_active: boolean;
    semesters?: Semester[];
}

export interface Teacher {
    id: string;
    nip: string | null;
    full_name: string;
    position: string;
    phone: string | null;
    status: string;
    can_input_teacher_attendance: boolean;
}

export interface SchoolClass {
    id: string;
    academic_level_id: string;
    name: string;
    display_name: string;
    homeroom_teacher_id: string | null;
    status: string;
    sort_order: number;
    students_count?: number;
    academic_level?: AcademicLevel;
    homeroom_teacher?: Pick<Teacher, 'id' | 'full_name'>;
}

export interface Student {
    id: string;
    name: string;
    nis: string;
    nisn: string | null;
    class_id: string | null;
    gender: string | null;
    guardian_name: string | null;
    guardian_phone: string | null;
    address?: string | null;
    status: string;
    school_class?: Pick<SchoolClass, 'id' | 'academic_level_id' | 'name' | 'display_name'> | null;
}

export interface ClassOption {
    id: string;
    name: string;
}

export interface CatalogType {
    id: string;
    category: string;
    name: string;
    points: number;
    status: string;
    sort_order: number;
}

export interface AttendanceStatus {
    id: string;
    code: string;
    name: string;
}

export interface ViolationThreshold {
    points: number;
    label: string;
}

export interface DateRangeFilters {
    from: string;
    to: string;
}
