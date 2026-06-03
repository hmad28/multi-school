<script setup lang="ts">
import Icon from '@/Components/App/Icon.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const school = computed(() => page.props.school);
const isTenantRoute = computed(() => route().current('tenant.*'));
const isPlatformRoute = computed(() => route().current('platform.*'));
const mobileMenuOpen = ref(false);
const darkMode = ref(false);
const sidebarRef = ref<HTMLElement | null>(null);
const mobileMenuRef = ref<HTMLElement | null>(null);
const sidebarScrollKey = 'ps.sidebar.scrollTop';
const mobileMenuScrollKey = 'ps.mobileMenu.scrollTop';

const dashboardHref = computed<string>(() => {
    if (isTenantRoute.value && school.value?.slug) {
        return route('tenant.dashboard', { tenant: school.value.slug });
    }

    if (isPlatformRoute.value) {
        return route('platform.dashboard');
    }

    return route('dashboard');
});

const profileHref = computed<string>(() => {
    if (isTenantRoute.value && school.value?.slug) {
        return route('tenant.profile.edit', { tenant: school.value.slug });
    }

    return route('profile.edit');
});

const logoutHref = computed<string>(() => {
    if (isTenantRoute.value && school.value?.slug) {
        return route('tenant.logout', { tenant: school.value.slug });
    }

    if (isPlatformRoute.value) {
        return route('platform.logout');
    }

    return route('logout');
});

const tenantRoute = (name: string): string => {
    if (!school.value?.slug) return '#';

    return route(name, { tenant: school.value.slug });
};

const appTitle = computed(() => {
    if (isTenantRoute.value && school.value?.name) return school.value.name;
    if (isPlatformRoute.value) return 'Platform Admin';

    return 'Platform Sekolah';
});

const appSubtitle = computed(() => {
    if (isTenantRoute.value) return 'Administrasi Sekolah';
    if (isPlatformRoute.value) return 'Tenant lifecycle';

    return 'Pusat aplikasi';
});

const rememberScroll = (key: string, element: HTMLElement | null) => {
    if (element) sessionStorage.setItem(key, String(element.scrollTop));
};

const restoreScroll = (key: string, element: HTMLElement | null) => {
    if (!element) return;
    element.scrollTop = Number(sessionStorage.getItem(key) ?? 0);
};

const applyTheme = () => {
    document.documentElement.classList.toggle('dark', darkMode.value);
    localStorage.setItem('theme', darkMode.value ? 'dark' : 'light');
};

const toggleTheme = () => {
    darkMode.value = !darkMode.value;
    applyTheme();
};

onMounted(() => {
    darkMode.value = localStorage.getItem('theme') === 'dark';
    applyTheme();
    nextTick(() => restoreScroll(sidebarScrollKey, sidebarRef.value));
});

onUnmounted(() => rememberScroll(sidebarScrollKey, sidebarRef.value));

watch(mobileMenuOpen, (open) => {
    if (open) nextTick(() => restoreScroll(mobileMenuScrollKey, mobileMenuRef.value));
});

