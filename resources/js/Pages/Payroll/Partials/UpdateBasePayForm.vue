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
    value: props.additionItem.addition_variable_items.find(e => e.addition_variable.id == 2).value,
    hours: props.additionItem.addition_variable_items.find(e => e.addition_variable.id == 2),
    rate: props.additionItem.addition_variable_items.find(e => e.addition_variable.id == 1),
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

        <form @submit.prevent="form.patch(route('additionVariableItem.update', form.hours.id))" class="mt-6 space-y-6">
            <div>
                <InputLabel for="value" :value="form.hours.addition_variable.name" />

                <TextInput
                    id="value"
                    type="number"
                    class="mt-1 block w-full"
                    v-model="form.value"
                    required
                    autofocus
                    autocomplete="value"
                />

                <InputError class="mt-2" :message="form.errors.value" />
            </div>
            <div>
                <InputLabel :value="form.rate.addition_variable.name" />

                <input
                    type="number"
                    class="mt-1 block w-full"
                    :value="form.rate.value"
                    disabled
                />
            </div>
            <div>
                <InputLabel :value="props.additionItem.addition.name" />

                <input
                    type="number"
                    class="mt-1 block w-full"
                    :value="form.value * form.rate.value"
                    disabled
                />
            </div>
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
