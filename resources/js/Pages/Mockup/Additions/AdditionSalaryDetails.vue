<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
</script>


<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Salary Additions Details</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="border flex flex-col w-full h-full text-gray-700 bg-white shadow-md rounded-xl bg-clip-border space-y-4">
                    <div class="m-8">
                        <!-- Name -->
                        <div class="my-4">
                            <header>
                                <h2 class="text-lg font-medium text-gray-900">Name</h2>

                                <p class="mt-1 text-sm text-gray-600">
                                    ????? (what to put here)
                                </p>
                            </header>
                            <form>
                                <div class="space-x-4 static">
                                    <div>
                                        <InputLabel></InputLabel>
                                        <TextInput class="w-full"></TextInput>
                                    </div>
                                </div>

                                <!-- Save button -->
                                <div class="flex items-center gap-4">
                                    <PrimaryButton class="my-4">Save</PrimaryButton>
                                </div>
                            </form>
                        </div>

                        <!-- Description -->
                        <div class="my-4">
                            <header>
                                <h2 class="text-lg font-medium text-gray-900">Description</h2>
                                <p class="mt-1 text-sm text-gray-600">
                                    ????? (what to put here)
                                </p>
                            </header>
                            <form>
                                <div class="space-x-4 static">
                                    <div>
                                        <InputLabel></InputLabel>
                                        <TextInput class="w-full"></TextInput>
                                    </div>
                                </div>

                                <!-- Save button -->
                                <div class="flex items-center gap-4">
                                    <PrimaryButton class="my-4">Save</PrimaryButton>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>