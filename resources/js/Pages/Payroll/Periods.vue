<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Period from '@/Components/Period.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps([
    'cutoffs',
    'account',
]);
</script>

<template>
    <Head title="Payroll Cutoffs"/>
    <AuthenticatedLayout>
        <template #header>
            <h2 v-if="account == null" class="font-semibold text-xl text-gray-800 leading-tight">Payroll Cutoffs</h2>
            <h2 v-else class="font-semibold text-xl text-gray-800 leading-tight">
                Payroll Cutoffs for
                <Link class="underline hover:text-gray-600" :href="route('account.get', account.id)">{{ account.name }}</Link>
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div v-if="account == null" class="text-end">
                        <PrimaryButton class="mr-10 m-5 rounded-md bg-slate-800 py-2 px-4 border border-transparent text-center text-sm text-white transition-all shadow-md hover:shadow-lg focus:bg-slate-700 focus:shadow-none active:bg-slate-700 hover:bg-slate-700 active:shadow-none disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none ml-2" type="button">
                            <Link :href="route('cutoff.newForm')">Add cutoff</Link>
                        </PrimaryButton>
                    </div>

                    <div class="border relative flex flex-col w-full h-full text-gray-700 bg-white shadow-md rounded-xl bg-clip-border">
                        <table class="ml-8 w-full text-left table-auto min-w-max">
                            <thead class="text-start">
                                <tr class="bg-light">
                                    <th scope="col" width="25%" class="p-4 border-b border-blue-gray-100 bg-blue-gray-50">Release/End</th>
                                    <th scope="col" width="25%" class="p-4 border-b border-blue-gray-100 bg-blue-gray-50">Start</th>
                                    <th scope="col" width="25%" class="p-4 border-b border-blue-gray-100 bg-blue-gray-50">Cutoff</th>
                                    <th v-if="account == null" scope="col" width="25%" class="p-4 border-b border-blue-gray-100 bg-blue-gray-50">Actions</th>
                                    <th v-else scope="col" width="25%" class="p-4 border-b border-blue-gray-100 bg-blue-gray-50">Status</th>
                                </tr>
                            </thead>
                            <Period v-for="cutoff in cutoffs" :cutoff :account/>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
