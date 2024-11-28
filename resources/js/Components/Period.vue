<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useFormat } from '@/Utils/FormatDate.js';
import { Link } from '@inertiajs/vue3';


const props = defineProps([
    'cutoff',
    'account',
    'accounts'
]);
</script>

<template>
    <tr>
        <td class="p-4 border-b text-gray-500 border-blue-gray-50"></td>
        <td class="font-semibold text-left p-4 border-b text-gray-500 border-blue-gray-50">
            <p v-if="$page.props.date < cutoff.start_date">
                {{ useFormat(cutoff.end_date) }}
            </p>
            <Link v-else-if="! $page.props.auth.isPayroll"
                class="underline hover:text-gray-700 hover:underline"
                :href="route('payroll.get', { cutoff: cutoff.id, user: $page.props.auth.user.id })"
            >
                {{ useFormat(cutoff.end_date) }}
            </Link>
            <Link v-else-if="account == null"
                class="underline hover:text-gray-700 hover:underline"
                :href="route('accounts.getFromCutoff', cutoff.id)"
            >
                {{ useFormat(cutoff.end_date) }}
            </Link>
            <Link v-else
                class="underline hover:text-gray-700 hover:underline"
                :href="route('payroll.get', { cutoff: cutoff.id, user: account.id })"
            >
                {{ useFormat(cutoff.end_date) }}
            </Link>
        </td>
        <td class="text-left p-4 border-b border-blue-gray-50">
            {{ useFormat(cutoff.start_date) }}
        </td>
        <td class="text-left p-4 border-b border-blue-gray-50">
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
        <td v-if="account == null && cutoff.end_date >= $page.props.date && ($page.props.auth.isHr)" class="p-4 border-b border-blue-gray-50">
            <PrimaryButton>
                <Link :href="route('cutoff.get', cutoff.id)">Reschedule</Link>
            </PrimaryButton>
        </td>
        <td v-else class="p-4 border-b border-blue-gray-50 text-green-600">
        </td>
    </tr>
</template>
