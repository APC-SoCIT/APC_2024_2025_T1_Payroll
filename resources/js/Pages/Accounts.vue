<script setup>
import Account from '@/Components/Account.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useFormat } from '@/Utils/FormatDate.js';
import { useForm, Head, Link } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger, } from '@/Components/ui/dropdown-menu'

const props = defineProps([
    'accounts',
    'cutoff',
]);
</script>

<!-- >> Accounts Page << -->

<template>
    <Head title="Accounts" />

    <AuthenticatedLayout>
        <template #header>
            <h2 v-if="cutoff == null" class="font-semibold text-xl text-gray-800 leading-tight">Accounts</h2>
            <h2 v-else class="font-semibold text-xl text-gray-800 leading-tight">
                Accounts in Cutoff for
                <Link class="underline text-gray-500 hover:text-gray-700 hover:underline" :href="route('cutoff.update', cutoff.id)">{{ useFormat(cutoff.end_date) }}</Link>
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <!-- <div class="flex">
                        <div class=" mr-4">
                            <TextInput class="w-80">

                            </TextInput>
                        </div>
                        <div>
                            <DropdownMenu>
                                <DropdownMenuTrigger class="bg-white border inline-flex items-center px-3 py-2 rounded-md text-black hover:text-white hover:bg-gray-800 focus:outline-none transition ease-in-out duration-150">
                                    Filter
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
                                </DropdownMenuTrigger>
                                <DropdownMenuContent>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem>Active</DropdownMenuItem>
                                    <DropdownMenuItem>Not Active</DropdownMenuItem>
                                    <DropdownMenuItem>All</DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div> -->

                    <div v-if="cutoff == null" class="text-end">
                        <PrimaryButton class="mr-10 m-5 rounded-md bg-slate-800 py-2 px-4 border border-transparent text-center text-sm text-white transition-all shadow-md hover:shadow-lg focus:bg-slate-700 focus:shadow-none active:bg-slate-700 hover:bg-slate-700 active:shadow-none disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none ml-2" type="button">
                            <Link :href="route('account.new')">+ Add account</Link>
                        </PrimaryButton>
                    </div>
                    <div v-else class="text-end">
                        <PrimaryButton class="mr-10 m-5 rounded-md bg-slate-800 py-2 px-4 border border-transparent text-center text-sm text-white transition-all shadow-md hover:shadow-lg focus:bg-slate-700 focus:shadow-none active:bg-slate-700 hover:bg-slate-700 active:shadow-none disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none ml-2" type="button">
                            <Link :href="route('cutoff.export', cutoff.id)">Export Data</Link>
                        </PrimaryButton>
                    </div>

                    <div class="border flex flex-col w-full h-full text-gray-700 bg-white shadow-md rounded-xl bg-clip-border">
                        <table class="w-full text-center table-auto">
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