const navGroups = computed(() => [
    {
        label: 'Utama',
        items: [
            {
                label: 'Dashboard',
                shortLabel: 'Home',
                href: dashboardHref.value,
                active: route().current('dashboard') || route().current('tenant.dashboard') || route().current('platform.dashboard'),
                show: true,
                icon: 'layout-dashboard',
            },
            {
                label: 'Kelola Sekolah',
                shortLabel: 'Sekolah',
                href: isPlatformRoute.value ? route('platform.tenants.index') : '#',
                active: route().current('platform.tenants.*'),
                show: isPlatformRoute.value,
                icon: 'graduation-cap',
            },
            {
                label: 'Billing',
                shortLabel: 'Billing',
                href: isPlatformRoute.value ? route('platform.billing.index') : '#',
                active: route().current('platform.billing.*'),
                show: isPlatformRoute.value,
                icon: 'currency-dollar',
            },
        ],
    },
    {
        label: 'Master Data',
        items: [
            { label: 'Data Siswa', shortLabel: 'Siswa', href: tenantRoute('tenant.students.index'), active: route().current('tenant.students.*'), show: isTenantRoute.value, icon: 'users' },
            { label: 'Data Guru', shortLabel: 'Guru', href: tenantRoute('tenant.teachers.index'), active: route().current('tenant.teachers.*'), show: isTenantRoute.value, icon: 'user-square' },
            { label: 'Kelas', shortLabel: 'Kelas', href: tenantRoute('tenant.classes.index'), active: route().current('tenant.classes.*'), show: isTenantRoute.value, icon: 'graduation-cap' },
            { label: 'Akademik', shortLabel: 'Akademik', href: tenantRoute('tenant.academic.index'), active: route().current('tenant.academic.*'), show: isTenantRoute.value, icon: 'calendar-check' },
        ],
    },
    {
        label: 'Operasional',
        items: [
            { label: 'Absensi Siswa', shortLabel: 'Absensi', href: tenantRoute('tenant.attendance.students.index'), active: route().current('tenant.attendance.students.index') || route().current('tenant.attendance.students.recap') || route().current('tenant.attendance.students.qr.*'), show: isTenantRoute.value, icon: 'calendar-check' },
            { label: 'Absensi Guru', shortLabel: 'Guru', href: tenantRoute('tenant.attendance.teachers.index'), active: route().current('tenant.attendance.teachers.*'), show: isTenantRoute.value, icon: 'calendar-check' },
            { label: 'Kalender Akademik', shortLabel: 'Kalender', href: tenantRoute('tenant.academic-calendar.holidays.index'), active: route().current('tenant.academic-calendar.*'), show: isTenantRoute.value, icon: 'calendar-check' },
            { label: 'Pelanggaran', shortLabel: 'Pelanggaran', href: tenantRoute('tenant.violation-types.index'), active: route().current('tenant.violation-types.*') || route().current('tenant.student-violations.*'), show: isTenantRoute.value, icon: 'shield-alert' },
            { label: 'Poin Karakter', shortLabel: 'Karakter', href: tenantRoute('tenant.student-character-points.index'), active: route().current('tenant.student-character-points.*') || route().current('tenant.character-point-types.*'), show: isTenantRoute.value, icon: 'heart-pulse' },
            { label: 'Kirim WhatsApp', shortLabel: 'WA', href: '#', active: false, show: false, icon: 'send' },
            { label: 'Notifikasi', shortLabel: 'Notif', href: '#', active: false, show: false, icon: 'alert-circle' },
        ],
    },
    {
        label: 'Sistem',
        items: [
            { label: 'Laporan', shortLabel: 'Laporan', href: tenantRoute('tenant.reports.index'), active: route().current('tenant.reports.*'), show: isTenantRoute.value, icon: 'file-text' },
            { label: 'Backup', shortLabel: 'Backup', href: '#', active: false, show: false, icon: 'database-backup' },
            { label: 'User', shortLabel: 'User', href: '#', active: false, show: false, icon: 'user-cog' },
            { label: 'Pengaturan', shortLabel: 'Setting', href: '#', active: false, show: false, icon: 'settings' },
        ],
    },
]);

const visibleNavGroups = computed(() =>
    navGroups.value
        .map((group) => ({ ...group, items: group.items.filter((item) => item.show) }))
        .filter((group) => group.items.length > 0),
);

const mobilePrimaryNav = computed(() => visibleNavGroups.value.flatMap((group) => group.items).slice(0, 4));
const activeMobilePage = computed(() => visibleNavGroups.value.flatMap((group) => group.items).find((item) => item.active));
</script>

