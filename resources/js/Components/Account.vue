<script setup>
import { Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps([
    'account',
    'cutoff',
]);
</script>

<template>
    <tbody>
        <tr >
            <td class="p-4 border-b border-blue-gray-50"></td>
            <td class="p-4 border-b border-blue-gray-50">
                <p v-if="(! $page.props.auth.isPayroll)" >{{ account.name }}</p>
                <Link v-else-if="cutoff == null" class="font-semibold underline text-gray-500 hover:text-gray-700derline" :href="route('cutoffs.getFromUser', account.id)">{{ account.name }}</Link>
                <Link v-else class="font-semibold underline text-gray-500 hover:text-gray-700" :href="route('payroll.get', { cutoff: cutoff.id, user: account.id })">{{ account.name }}</Link>
            </td>
            <td class="text-start p-4 border-b border-blue-gray-50">{{ account.email }}</td>
            <td v-if="account.active" class="font-semibold p-4 border-b border-blue-gray-50 text-center text-green-600">Active</td>
            <td v-else class="font-semibold p-4 border-b border-blue-gray-50 text-center text-red-600">Inactive</td>
            <td v-if="cutoff == null" class="p-4 border-b border-blue-gray-50 text-center">
                <PrimaryButton>
                    <Link :href="route('account.get', account.id)">Edit Account</Link>
                </PrimaryButton>
            </td>
        </tr>
    </tbody>
</template>
