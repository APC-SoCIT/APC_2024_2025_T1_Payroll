<script setup lang="ts">
const props = defineProps([
    'account'
]);
</script>

<template>
    <div>
        <p>{{ account.name }} ({{ account.email }})</p>
    </div>
</template>