<template>
    <div class="min-h-screen bg-brand-50 text-ink dark:bg-slate-950 dark:text-slate-100 lg:flex">
        <aside ref="sidebarRef" class="hidden border-r border-line bg-white text-ink lg:sticky lg:top-0 lg:block lg:h-screen lg:w-72 lg:overflow-y-auto dark:border-white/10 dark:bg-[#1A1D20] dark:text-white" @scroll.passive="rememberScroll(sidebarScrollKey, sidebarRef)">
            <div class="px-6 py-6">
                <Link :href="dashboardHref" class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-[#2563EB] to-[#38BDF8] text-lg font-black text-white shadow-lg shadow-black/30">PS</span>
                    <span>
                        <span class="block text-lg font-bold tracking-tight">{{ appTitle }}</span>
                        <span class="block text-xs font-medium text-slate-400">{{ appSubtitle }}</span>
                    </span>
                </Link>
            </div>

            <nav class="space-y-6 px-4 pb-6">
                <div v-for="group in visibleNavGroups" :key="group.label" class="space-y-2">
                    <p class="px-3 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-500">
                        {{ group.label }}
                    </p>
                    <Link
                        v-for="item in group.items"
                        :key="item.label"
                        :href="item.href"
                        preserve-scroll
                        class="flex items-center justify-between rounded-2xl px-3 py-2.5 text-sm font-semibold transition"
                        :class="item.active ? 'bg-brand-700 text-white shadow-sm shadow-blue-200/70 dark:shadow-blue-950/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white'"
                    >
                        <span class="flex items-center gap-3">
                            <Icon :name="item.icon" class="h-5 w-5" />
                            <span>{{ item.label }}</span>
                        </span>
                        <span v-if="item.active" class="h-2 w-2 rounded-full bg-[#38BDF8]"></span>
                    </Link>
                </div>
            </nav>
        </aside>

        <div class="min-w-0 flex-1">
            <header class="mobile-topbar lg:hidden">
                <Link :href="dashboardHref" class="flex min-w-0 items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-[#2563EB] to-[#38BDF8] text-sm font-black text-white shadow-lg shadow-blue-200">PS</span>
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-bold text-slate-900 dark:text-slate-100">{{ appTitle }}</span>
                        <span class="block truncate text-xs text-slate-500 dark:text-slate-400">{{ activeMobilePage?.label ?? 'Dashboard' }}</span>
                    </span>
                </Link>
                <button type="button" class="flex h-11 w-11 items-center justify-center rounded-2xl border border-blue-100 bg-white text-sm font-bold text-[#2563EB] shadow-sm shadow-blue-100/60 dark:border-slate-700 dark:bg-slate-900" aria-label="Buka menu" @click="mobileMenuOpen = true">
                    {{ user?.name?.charAt(0) ?? 'U' }}
                </button>
            </header>

            <header class="sticky top-0 z-20 hidden border-b border-slate-200/80 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-950/90 lg:block">
                <div class="flex min-h-20 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    <div class="min-w-0">
                        <slot name="header" />
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" class="app-button-secondary gap-2" @click="toggleTheme">
                            <Icon :name="darkMode ? 'moon' : 'sun'" class="h-4 w-4" />
                            {{ darkMode ? 'Mode gelap' : 'Mode terang' }}
                        </button>
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button type="button" class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-left shadow-sm transition hover:border-[#38BDF8]/50 hover:bg-[#38BDF8]/10 dark:border-slate-700 dark:bg-slate-900 dark:hover:border-slate-600 dark:hover:bg-slate-800">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#DBEAFE] text-sm font-bold text-[#2563EB]">
                                        {{ user?.name?.charAt(0) ?? 'U' }}
                                    </span>
                                    <span class="hidden sm:block">
                                        <span class="block text-sm font-semibold text-slate-800 dark:text-slate-100">{{ user?.name }}</span>
                                        <span class="block text-xs text-slate-500 dark:text-slate-400">Akun pengguna</span>
                                    </span>
                                </button>
                            </template>
                            <template #content>
                                <DropdownLink :href="profileHref">Profil</DropdownLink>
                                <DropdownLink :href="logoutHref" method="post" as="button">Keluar</DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </header>

            <main class="mobile-shell-pad p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>

        <div v-if="mobileMenuOpen" class="fixed inset-0 z-40 lg:hidden">
            <button type="button" class="absolute inset-0 bg-slate-950/40" aria-label="Tutup menu" @click="mobileMenuOpen = false"></button>
            <div ref="mobileMenuRef" class="absolute inset-x-0 bottom-0 max-h-[82vh] overflow-y-auto rounded-t-[2rem] bg-white p-5 shadow-2xl dark:bg-slate-950" @scroll.passive="rememberScroll(mobileMenuScrollKey, mobileMenuRef)">
                <div class="mx-auto mb-4 h-1.5 w-12 rounded-full bg-slate-200"></div>
                <div class="mb-5 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#2563EB]">Menu</p>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">Navigasi aplikasi</h2>
                    </div>
                    <button type="button" class="rounded-full bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300" @click="mobileMenuOpen = false">Tutup</button>
                </div>

                <div class="space-y-5">
                    <div v-for="group in visibleNavGroups" :key="group.label" class="space-y-2">
                        <p class="px-1 text-xs font-bold uppercase tracking-[0.16em] text-slate-400">{{ group.label }}</p>
                        <Link
                            v-for="item in group.items"
                            :key="item.label"
                            :href="item.href"
                            preserve-scroll
                            class="flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold transition"
                            :class="item.active ? 'bg-brand-700/10 text-brand-700 dark:bg-brand-700/20 dark:text-brand-100' : 'text-slate-700 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800'"
                            @click="mobileMenuOpen = false"
                        >
                            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-brand-100 text-brand-700 dark:bg-[#38BDF8]/15 dark:text-slate-100"><Icon :name="item.icon" class="h-5 w-5" /></span>
                            <span>{{ item.label }}</span>
                        </Link>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3 border-t border-slate-100 pt-5 dark:border-slate-800">
                    <button type="button" class="app-button-secondary" @click="toggleTheme">{{ darkMode ? 'Mode gelap' : 'Mode terang' }}</button>
                    <Link :href="profileHref" class="app-button-secondary" @click="mobileMenuOpen = false">Profil</Link>
                    <Link :href="logoutHref" method="post" as="button" class="app-button-primary col-span-2">Keluar</Link>
                </div>
            </div>
        </div>

        <nav class="mobile-bottom-nav lg:hidden">
            <Link
                v-for="item in mobilePrimaryNav"
                :key="item.label"
                :href="item.href"
                preserve-scroll
                class="mobile-nav-item"
                :class="item.active ? 'text-brand-700 dark:text-brand-100' : 'text-slate-500 dark:text-slate-400'"
            >
                <span class="flex h-8 w-8 items-center justify-center rounded-2xl" :class="item.active ? 'bg-brand-100 dark:bg-brand-700/20' : 'bg-slate-100 dark:bg-slate-800'"><Icon :name="item.icon" class="h-4 w-4" /></span>
                <span>{{ item.shortLabel }}</span>
            </Link>
            <button type="button" class="mobile-nav-item text-slate-500 dark:text-slate-400" aria-label="Buka menu lengkap" @click="mobileMenuOpen = true">
                <span class="flex h-8 w-8 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800"><Icon name="settings" class="h-4 w-4" /></span>
                <span>Menu</span>
            </button>
        </nav>
    </div>
</template>
