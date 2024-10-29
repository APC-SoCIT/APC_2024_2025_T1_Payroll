<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useFormat } from '@/Utils/FormatDate.js';
import { Link } from '@inertiajs/vue3';

const props = defineProps([
    'cutoff',
    'account',
]);
</script>

<template>
    <tr>
        <td class="p-4 border-b border-blue-gray-50">
            <Link v-if="account == null"
                class="text-gray-500 hover:text-gray-700 hover:underline"
                :href="route('accounts.getFromCutoff', cutoff.id)"
            >
                {{ useFormat(cutoff.end_date) }}
            </Link>
            <Link v-else
                class="text-gray-500 hover:text-gray-700 hover:underline"
                :href="route('payroll.get', { cutoff: cutoff.id, user: account.id })"
            >
                {{ useFormat(cutoff.end_date) }}
            </Link>
        </td>
        <td class="p-4 border-b border-blue-gray-50">
            {{ useFormat(cutoff.start_date) }}
        </td>
        <td class="p-4 border-b border-blue-gray-50">
            {{ useFormat(cutoff.cutoff_date) }}
        </td>
        <td v-if="cutoff.end_date < $page.props.date" class="p-4 border-b border-blue-gray-50 text-red-600">
            Completed
        </td>
        <td v-else-if="cutoff.start_date > $page.props.date" class="p-4 border-b border-blue-gray-50 text-yellow-600">
            Not Started
        </td>
        <td v-else class="p-4 border-b border-blue-gray-50 text-green-600">
            In Progress
        </td>
        <td v-if="account == null" class="p-4 border-b border-blue-gray-50">
            <PrimaryButton
                v-if="cutoff.end_date >= $page.props.date"
            ><Link :href="route('cutoff.get', cutoff.id)">Reschedule</Link></PrimaryButton>
        </td>
    </tr>
</template>
