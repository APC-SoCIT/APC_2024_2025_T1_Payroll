<script setup lang="ts">
import AdditionSelector from '@/Components/AdditionSelector.vue';
import DeductionSelector from '@/Components/DeductionSelector.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useFormat } from '@/Utils/FormatDate.js';
import UpdateBasePayForm from './Partials/UpdateBasePayForm.vue';
import UpdateAdditionItemForm from './Partials/UpdateAdditionItemForm.vue';
import UpdateDeductionItemForm from './Partials/UpdateDeductionItemForm.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps([
    'targetAccount',
    'payrollItem',
    'additions',
    'deductions',
]);

const periodHasEnded = props.payrollItem.payroll_period.end_date < page.props.date;
</script>

<template>
    <Head title="Payroll Item" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Payroll Item for
                for <Link :href="route('cutoffs.getFromUser', targetAccount.id)">{{ targetAccount.name }}</Link>
                for <Link :href="route('accounts.getFromCutoff', payrollItem.payroll_period.id)">{{ useFormat(payrollItem.payroll_period.end_date) }}</Link>
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Additions</h2>
                    <AdditionSelector
                        v-if="!periodHasEnded"
                        :targetAccount
                        :payrollItem
                        :additions
                    />
                    <div v-for="additionItem in payrollItem.addition_items">
                        <UpdateBasePayForm
                            v-if="additionItem.addition.id == 1"
                            :targetAccount
                            :additionItem
                            :payrollPeriod="payrollItem.payroll_period"
                            class="max-w-xl"
                        />
                        <UpdateAdditionItemForm
                            v-else
                            :targetAccount
                            :additionItem
                            :payrollPeriod="payrollItem.payroll_period"
                            class="max-w-xl"
                        />
                    </div>
                </div>
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Deductions</h2>
                    <DeductionSelector
                        v-if="!periodHasEnded"
                        :targetAccount
                        :payrollItem
                        :deductions
                    />
                    <div v-for="deductionItem in payrollItem.deduction_items">
                        <UpdateDeductionItemForm
                            :key="deductionItem.id"
                            :targetAccount
                            :deductionItem
                            :payrollPeriod="payrollItem.payroll_period"
                            class="max-w-xl"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
