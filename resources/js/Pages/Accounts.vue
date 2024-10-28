<script setup lang="ts">
import Account from '@/Components/Account.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useFormat } from '@/Utils/FormatDate.js';
import { useForm, Head, Link } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import FilterAccounts from '@/Components/FilterAccounts.vue';

const props = defineProps([
    'accounts',
    'cutoff',
]);
</script>

<template>
    <Head title="Accounts" />

    <AuthenticatedLayout>
        <template #header>
            <h2 v-if="cutoff == null" class="font-semibold text-xl text-gray-800 leading-tight">Accounts</h2>
            <h2 v-else class="font-semibold text-xl text-gray-800 leading-tight">
                Accounts in Cutoff for
                <Link class="text-gray-500 hover:text-gray-700 hover:underline" :href="route('cutoff.update', cutoff.id)">{{ useFormat(cutoff.end_date) }}</Link>
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="cutoff == null" class="columns-2 my-10">
                    <div class="columns-2">
                        <div>
                            <TextInput>

                            </TextInput>  
                            </div>      
                        <div>
                            <FilterAccounts>
                                
                            </FilterAccounts>
                        </div>
                    </div>
                    <div class="text-end">
                        <PrimaryButton class="mr-10 rounded-md bg-slate-800 py-2 px-4 border border-transparent text-center text-sm text-white transition-all shadow-md hover:shadow-lg focus:bg-slate-700 focus:shadow-none active:bg-slate-700 hover:bg-slate-700 active:shadow-none disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none ml-2" type="button">
                        <Link :href="route('account.new')">Add account</Link>
                        </PrimaryButton>
                    </div>        
                </div>

                <div class="border flex flex-col w-full h-full text-gray-700 bg-white shadow-md rounded-xl bg-clip-border">
                    <table class="ml-8 w-full text-left table-auto min-w-max">
                        <thead class="text-start">
                            <tr v-if="cutoff == null" class="bg-light">
                                <th scope="col" width="30%" class="p-4 border-b border-blue-gray-100 bg-blue-gray-50">Name</th>
                                <th scope="col" width="20%" class="p-4 border-b border-blue-gray-100 bg-blue-gray-50">Email</th>
                                <th scope="col" width="20%" class="p-4 border-b border-blue-gray-100 bg-blue-gray-50">Status</th>
                                <th scope="col" width="30%" class="p-4 border-b border-blue-gray-100 bg-blue-gray-50">Action</th>
                            </tr>
                            <tr v-else class="bg-light">
                                <th scope="col" width="30%" class="p-4 border-b border-blue-gray-100 bg-blue-gray-50">Name</th>
                                <th scope="col" width="30%" class="p-4 border-b border-blue-gray-100 bg-blue-gray-50">Email</th>
                                <th scope="col" width="30%" class="p-4 border-b border-blue-gray-100 bg-blue-gray-50">Status</th>
                            </tr>
                        </thead>
                        <Account v-for="account in accounts" :account :cutoff/>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
