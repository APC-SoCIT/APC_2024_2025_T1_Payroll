<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import UserVariableSelector from '@/Components/UserVariableSelector.vue'
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import UpdateUserVariableItemForm from './Partials/UpdateUserVariableItemForm.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps([
    'targetAccount',
    'userVariables',
]);

const existingUserVariables = props.targetAccount.user_variable_items.map(a => a.user_variable.id);
const missingUserVariables = props.targetAccount.user_variable_items.filter(a => !existingUserVariables.includes(a.id));
</script>

<template>
    <Head title="Account" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ targetAccount.name }} Account Details</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div v-if="$page.props.auth.isAuthorized" class="p-4 sm:p-8 space-x-4 bg-white shadow sm:rounded-lg">
                    <SecondaryButton v-if="targetAccount.active == true"><Link :href="route('payroll.getCurrentFromUser', targetAccount.id)">View entry in current cutoff</Link></SecondaryButton>
                    <SecondaryButton><Link :href="route('cutoffs.getFromUser', targetAccount.id)">View all involved cutoffs</Link></SecondaryButton>
                </div>
            </div>
            <div :class="$page.props.auth.isAuthorized ? 'p-4 sm:p-8' : ''" class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <UpdateProfileInformationForm
                        :targetAccount="targetAccount"
                        class="max-w-xl"
                    />
                </div>
            </div>
            <div :class="$page.props.auth.isAuthorized ? '' : 'p-4 sm:p-8'" class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900">Account Variables</h3>
                    <UserVariableSelector v-if="missingUserVariables.length > 0"
                        :targetAccount
                        :userVariables="missingUserVariables"
                    />
                    <UpdateUserVariableItemForm v-for="userVariableItem in targetAccount.user_variable_items"
                        :key="userVariableItem.id"
                        :targetAccount
                        :userVariableItem
                        class="max-w-xl"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
