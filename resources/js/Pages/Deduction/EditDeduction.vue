<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UpdateItemDeductionForm from '@/Pages/Payroll/Partials/UpdateItemDeductionForm.vue';
import { useFormat } from '@/Utils/FormatDate.js';
import { useForm, Head, Link } from '@inertiajs/vue3';

const props = defineProps([ 'deduction' ]);

const form = useForm({
    name: props.deduction.name,
    description: props.deduction.description,
    taxable: props.deduction.taxable == true,
    has_deadline: props.deduction.has_deadline == true,
    hr_access: props.deduction.hr_access == true,
});
</script>

<template>
    <Head title="Editing Deduction Type" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editing Deduction Type
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editing Deduction Type {{ deduction.name }}</h2>
                    <form @submit.prevent="form.patch(route('deduction.update', deduction.id))">
                        <div class="py-3">
                            <InputLabel for="name" value="Name" />
                            <TextInput
                                id="name"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.name"
                                required
                                autofocus
                            />

                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>
                        <div class="py-2">
                            <InputLabel for="description" value="Description" />
                            <TextInput
                                id="description"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.description"
                                required
                            />

                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>
                        <div class="block mt-4">
                            <label class="flex items-center">
                                <Checkbox name="active" v-model:checked="form.taxable" />
                                <span class="ms-2 text-sm text-gray-600">Taxable</span>
                            </label>
                        </div>
                        <div class="block mt-4">
                            <label class="flex items-center">
                                <Checkbox name="active" v-model:checked="form.has_deadline" />
                                <span class="ms-2 text-sm text-gray-600">Has Deadline (Loan)</span>
                            </label>
                        </div>
                        <div class="block mt-4">
                            <label class="flex items-center">
                                <Checkbox name="active" v-model:checked="form.hr_access" />
                                <span class="ms-2 text-sm text-gray-600">HR can set account values</span>
                            </label>
                        </div>
                        <div class="flex items-center gap-4 py-5">
                            <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                            <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                                <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
                            </Transition>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
