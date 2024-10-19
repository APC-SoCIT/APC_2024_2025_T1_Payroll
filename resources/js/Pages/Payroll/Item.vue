<script setup lang="ts">
import AdditionItemSelector from '@/Components/AdditionItemSelector.vue';
import DeductionItemSelector from '@/Components/DeductionItemSelector.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UpdateAdditionItemForm from './Partials/UpdateAdditionItemForm.vue';
import UpdateDeductionItemForm from './Partials/UpdateDeductionItemForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps([
    'targetAccount',
    'payrollItem',
    'additions',
    'deductions',
]);

function format(dateString) {
    var options = {  year: 'numeric', month: 'long', day: 'numeric' };
    var date  = new Date(dateString);
    return date.toLocaleDateString("en-US", options);
}
</script>

<template>
    <Head title="Payroll Item" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Payroll Item for {{ targetAccount.name }}
                for {{ format(payrollItem.payroll_period.end_date) }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Additions</h2>
                    <AdditionItemSelector
                        :targetAccount
                        :payrollItem
                        :additions
                    />
                    <UpdateAdditionItemForm v-for="additionItem in payrollItem.addition_items"
                        :key="additionItem.id"
                        :targetAccount
                        :additionItem
                        class="max-w-xl"
                    />
                </div>
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Deductions</h2>
                    <DeductionItemSelector
                        :targetAccount
                        :payrollItem
                        :deductions
                    />
                    <UpdateDeductionItemForm v-for="deductionItem in payrollItem.deduction_items"
                        :key="deductionItem.id"
                        :targetAccount
                        :deductionItem
                        class="max-w-xl"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
