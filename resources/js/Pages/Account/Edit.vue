<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import UpdateAccountInformationForm from './Partials/UpdateAccountInformationForm.vue';
import AdditionsAndDeductions from '@/Pages/Payroll/Partials/AdditionsAndDeductions.vue';
import RoleSelector from '@/Components/RoleSelector.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { TagsInput, TagsInputInput, TagsInputItem, TagsInputItemDelete, TagsInputItemText } from '@/components/ui/tags-input'
import { ref } from 'vue'
import { X } from 'lucide-vue-next'

const modelValue = ref(['Admin', 'Payroll', 'Human Resources'])


const props = defineProps([
    'targetAccount',
    'payrollItem',
    'additions',
    'deductions',
    'roles',
]);

console.log(props.targetAccount)
</script>

<!-- other things in UpdateAccountInformationForm  -->

<template>

    <Head title="Account" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ targetAccount.name }} Account Details</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div v-if="($page.props.auth.isPayroll || $page.props.auth.isHr)"
                    class="p-4 sm:p-8 space-x-4 bg-white shadow sm:rounded-lg">
                    <!-- View entry in current cutoff button -->
                    <SecondaryButton v-if="targetAccount.active == true">
                        <Link :href="route('payroll.getCurrentFromUser', targetAccount.id)">View entry in current cutoff
                        </Link>
                    </SecondaryButton>
                    <!-- View entry in current cutoff button -->
                    <SecondaryButton>
                        <Link :href="route('cutoffs.getFromUser', targetAccount.id)">View all involved cutoffs</Link>
                    </SecondaryButton>
                </div>
            </div>
            <div :class="($page.props.auth.isPayroll || $page.props.auth.isHr) ? 'p-4 sm:p-8' : ''"
                class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <UpdateAccountInformationForm :targetAccount="targetAccount" class="max-w-xl" />
                </div>
            </div>
            <div class="mb-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 space-x-4 bg-white shadow sm:rounded-lg">
                    <!-- Roles -->
                    <div>
                        <section v-if="$page.props.auth.isAdmin">
                            <h2 class="text-lg font-medium text-gray-900">Roles</h2>

                            <p class="mt-1 text-sm text-gray-600">
                                Account permissions
                            </p>
                            <RoleSelector :targetAccount :roles />

                            <div>
                                <div class="mt-4">
                                    <TagsInput v-model="modelValue">
                                        <TagsInputItem v-for="user_role in targetAccount.user_roles" :key="user_role"
                                            :value="user_role?.role.name">
                                            <TagsInputItemText />
                                            <TagsInputItemDelete>
                                                <Link :href="route('role.remove', user_role.id)" method="delete">
                                                <X class="w-4 h-4" />
                                                </Link>
                                            </TagsInputItemDelete>
                                        </TagsInputItem>
                                    </TagsInput>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
            <div :class="($page.props.auth.isPayroll || $page.props.auth.isHr) ? '' : 'p-4 sm:p-8'"
                class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <AdditionsAndDeductions v-if="targetAccount.active == true" :targetAccount :payrollItem :additions
                    :deductions />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
