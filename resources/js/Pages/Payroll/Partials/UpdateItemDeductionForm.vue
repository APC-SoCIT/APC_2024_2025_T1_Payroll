<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useConfirm } from '@/Utils/Confirm.js';
import { useForm, usePage, Link } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps([
    'targetAccount',
    'itemDeduction',
    'cutoff',
    'deductionView',
]);


const periodHasEnded = props.cutoff.end_date < page.props.date;
const disabled = periodHasEnded || props.itemDeduction.deduction.calculated;
const deleteable = !periodHasEnded && !props.itemDeduction.deduction.required;

const form = useForm({
    amount: props.itemDeduction.amount,
    hours: props.itemDeduction.hours,
    minutes: props.itemDeduction.minutes,
    remaining_payments: props.itemDeduction.remaining_payments,
});
</script>

<template>
    <section class="grid grid-cols-2 py-4">
        <header v-if="! deductionView" class="col-span-1">
            <h2 class="text-lg font-medium text-gray-900">{{ itemDeduction.deduction.name }}</h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ itemDeduction.deduction.description }}
            </p>
        </header>
        <header v-else class="col-span-1">
            <h2 class="text-lg font-medium text-gray-900">{{ targetAccount.name }}</h2>
        </header>

        <div class="relative left-80 bottom-7">
            <form @submit.prevent="form.patch(route('itemDeduction.update', itemDeduction.id), { preserveScroll: true })" class="mt-6 space-y-6">
                <div v-if="itemDeduction.deduction.hour_based">
                    <InputLabel class="text-end relative right-16" for="hours" value="Hours" />
                    <TextInput
                        id="hours"
                        type="number"
                        step="1"
                        class="text-end mt-1 block w-60 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        v-model="form.hours"
                        required
                        :disabled
                    />
                    <InputError class="mt-2" :message="form.errors.hours" />
                </div>

                <div v-if="itemDeduction.deduction.hour_based">
                    <InputLabel class="text-end relative right-16" for="minutes" value="Minutes" />
                    <TextInput
                        id="minutes"
                        type="number"
                        step="1"
                        class="text-end mt-1 block w-60 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        v-model="form.minutes"
                        required
                        :disabled
                    />
                    <InputError class="mt-2" :message="form.errors.minutes" />
                </div>

                <div>
                    <div v-if="!itemDeduction.deduction.calculated && !itemDeduction.deduction.hour_based">
                        <InputLabel class="text-end relative right-16" for="amount" value="Amount" />

                        <TextInput v-if="!itemDeduction.deduction.calculated && !itemDeduction.deduction.hour_based"
                            id="amount"
                            type="number"
                            step="0.01"
                            class="text-end mt-1 block w-60 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            v-model="form.amount"
                            required
                            autocomplete="amount"
                            :disabled
                        />
                    </div>
                    <!-- TextInput doesn't update on partial reloads -->
                    <div v-else
                            type="number"
                            step="0.01"
                            class="text-end relative right-16 mt-1 font-semibold"
                            required
                            disabled>
                        <InputLabel for="amount" value="Amount" />
                        <p>₱ {{ itemDeduction.amount }}</p>
                        <InputError class="mt-2" :message="form.errors.amount" />
                    </div>
                </div>

                <div v-if="itemDeduction.deduction.has_deadline">
                <InputLabel class="text-end relative right-16" for="remaining_payments" value="Remaining Payments" />
                <TextInput
                    id="remaining_payments"
                    type="number"
                    step="1"
                    class="text-end mt-1 block w-60 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    v-model="form.remaining_payments"
                    required
                    min=0
                    :disabled
                />
                <InputError class="mt-2" :message="form.errors.remaining_payments" />
                </div>

                <div v-if="!disabled" class="flex items-center gap-2 relative left-20">
                    <Link
                        v-if="deleteable"
                        :href="route('itemDeduction.delete', itemDeduction.id)"
                        method="delete"
                        :onBefore="useConfirm(`Are you sure you want to delete ${itemDeduction.deduction.name}? This action cannot be undone.`)"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        as="button"
                    >
                        Remove
                    </Link>
                    <div
                        v-else
                        class="inline-flex items-center px-4 py-2 transparent border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest"
                    >
                        ---------
                    </div>
                    <PrimaryButton :disabled="form.processing">Save</PrimaryButton>
                </div>
                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600 text-end relative right-12 bottom-5">Saved.</p>
                </Transition>
            </form>
        </div>
    </section>
</template>
