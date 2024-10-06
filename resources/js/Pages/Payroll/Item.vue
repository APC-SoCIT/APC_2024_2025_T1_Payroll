<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps([
    'targetAccount',
    'payrollItem',
]);
</script>

<template>
    <Head title="Payroll Item" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Payroll Item for {{ targetAccount.name }} for {{ payrollItem.payroll_period.start_date }} to {{ payrollItem.payroll_period.end_date }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    {{ targetAccount.name }}
                    {{ payrollItem }}
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
