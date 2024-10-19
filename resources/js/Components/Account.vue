<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps([
    'account',
    'cutoff',
]);
</script>

<template>
    <tbody>
        <tr>
            <td class="p-4 border-b border-blue-gray-50">
                <Link v-if="cutoff == null" :href="route('cutoffs.getFromUser', account.id)">{{ account.name }}</Link>
                <Link v-if="cutoff != null" :href="route('payroll.getItem', { cutoff: cutoff.id, user: account.id })">{{ account.name }}</Link>
            </td>
            <td class="p-4 border-b border-blue-gray-50">{{ account.email }}</td>
            <td class="p-4 border-b border-blue-gray-50 text-center">{{ account.active ? "Active": "Inactive" }}</td>
            <td class="p-4 border-b border-blue-gray-50 text-center">
                <PrimaryButton><Link :href="route('profile.edit', account.id)">Edit Account</Link></PrimaryButton>
                <PrimaryButton><Link :href="route('payroll.getCurrentItemFromUser', account.id)">Current Entry</Link></PrimaryButton>
            </td>
        </tr>
    </tbody>
</template>
