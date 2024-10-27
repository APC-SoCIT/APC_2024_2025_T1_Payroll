<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CreateCutoffForm from './Partials/CreateCutoffForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps([
    'cutoff'
]);

</script>

<template>
    <Head title="Account" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Payroll Cutoff Scheduling</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <CreateCutoffForm :cutoff/>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
