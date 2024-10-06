<script setup lang="ts">
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UpdateAdditionItemForm from './Partials/UpdateAdditionItemForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps([
    'targetAccount',
    'payrollItem',
    'additions',
    'deductions',
]);
</script>

<template>
    <Head title="Payroll Item" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Payroll Item for {{ targetAccount.name }} for {{ payrollItem.payroll_period.start_date }} to {{ payrollItem.payroll_period.end_date }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Additions</h2>
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <!-- Settings Dropdown -->
                        <div class="ms-3 relative">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <span class="inline-flex rounded-md">
                                        <button
                                            type="button"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                                        >
                                            Add Additions
                                            <svg
                                                class="ms-2 -me-0.5 h-4 w-4"
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </button>
                                    </span>
                                </template>

                                <template #content>
                                    <!-- <DropdownLink :href="route('profile.edit')"> Profile </DropdownLink> -->
                                    <DropdownLink v-for="addition in additions"
                                        :href="route('payroll.addAdditionItem', { payrollItem: payrollItem.id, addition: addition.id })" method="post" as="button">
                                        {{ addition.name }}
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                    <UpdateAdditionItemForm v-for="addition_item in payrollItem.addition_items"
                        :key="addition_item.id"
                        :targetAccount="targetAccount"
                        :additionItem="addition_item"
                        class="max-w-xl"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
