<script setup lang="ts">
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useFormat } from '@/Utils/FormatDate.js';
import { Link } from '@inertiajs/vue3';

const props = defineProps([
    'cutoff',
    'account',
]);

function confirm(prompt){
    return window.confirm(prompt);
}
</script>

<template>
    <tr>
        <td class="p-4 border-b border-blue-gray-50">
            <Link v-if="account == null" :href="route('accounts.getFromCutoff', cutoff.id)">{{ useFormat(cutoff.end_date) }}</Link>
            <Link v-else :href="route('payroll.get', { cutoff: cutoff.id, user: account.id })">{{ useFormat(cutoff.end_date) }}</Link>
        </td>
        <td class="p-4 border-b border-blue-gray-50">
            {{ useFormat(cutoff.start_date) }}
        </td>
        <td class="p-4 border-b border-blue-gray-50">
            {{ useFormat(cutoff.cutoff_date) }}
        </td>
        <td v-if="account == null" class="p-4 border-b border-blue-gray-50">
            <PrimaryButton
                v-if="cutoff.end_date > $page.props.date"
            ><Link :href="route('cutoff.get', cutoff.id)">Reschedule</Link></PrimaryButton>
            <DangerButton v-if="cutoff.end_date > $page.props.date">
                <Link
                    :href="route('cutoff.delete', cutoff.id)"
                    method="delete"
                    as="button"
                    :onBefore="() => confirm(`Are you sure you want to delete the schedule for ${useFormat(new Date(cutoff.end_date))}?`)"
                >Delete</Link>
            </DangerButton>
        </td>
        <td v-else class="p-4 border-b border-blue-gray-50">
            {{ cutoff.end_date < $page.props.date
                ? "Completed"
                : cutoff.start_date > $page.props.date
                    ? "Not Started"
                    : "In Progress" }}
        </td>
    </tr>
</template>
