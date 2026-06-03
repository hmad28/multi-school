<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

defineProps<{ links: PaginationLink[] }>();
</script>

<template>
    <nav v-if="links && links.length > 3" class="flex flex-wrap items-center justify-center gap-1 py-2" aria-label="Pagination">
        <template v-for="(link, index) in links" :key="index">
            <span
                v-if="link.url === null"
                class="rounded-lg px-3 py-2 text-sm text-slate-400"
                v-html="link.label"
            />
            <Link
                v-else
                :href="link.url"
                class="rounded-lg px-3 py-2 text-sm font-medium transition"
                :class="link.active ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'"
                preserve-scroll
                v-html="link.label"
            />
        </template>
    </nav>
</template>
