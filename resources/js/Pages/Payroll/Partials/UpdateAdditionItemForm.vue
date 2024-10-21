<script setup lang="ts">
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, usePage, Link } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps([
    'targetAccount',
    'additionItem',
    'payrollPeriod',
]);

const form = useForm({
    amount: props.additionItem.amount,
});

const periodHasEnded = props.payrollPeriod.end_date < page.props.date;
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">{{ additionItem.addition.name }}</h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ additionItem.addition.description }}
            </p>
        </header>

        <form @submit.prevent="form.patch(route('additionItem.update', additionItem.id))" class="mt-6 space-y-6">
            <div>
                <InputLabel for="amount" value="Amount" />

                <TextInput
                    id="amount"
                    type="number"
                    class="mt-1 block w-full"
                    v-model="form.amount"
                    required
                    autofocus
                    :disabled="periodHasEnded"
                    autocomplete="amount"
                />

                <InputError class="mt-2" :message="form.errors.amount" />
            </div>
            <Link
                v-if="!periodHasEnded"
                :href="route('additionItem.delete', additionItem.id)"
                method="delete"
                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                as="button"
            >
                Remove
            </Link>
            <div v-if="!periodHasEnded"
                class="flex items-center gap-4">
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
    </section>
</template>
