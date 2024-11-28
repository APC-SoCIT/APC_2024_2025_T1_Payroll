<script setup>
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

defineProps([
    'targetAccount',
    'roles',
]);
</script>

<template>
    <div class="hidden sm:flex sm:items-center">
        <!-- Settings Dropdown -->
        <div class="relative">
            <Dropdown align="left" width="48">
                <template #trigger>
                    <span class="inline-flex rounded-md">
                        <div class="mt-2">
                            <button type="button"
                                class="inline-flex items-center px-3 py-2 border text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                Add Roles
                                <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </span>
                </template>

                <template #content>
                    <DropdownLink v-for="role in roles"
                        :href="route('role.add', { user: targetAccount.id, role: role.id })" method="post" as="button">
                        {{ role.name }}
                    </DropdownLink>
                </template>
            </Dropdown>
        </div>
    </div>
</template>
