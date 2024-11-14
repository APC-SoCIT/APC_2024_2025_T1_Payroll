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
    'itemAddition',
    'cutoff',
    'additionView',
]);

const periodHasEnded = props.cutoff.end_date < page.props.date;
const disabled = periodHasEnded || props.itemAddition.addition.calculated;
const deleteable = !periodHasEnded && !props.itemAddition.addition.required;

const form = useForm({
    amount: props.itemAddition.amount,
    hours: props.itemAddition.hours,
    minutes: props.itemAddition.minutes,
});
</script>

<template>
    <section class="grid grid-cols-2 py-4">
        <header v-if="! additionView"
            class="col-span-1">
            <h2 class="text-lg font-medium text-gray-900">{{ itemAddition.addition.name }}</h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ itemAddition.addition.description }}
            </p>
        </header>
        <header v-else
            class="col-span-1">
            <h2 class="text-lg font-medium text-gray-900">{{ targetAccount.name }}</h2>
        </header>

        <div class="relative left-80 bottom-5">
            <!--Input that needs saving-->
            <form @submit.prevent="form.patch(route('itemAddition.update', itemAddition.id), { preserveScroll: true })" class="my-3 space-y-2">
                <div v-if="itemAddition.addition.hour_based">
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
                <div v-if="itemAddition.addition.hour_based">
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
                    <InputLabel class="text-end relative right-16" for="amount" value="Amount" />
                    <TextInput v-if="!itemAddition.addition.calculated && !itemAddition.addition.hour_based"
                        id="amount"
                        type="number"
                        step="0.01"
                        class="text-end mt-1 block w-60 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        v-model="form.amount"
                        required
                        :disabled
                    />
                    <!-- TextInput doesn't update on partial reloads -->
                    <p v-else
                        type="number"
                        step="0.01"
                        class="text-end relative right-16 mt-1 font-semibold"
                        required
                        disabled
                    >₱ {{ itemAddition.amount }}</p>
                    <InputError class="mt-2" :message="form.errors.amount" />
                </div>
                <div v-if="!disabled"
                    class="flex items-center gap-2 relative left-20">
                    <Link
                        v-if="deleteable"
                        :href="route('itemAddition.delete', itemAddition.id)"
                        method="delete"
                        :onBefore="useConfirm(`Are you sure you want to delete ${itemAddition.addition.name}? This action cannot be undone.`)"
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
                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
                </Transition>
            </form>
        </div>
    </section>
</template>
