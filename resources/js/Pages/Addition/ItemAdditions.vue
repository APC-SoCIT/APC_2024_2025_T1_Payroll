<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
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

            <!-- Select Account dropdown | Add button -->
            <form @submit.prevent>
                <div v-if="accountsWithout.length > 0" class="my-8 space-x-4 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <select class="border p-2 w-64" v-model="form.account_id">
                        <option value="">Select account...</option>
                        <option v-for="account in accountsWithout" :value="account.id">{{ account.name }}
                        </option>
                    </select>
                    <PrimaryButton
                        class="mr-10 m-5 rounded-md bg-slate-800 py-2 px-4 border border-transparent text-center text-sm text-white transition-all shadow-md hover:shadow-lg focus:bg-slate-700 focus:shadow-none active:bg-slate-700 hover:bg-slate-700 active:shadow-none disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none ml-2"
                        type="button">
                        <Link
                            :href="route('itemAddition.new', { cutoff: cutoff.id, user: form.account_id, addition: addition.id })"
                            method="post">+ Add
                        </Link>
                    </PrimaryButton>
                </div>
            </form>

            <!-- Accounts with Salary/Wage Addition -->
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Accounts with {{ addition.name }}
                        Addition
                    </h2>

                    <div class="my-8 w-4/6" v-for="itemAddition in itemAdditions">
                        <UpdateItemAdditionForm :itemAddition :cutoff :additionView="true"
                            :targetAccount="itemAddition.payroll_item.user" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
