<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table'

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

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-4 text-end">
                    <PrimaryButton
                        class="mr-10 m-5 rounded-md bg-slate-800 py-2 px-4 border border-transparent text-center text-sm text-white transition-all shadow-md hover:shadow-lg focus:bg-slate-700 focus:shadow-none active:bg-slate-700 hover:bg-slate-700 active:shadow-none disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none ml-2"
                        type="button">
                        + New Type
                    </PrimaryButton>
                </div>

                <div
                    class="border flex flex-col w-full h-full text-gray-700 bg-white shadow-md rounded-xl bg-clip-border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="text-center w-80">Name</TableHead>
                                <TableHead class="text-center w-80">Description</TableHead>
                                <TableHead class="text-center w-52">Action</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="addition in additions">
                                <TableCell><Link class="ml-8 underline" :href="route('addition.getRelated', addition.id)">{{ addition.name }}</Link></TableCell>
                                <TableCell> {{ addition.description }}</TableCell>
                                <TableCell class="text-center">
                                    <PrimaryButton type="button">
                                        <Link :href="route('mockup.additionsalarydetails')">Edit</Link>
                                    </PrimaryButton>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>

</template>