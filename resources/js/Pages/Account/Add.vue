<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { useForm, Head } from '@inertiajs/vue3';


const form = useForm({
    name: '',
    email: '',
});
</script>



<template>
    <Head title="Accounts" />

    <AuthenticatedLayout>
        <template #header>
            <header>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add account</h2>
            </header>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h2 class="text-lg font-medium text-gray-900">Add an employee account</h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Add the employee's account information.
                    </p>
                    <div class="max-w-xl">
                        <form @submit.prevent="form.post(route('account.new'), { onSuccess: () => form.reset() })" class=" mt-6 space-y-6">
                            <div>
                                <InputLabel for="fullName" value="Employee's Full Name" />
                                <InputError :message="form.errors.name" class="mt-2" />
                                <input
                                    v-model="form.name"
                                    placeholder="Full Name"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                                ></input>
                            </div>
                            <div>
                                <InputError :message="form.errors.email" class="mt-2" />
                                <InputLabel for="email" value="Email Address" />
                                <input
                                    v-model="form.email"
                                    placeholder="Email"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                                ></input>
                            </div>
                            <PrimaryButton class="mt-4">Add Account</PrimaryButton>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
