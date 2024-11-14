<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps([
    'additions',
]);
</script>
<!--
<template>
    <Head title="Addition Types" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Addition Types
            </h2>
        </template>

        <div class="py-12">
            <div v-for="addition in additions">
                <Link :href="route('addition.getRelated', addition.id)">{{ addition.name }}</Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template> -->


<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Additions</h2>
        </template>

        <div class="pt-3 pb-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-4 text-end">
                    <PrimaryButton
                        class="mr-10 m-5 rounded-md bg-slate-800 py-2 px-4 border border-transparent text-center text-sm text-white transition-all shadow-md hover:shadow-lg focus:bg-slate-700 focus:shadow-none active:bg-slate-700 hover:bg-slate-700 active:shadow-none disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none ml-2"
                        type="button">
                        <Link :href="route('addition.newForm')">
                            + New Type
                        </Link>
                    </PrimaryButton>
                </div>

                <div class="border flex flex-col w-full h-full text-gray-700 bg-white shadow-md rounded-xl bg-clip-border">
                    <table class="w-full text-start table-auto">
                        <thead class="uppercase">
                            <tr class="bg-light">
                                <th scope="col" width="5%" class="p-4 border-b border-blue-gray-100 bg-blue-gray-50"></th>
                                <th scope="col" width="30%" class="text-start p-4 border-b border-blue-gray-100 bg-blue-gray-50">Name</th>
                                <th scope="col" class="text-start p-4 border-b border-blue-gray-100 bg-blue-gray-50">Description</th>
                                <th scope="col" width="30%" class="p-4 border-b border-blue-gray-100 bg-blue-gray-50">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="addition in additions">
                                <td class="p-4 border-b border-blue-gray-50"></td>
                                <td class="p-4 border-b border-blue-gray-50"><Link class="underline" :href="route('addition.getRelated', addition.id)">{{ addition.name }}</Link></td>
                                <td class="p-4 border-b border-blue-gray-50"> {{ addition.description }}</td>
                                <td class="p-4 border-b border-blue-gray-50 text-center">
                                    <PrimaryButton type="button">
                                        <Link :href="route('addition.edit', addition.id)">Edit</Link>
                                    </PrimaryButton>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>

</template>
