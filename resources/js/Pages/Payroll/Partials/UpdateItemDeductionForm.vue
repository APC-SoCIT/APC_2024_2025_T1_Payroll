<script setup lang="ts">
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
]);


const periodHasEnded = props.cutoff.end_date < page.props.date;
const disabled = periodHasEnded || props.itemDeduction.deduction.calculated;
const deleteable = !periodHasEnded && !props.itemDeduction.deduction.required;

const form = useForm({
    amount: props.itemDeduction.amount,
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">{{ itemDeduction.deduction.name }}</h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ itemDeduction.deduction.description }}
            </p>
        </header>

        <form @submit.prevent="form.patch(route('itemDeduction.update', itemDeduction.id), { preserveScroll: true })" class="mt-6 space-y-6">
            <div>
                <InputLabel for="amount" value="Amount" />

                <TextInput v-if="!itemDeduction.deduction.calculated"
                    id="amount"
                    type="number"
                    step="0.01"
                    class="mt-1 block w-full"
                    v-model="form.amount"
                    required
                    autocomplete="amount"
                />
                <!-- TextInput doesn't update on partial reloads -->
                <input v-else
                    type="number"
                    step="0.01"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    required
                    :value="itemDeduction.amount"
                    disabled
                />

                <InputError class="mt-2" :message="form.errors.amount" />
            </div>
            <div v-if="!disabled" class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
                </Transition>
            </div>
        </form>
        <div class="py-2">
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
        </div>
    </section>
</template>
