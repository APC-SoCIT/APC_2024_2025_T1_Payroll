<script setup>
import PrimaryButtom from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UpdateItemAdditionForm from '@/Pages/Payroll/Partials/UpdateItemAdditionForm.vue';
import { useFormat } from '@/Utils/FormatDate.js';
import { useForm, Head, Link } from '@inertiajs/vue3';

const props = defineProps([
    'addition',
    'cutoff',
    'itemAdditions',
    'accountsWithout',
]);

const form = useForm({
    account_id: 0,
});
</script>

<template>
    <Head :title="addition.name + ' Additions'" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ addition.name }} Additions for {{ useFormat(cutoff.end_date) }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Accounts with {{ addition.name }} Addition</h2>

                    <form @submit.prevent>
                        <select v-model="form.account_id">
                            <option value="">Select account...</option>
                            <option v-for="account in accountsWithout" :value="account.id">{{ account.name }}</option>
                        </select>

                        <PrimaryButton>
                            <Link :href="route('itemAddition.new', { cutoff: cutoff.id, user: form.account_id, addition: addition.id})" method="post">
                                Add
                            </Link>
                        </PrimaryButton>
                    </form>
                    <div v-for="itemAddition in itemAdditions">
                        <UpdateItemAdditionForm
                            :itemAddition
                            :cutoff
                            :additionView="true"
                            :targetAccount="itemAddition.payroll_item.user"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
