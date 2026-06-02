<script setup lang="ts">
defineProps<{ points: number; thresholds: Array<{ points: number; label: string }> }>();
</script>
<template>
    <span v-for="t in thresholds" :key="t.points">
        <span v-if="points >= t.points && (!thresholds.find((x: any) => x.points > t.points) || points < thresholds.filter((x: any) => x.points > t.points).sort((a: any, b: any) => a.points - b.points)[0]?.points)" class="app-badge bg-amber-50 text-amber-700">{{ t.label }}</span>
    </span>
</template>
