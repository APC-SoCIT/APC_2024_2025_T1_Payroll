<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import UpdateCutoffForm from './Partials/UpdateCutoffForm.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps([
    'cutoff'
]);
</script>

<template>
    <Head title="Account" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Payroll Cutoff Schedule Details</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 space-x-4 bg-white shadow sm:rounded-lg">
                    <SecondaryButton><Link :href="route('accounts.getFromCutoff', cutoff.id)">View all involved accounts</Link></SecondaryButton>
                </div>
            </div>
            <div class="p-4 sm:p-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <UpdateCutoffForm :cutoff/>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
