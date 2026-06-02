<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-brand-50 px-4 pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/">
                <ApplicationLogo class="h-20 w-20 fill-current text-brand-700" />
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden rounded-2xl border border-line bg-white px-6 py-4 shadow-card sm:max-w-md"
        >
            <slot />
        </div>
    </div>
</template>
