<script setup>
import AdditionSelector from '@/Components/AdditionSelector.vue';
import DeductionSelector from '@/Components/DeductionSelector.vue';
import UpdateItemAdditionForm from './UpdateItemAdditionForm.vue';
import UpdateItemDeductionForm from './UpdateItemDeductionForm.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps([
    'targetAccount',
    'payrollItem',
    'additions',
    'deductions',
]);

const periodHasEnded = props.payrollItem.cutoff.end_date < page.props.date;
const existingAdditions = props.payrollItem.item_additions.map(a => a.addition.id);
const missingAdditions = props.additions.filter(a => !existingAdditions.includes(a.id));

const existingDeductions = props.payrollItem.item_deductions.map(a => a.deduction.id);
const missingDeductions = props.deductions.filter(a => !existingDeductions.includes(a.id));

const salary = props.payrollItem.item_additions.find(a => a.addition_id == 1).amount;
const netPayPolicyViolation = (props.payrollItem.amount / salary) < 0.3;
</script>

<!-- Addiions and Deductions (UpdateAccountInformationForm) -->

<template>
    <div v-if="netPayPolicyViolation"
        class="p-4 sm:p-8 text-red-900 bg-red-300 shadow sm:rounded-lg">
        Take home pay is less than 30% of salary.
    </div>
    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Additions</h2>
        <AdditionSelector class="relative left-28 bottom-9"
            v-if="!periodHasEnded && missingAdditions.length > 0"
            :targetAccount
            :payrollItem
            :additions="missingAdditions"
        />
        <div v-for="itemAddition in payrollItem.item_additions">
            <UpdateItemAdditionForm
                :targetAccount
                :itemAddition
                :cutoff="payrollItem.cutoff"
                class="max-w-xl"
            />
        </div>
    </div>
    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Deductions</h2>
        <DeductionSelector class="relative left-32 bottom-9"
            v-if="!periodHasEnded && missingDeductions.length > 0"
            :targetAccount
            :payrollItem
            :deductions="missingDeductions"
        />
        <div v-for="itemDeduction in payrollItem.item_deductions">
            <UpdateItemDeductionForm
                :key="itemDeduction.id"
                :targetAccount
                :itemDeduction
                :cutoff="payrollItem.cutoff"
                class="max-w-xl"
            />
        </div>
    </div>
</template>
