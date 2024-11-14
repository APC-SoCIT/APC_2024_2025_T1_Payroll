<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps([
    'additions',
]);
</script>

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
</template>
