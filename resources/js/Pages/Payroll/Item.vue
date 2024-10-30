<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdditionsAndDeductions from './Partials/AdditionsAndDeductions.vue'
import { useConfirm } from '@/Utils/Confirm.js';
import { useFormat } from '@/Utils/FormatDate.js';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps([
    'targetAccount',
    'payrollItem',
    'additions',
    'deductions',
]);
</script>

<template>
    <Head title="Payroll Item" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Payroll Item for
                <Link v-if="$page.props.auth.isAuthorized"
                    class="underline text-gray-500 hover:text-gray-700 hover:underline"
                    :href="route('account.get', targetAccount.id)"
                >
                    {{ targetAccount.name }}
                </Link>
                <span v-else>{{ targetAccount.name }}</span>
                for
                <Link v-if="$page.props.auth.isAuthorized"
                    class="underline text-gray-500 hover:text-gray-700 hover:underline"
                    :href="route('cutoff.get', payrollItem.cutoff.id)"
                >
                    {{ useFormat(payrollItem.cutoff.end_date) }}
                </Link>
                <span v-else>{{ useFormat(payrollItem.cutoff.end_date) }}</span>
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 space-x-4 bg-white shadow sm:rounded-lg">
                    <SecondaryButton>
                        <Link :href="route('cutoffs.getFromUser', targetAccount.id)">
                            View all involved cutoffs
                        </Link>
                    </SecondaryButton>
                    <SecondaryButton v-if="$page.props.auth.isAuthorized">
                        <Link :href="route('accounts.getFromCutoff', payrollItem.cutoff.id)">
                            View all involved accounts
                        </Link>
                    </SecondaryButton>
                    <DangerButton v-if="$page.props.auth.isAuthorized && payrollItem.cutoff.end >= $page.props.date">
                        <Link
                            :href="route('payroll.delete', { cutoff: payrollItem.cutoff.id, user: targetAccount.id })"
                            :onBefore="useConfirm(`Are you sure you want to delete payroll entry for ${targetAccount.name} for ${useFormat(payrollItem.cutoff.end_date)}? This action cannot be undone.`)"
                            as="button"
                            method="delete"
                        >
                            Delete Entry
                        </Link>
                    </DangerButton>
                </div>
            </div>
            <div class="p-4 sm:p-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 space-x-4 bg-white shadow sm:rounded-lg">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Payroll Entry Total: {{ payrollItem.amount }}
                    </h2>
                </div>
            </div>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <AdditionsAndDeductions
                    :targetAccount
                    :payrollItem
                    :additions
                    :deductions
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
