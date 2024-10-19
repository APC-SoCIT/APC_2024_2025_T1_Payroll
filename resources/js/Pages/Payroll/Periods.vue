<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Period from '@/Components/Period.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps([
    'cutoffs',
]);
</script>

<template>
    <Head title="Payroll Cutoffs"/>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Payroll Cutoffs</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="border relative flex flex-col w-full h-full text-gray-700 bg-white shadow-md rounded-xl bg-clip-border">
                        <table class="ml-8 w-full text-left table-auto min-w-max">
                            <thead class="text-start">
                                <tr class="bg-light">
                                    <th scope="col" width="25%" class="p-4 border-b border-blue-gray-100 bg-blue-gray-50">Cutoff</th>
                                    <th scope="col" width="25%" class="p-4 border-b border-blue-gray-100 bg-blue-gray-50">Start</th>
                                    <th scope="col" width="25%" class="p-4 border-b border-blue-gray-100 bg-blue-gray-50">End</th>
                                    <th scope="col" width="25%" class="p-4 border-b border-blue-gray-100 bg-blue-gray-50">Actions</th>
                                </tr>
                            </thead>
                            <Period v-for="cutoff in cutoffs" :cutoff/>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
