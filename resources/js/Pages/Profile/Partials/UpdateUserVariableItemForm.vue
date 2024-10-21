<script setup lang="ts">
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useConfirm } from '@/Utils/Confirm.js';
import { useForm, usePage, Link } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps([
    'targetAccount',
    'userVariableItem'
]);

const form = useForm({
    value: props.userVariableItem.value,
});
</script>

<template>
    <section v-if="userVariableItem.user_variable.id != 1 || $page.props.auth.isPayroll">
        <header>
            <h2 class="text-m font-medium text-gray-900">{{ userVariableItem.user_variable.name }}</h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ userVariableItem.user_variable.description }}
            </p>
        </header>

        <form @submit.prevent="form.patch(route('userVariableItem.update', userVariableItem.id))" class="mt-6 space-y-6">
            <div>
                <InputLabel for="value" value="Value" />

                <TextInput
                    id="value"
                    type="number"
                    class="mt-1 block w-full"
                    v-model="form.value"
                    min="0"
                    required
                    autofocus
                    autocomplete="value"
                />

                <InputError class="mt-2" :message="form.errors.value" />
            </div>
            <div class="flex items-center gap-4">
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
                v-if="userVariableItem.user_variable.calculated"
                :href="route('userVariableItem.delete', userVariableItem.id)"
                method="delete"
                :onBefore="useConfirm(`Are you sure you want to remove ${userVariableItem.user_variable.name}? This action cannot be undone.`)"
                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                as="button"
            >
                Remove
            </Link>
        </div>
    </section>
</template>
