<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Period from '@/Components/Period.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps([
    'cutoffs',
    'account',
]);

const sortedCutoffs = computed(() =>
    Object.values(props.cutoffs).sort(
        (a, b) => a.end_date < b.end_date ? 1 : b.end_date < a.end_date ? -1 : 0
    )
);
</script>

<template>

    <Head title="Payroll Cutoffs" />
    <AuthenticatedLayout>
        <template #header>
            <h2 v-if="account == null" class="font-semibold text-xl text-gray-800 leading-tight">Payroll Cutoffs</h2>
            <h2 v-else class="font-semibold text-xl text-gray-800 leading-tight">
                Payroll Cutoffs for
                <Link v-if="($page.props.auth.isPayroll || $page.props.auth.isHr)"
                    class="text-gray-500 hover:text-gray-700 hover:underline" :href="route('account.get', account.id)">
                {{ account.name }}
                </Link>
                <span v-else>{{ account.name }}</span>
            </h2>
        </template>

        <div class="pt-2 pb-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div v-if="account == null" class="text-end">
                    <PrimaryButton
                        class="mr-10 m-5 rounded-md bg-slate-800 py-2 px-4 border border-transparent text-center text-sm text-white transition-all shadow-md hover:shadow-lg focus:bg-slate-700 focus:shadow-none active:bg-slate-700 hover:bg-slate-700 active:shadow-none disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none ml-2"
                        type="button">
                        <Link :href="route('cutoff.newForm')">+Add cutoff</Link>
                    </PrimaryButton>
                </div>
                <div v-else-if="($page.props.auth.isPayroll || $page.props.auth.isHr)" class="text-end">
                    <PrimaryButton
                        class="mr-10 m-5 rounded-md bg-slate-800 py-2 px-4 border border-transparent text-center text-sm text-white transition-all shadow-md hover:shadow-lg focus:bg-slate-700 focus:shadow-none active:bg-slate-700 hover:bg-slate-700 active:shadow-none disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none ml-2"
                        type="button">
                        <Link :href="route('payroll.getCurrentFromUser', account.id)">Current Cutoff</Link>
                    </PrimaryButton>
                </div>

                <div class="mt-3 border relative flex flex-col w-full h-full text-gray-700 bg-white shadow-md rounded-xl bg-clip-border">
                    <table class="w-full text-center table-auto min-w-max">
                        <thead class="uppercase">
                            <tr v-if="account == null" class="bg-light">
                                <th scope="col" width="5%" class="p-4 border-b border-blue-gray-100 bg-blue-gray-50"></th>
                                <th scope="col"
                                    class="text-left p-4 border-b border-blue-gray-100 bg-blue-gray-50">
                                    Release/End</th>
                                <th scope="col"
                                    class="text-left p-4 border-b border-blue-gray-100 bg-blue-gray-50">
                                    Start</th>
                                <th scope="col"
                                    class="text-left p-4 border-b border-blue-gray-100 bg-blue-gray-50">
                                    Cutoff</th>
                                <th scope="col"
                                    class="p-4 border-b border-blue-gray-100 bg-blue-gray-50">
                                    Status</th>
                                <th v-if="($page.props.auth.isHr)"
                                    scope="col"
                                    class="p-4 border-b border-blue-gray-100 bg-blue-gray-50">
                                    Action</th>
                            </tr>
                            <tr v-else class="bg-light">
                                <th scope="col" width="5%" class="p-4 border-b border-blue-gray-100 bg-blue-gray-50"></th>
                                <th scope="col"
                                    class="text-left p-4 border-b border-blue-gray-100 bg-blue-gray-50">
                                    Release/End</th>
                                <th scope="col"
                                    class="text-left p-4 border-b border-blue-gray-100 bg-blue-gray-50">
                                    Start</th>
                                <th scope="col"
                                    class="text-left p-4 border-b border-blue-gray-100 bg-blue-gray-50">
                                    Cutoff</th>
                                <th scope="col"
                                    class="p-4 border-b border-blue-gray-100 bg-blue-gray-50">
                                    Status</th>
                                <th scope="col" width="10%" class="p-4 border-b border-blue-gray-100 bg-blue-gray-50"></th>
                            </tr>
                        </thead>
                        <Period v-for="cutoff in sortedCutoffs" :cutoff :account />
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
