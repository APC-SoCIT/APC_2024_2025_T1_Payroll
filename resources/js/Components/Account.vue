<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps([
    'account',
]);
</script>

<template>
    <tbody>
        <tr>
            <td>{{ account.name }}</td>
            <td>{{ account.email }}</td>
            <td class="text-center">{{ account.active ? "Active": "Inactive" }}</td>
            <td class="text-center">
                <PrimaryButton><Link :href="route('profile.edit', { id: account.id })">Edit Account</Link></PrimaryButton>
            </td>
        </tr>
    </tbody>
</template>
