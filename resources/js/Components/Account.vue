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
        <tr v-if="cutoff == null">
            <td class="p-4 border-b border-blue-gray-50">
                <Link :href="route('cutoffs.getFromUser', account.id)">{{ account.name }}</Link>
            </td>
            <td class="p-4 border-b border-blue-gray-50">{{ account.email }}</td>
            <td class="p-4 border-b border-blue-gray-50 text-center">{{ account.active ? "Active": "Inactive" }}</td>
            <td class="p-4 border-b border-blue-gray-50 text-center">
                <PrimaryButton><Link :href="route('account.updateForm', account.id)">Edit Account</Link></PrimaryButton>
                <PrimaryButton><Link :href="route('payroll.getCurrentFromUser', account.id)">Current Entry</Link></PrimaryButton>
            </td>
        </tr>
        <tr v-else>
            <td class="p-4 border-b border-blue-gray-50">
                <Link :href="route('payroll.get', { cutoff: cutoff.id, user: account.id })">{{ account.name }}</Link>
            </td>
            <td class="p-4 border-b border-blue-gray-50">{{ account.email }}</td>
            <td class="p-4 border-b border-blue-gray-50 text-center">{{ account.active ? "Active": "Inactive" }}</td>
        </tr>
    </tbody>
</template>
