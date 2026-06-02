<script setup lang="ts">
defineProps<{
    name: string;
    class?: string;
}>();
</script>

<template>
    <svg :class="$props.class" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <template v-if="name === 'layout-dashboard'">
            <rect x="3" y="3" width="7" height="8" rx="2" />
            <rect x="14" y="3" width="7" height="5" rx="2" />
            <rect x="14" y="12" width="7" height="9" rx="2" />
            <rect x="3" y="15" width="7" height="6" rx="2" />
        </template>
        <template v-else-if="name === 'users'">
            <path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2" />
            <circle cx="9.5" cy="7" r="4" />
            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
        </template>
        <template v-else-if="name === 'user-check'">
            <path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2" />
            <circle cx="9.5" cy="7" r="4" />
            <path d="m16 11 2 2 4-4" />
        </template>
        <template v-else-if="name === 'user-square'">
            <rect x="3" y="3" width="18" height="18" rx="4" />
            <circle cx="12" cy="10" r="3" />
            <path d="M7 18a5 5 0 0 1 10 0" />
        </template>
        <template v-else-if="name === 'graduation-cap'">
            <path d="m22 10-10-5-10 5 10 5 10-5Z" />
            <path d="M6 12v5c3 2 9 2 12 0v-5" />
        </template>
        <template v-else-if="name === 'calendar-check'">
            <path d="M8 2v4" />
            <path d="M16 2v4" />
            <rect x="3" y="4" width="18" height="18" rx="3" />
            <path d="M3 10h18" />
            <path d="m9 16 2 2 4-4" />
        </template>
        <template v-else-if="name === 'shield-alert'">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
            <path d="M12 8v5" />
            <path d="M12 17h.01" />
        </template>
        <template v-else-if="name === 'gavel'">
            <path d="m14 13-7 7" />
            <path d="m8 8 8 8" />
            <path d="m11 5 8 8" />
            <path d="m2 22 5-5" />
            <path d="m5 11 6-6" />
            <path d="m13 3 8 8" />
        </template>
        <template v-else-if="name === 'file-text'">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" />
            <path d="M14 2v6h6" />
            <path d="M8 13h8" />
            <path d="M8 17h6" />
        </template>
        <template v-else-if="name === 'database-backup'">
            <ellipse cx="12" cy="5" rx="8" ry="3" />
            <path d="M4 5v6c0 1.66 3.58 3 8 3" />
            <path d="M4 11v6c0 1.66 3.58 3 8 3 1.12 0 2.18-.09 3.14-.26" />
            <path d="M20 5v5" />
            <path d="M18 16h3v3" />
            <path d="m21 16-4 4" />
        </template>
        <template v-else-if="name === 'user-cog'">
            <circle cx="9" cy="7" r="4" />
            <path d="M2 21v-2a4 4 0 0 1 4-4h4" />
            <circle cx="18" cy="17" r="3" />
            <path d="M18 13v1" />
            <path d="M18 20v1" />
            <path d="M14 17h1" />
            <path d="M21 17h1" />
        </template>
        <template v-else-if="name === 'settings'">
            <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.65 1.65 0 0 0 15 19.4a1.65 1.65 0 0 0-1 .6 1.65 1.65 0 0 0-.33 1V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 8.6 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-.6-1 1.65 1.65 0 0 0-1-.33H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 8.6a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 8.6 4.6a1.65 1.65 0 0 0 1-.6 1.65 1.65 0 0 0 .33-1V3a2 2 0 1 1 4 0v.09A1.65 1.65 0 0 0 15 4.6a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 8.6a1.65 1.65 0 0 0 .6 1 1.65 1.65 0 0 0 1 .33H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1.07Z" />
        </template>
        <template v-else-if="name === 'plus'">
            <path d="M12 5v14" />
            <path d="M5 12h14" />
        </template>
        <template v-else-if="name === 'clock'">
            <circle cx="12" cy="12" r="9" />
            <path d="M12 7v5l3 2" />
        </template>
        <template v-else-if="name === 'sun'">
            <circle cx="12" cy="12" r="4" />
            <path d="M12 2v2" />
            <path d="M12 20v2" />
            <path d="m4.93 4.93 1.41 1.41" />
            <path d="m17.66 17.66 1.41 1.41" />
            <path d="M2 12h2" />
            <path d="M20 12h2" />
            <path d="m4.93 19.07 1.41-1.41" />
            <path d="m17.66 6.34 1.41-1.41" />
        </template>
        <template v-else-if="name === 'moon'">
            <path d="M21 14.5A8.5 8.5 0 1 1 9.5 3 7 7 0 0 0 21 14.5Z" />
        </template>
        <template v-else-if="name === 'check-circle'">
            <circle cx="12" cy="12" r="9" />
            <path d="m8 12 2.5 2.5L16 9" />
        </template>
        <template v-else-if="name === 'alert-circle'">
            <circle cx="12" cy="12" r="9" />
            <path d="M12 7v6" />
            <path d="M12 17h.01" />
        </template>
        <template v-else-if="name === 'heart-pulse'">
            <path d="M19.5 12.6 12 20l-7.5-7.4a5 5 0 0 1 7.1-7.1l.4.4.4-.4a5 5 0 0 1 7.1 7.1Z" />
            <path d="M3 12h4l2-3 3 6 2-3h7" />
        </template>
        <template v-else-if="name === 'x-circle'">
            <circle cx="12" cy="12" r="9" />
            <path d="m15 9-6 6" />
            <path d="m9 9 6 6" />
        </template>
        <template v-else-if="name === 'save'">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z" />
            <path d="M17 21v-8H7v8" />
            <path d="M7 3v5h8" />
        </template>
        <template v-else-if="name === 'trash'">
            <path d="M3 6h18" />
            <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
            <path d="M19 6 18 20a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
            <path d="M10 11v6" />
            <path d="M14 11v6" />
        </template>
        <template v-else-if="name === 'arrow-left'">
            <path d="m12 19-7-7 7-7" />
            <path d="M19 12H5" />
        </template>
        <template v-else-if="name === 'search'">
            <circle cx="11" cy="11" r="8" />
            <path d="m21 21-4.3-4.3" />
        </template>
        <template v-else-if="name === 'upload'">
            <path d="M12 3v12" />
            <path d="m7 8 5-5 5 5" />
            <path d="M5 21h14" />
        </template>
        <template v-else-if="name === 'send'">
            <path d="m22 2-7 20-4-9-9-4 20-7Z" />
            <path d="M22 2 11 13" />
        </template>
        <template v-else-if="name === 'clipboard-list'">
            <path d="M9 5h6" />
            <path d="M9 12h6" />
            <path d="M9 16h6" />
            <path d="M5 7h14v14H5z" />
            <path d="M9 3h6v4H9z" />
        </template>
        <template v-else>
            <circle cx="12" cy="12" r="9" />
        </template>
    </svg>
</template>
