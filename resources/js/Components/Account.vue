<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

const props = defineProps([
    'account',
]);
</script>

<template>
    <div>
        <Link :href="route('payroll.getCurrentItemFromUser', {id: account.id})"> {{ account.name }} </Link>
        <p>({{ account.email }})</p>

        <Link :href="route('profile.edit', { id: account.id })"> Edit Account </Link>
    </div>
</template>
