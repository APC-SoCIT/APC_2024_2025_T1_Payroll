<script setup lang="ts">
import Account from '@/Components/Account.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, Head } from '@inertiajs/vue3';

const props = defineProps([
    'accounts'
]);

const form = useForm({
    name: '',
    email: '',
});
</script>

<template>
    <Head title="Accounts" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Accounts</h2>
        </template>

        <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
            <form @submit.prevent="form.post(route('profile.store'), { onSuccess: () => form.reset() })">
                <InputError :message="form.errors.name" class="mt-2" />
                <input
                    v-model="form.name"
                    placeholder="Full Name"
                    class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                ></input>
                <InputError :message="form.errors.email" class="mt-2" />
                <input
                    v-model="form.email"
                    placeholder="Email"
                    class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                ></input>
                <PrimaryButton class="mt-4">Add Account</PrimaryButton>
            </form>
        </div>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <Account v-for="account in accounts" :account="account"/>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
