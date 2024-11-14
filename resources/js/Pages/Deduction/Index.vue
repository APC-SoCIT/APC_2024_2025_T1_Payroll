<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps([
    'deductions',
]);
</script>

<template>
    <Head title="Deduction Types" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Deduction Types
            </h2>
        </template>

        <div class="py-12">
            <div v-for="deduction in deductions">
                <Link :href="route('deduction.getRelated', deduction.id)">{{ deduction.name }}</Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
