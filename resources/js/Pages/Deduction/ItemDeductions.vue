<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UpdateItemDeductionForm from '@/Pages/Payroll/Partials/UpdateItemDeductionForm.vue';
import { useFormat } from '@/Utils/FormatDate.js';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps([
    'deduction',
    'cutoff',
    'itemDeductions',
]);
</script>

<template>
    <Head :title="deduction.name + ' Deductions'" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ deduction.name }} Deductions for {{ useFormat(cutoff.end_date) }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Accounts with {{ deduction.name }} Deduction</h2>
                    <div v-for="itemDeduction in itemDeductions">
                        <UpdateItemDeductionForm
                            :itemDeduction
                            :cutoff
                            :deductionView="true"
                            :targetAccount="itemDeduction.payroll_item.user"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
