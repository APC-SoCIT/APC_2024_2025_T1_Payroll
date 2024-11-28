<script setup lang="ts">
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table'
</script>


<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Deductions</h2>
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
                                <TableHead class="text-center">Name</TableHead>
                                <TableHead class="text-center">Description</TableHead>
                                <TableHead class="text-center w-80">Action</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow>
                                <TableCell class="text-center">
                                    <Link :href="route('mockup.deductionabsenceaccount')">placeholder</Link>
                                </TableCell>
                                <TableCell class="text-center">placeholder</TableCell>
                                <TableCell class="text-center">
                                    <PrimaryButton type="button">
                                        <Link :href="route('mockup.deductionabsencedetails')">Edit</Link>
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