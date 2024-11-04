<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import UpdateAccountInformationForm from './Partials/UpdateAccountInformationForm.vue';
import AdditionsAndDeductions from '@/Pages/Payroll/Partials/AdditionsAndDeductions.vue';
import RoleSelector from '@/Components/RoleSelector.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps([
    'targetAccount',
    'payrollItem',
    'additions',
    'deductions',
    'roles',
]);
</script>

<template>
    <Head title="Account" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ targetAccount.name }} Account Details</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div v-if="($page.props.auth.isPayroll || $page.props.auth.isHr)" class="p-4 sm:p-8 space-x-4 bg-white shadow sm:rounded-lg">
                    <SecondaryButton v-if="targetAccount.active == true"><Link :href="route('payroll.getCurrentFromUser', targetAccount.id)">View entry in current cutoff</Link></SecondaryButton>
                    <SecondaryButton><Link :href="route('cutoffs.getFromUser', targetAccount.id)">View all involved cutoffs</Link></SecondaryButton>
                </div>
            </div>
            <div :class="($page.props.auth.isPayroll || $page.props.auth.isHr) ? 'p-4 sm:p-8' : ''" class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <UpdateAccountInformationForm
                        :targetAccount="targetAccount"
                        class="max-w-xl"
                    />

                    <section v-if="$page.props.auth.isAdmin">
                        <h2 class="text-lg font-medium text-gray-900">Roles</h2>

                        <p class="mt-1 text-sm text-gray-600">
                            Account permissions
                        </p>
                        <RoleSelector
                            :targetAccount
                            :roles
                        />

                        <div v-for="user_role in targetAccount.user_roles">
                            <h3 class="text-m font-heavy text-gray-900">{{ user_role.role.name }}</h3>
                            <p class="text-sm font-medium text-gray-500">{{ user_role.role.description }}</p>
                            <Link
                                :href="route('role.remove', user_role.id)"
                                method="delete"
                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                as="button"
                            >
                                Remove
                            </Link>
                        </div>
                    </section>
                </div>
            </div>
            <div :class="($page.props.auth.isPayroll || $page.props.auth.isHr) ? '' : 'p-4 sm:p-8'" class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <AdditionsAndDeductions v-if="targetAccount.active == true"
                    :targetAccount
                    :payrollItem
                    :additions
                    :deductions
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
