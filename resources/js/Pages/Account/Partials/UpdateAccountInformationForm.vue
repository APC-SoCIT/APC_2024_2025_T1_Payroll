<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useConfirm } from '@/Utils/Confirm.js';
import { useForm, usePage } from '@inertiajs/vue3';

const props = defineProps(['targetAccount']);

const form = useForm({
    name: props.targetAccount.name,
    email: props.targetAccount.email,
    bank_account_number: props.targetAccount.bank_account_number,
    // checkbox component needs an explicit boolean value
    active: props.targetAccount.active == true,
});
</script>


<!-- >> from Edit Account button (Accounts Page) << -->


<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">Account Information</h2>

            <p class="mt-1 text-sm text-gray-600">
                Update account information.
            </p>
        </header>

        <form @submit.prevent="form.patch(route('account.update', { id: targetAccount.id }), {
            preserveScroll: true,
            onBefore: () => {
                if (!form.active && props.targetAccount.active == true) {
                    return useConfirm(`Are you sure you want to deactivate the account of ${form.name}? This will delete all their records for ongoing and upcoming payroll cutoffs. This action cannot be undone.`)();
                }
                return true;
            }
        })" class="mt-6 space-y-6">
            <div class="flex space-x-4 static">
                <!-- Name text input -->
                <div>
                    <InputLabel for="name" value="Name" />
                    <TextInput id="name" type="text" class="mt-1 block w-64 " v-model="form.name" required autofocus
                        :disabled="!($page.props.auth.isPayroll || $page.props.auth.isHr || $page.props.auth.isAdmin)" />

                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <!-- Email Adress text input -->
                <div>
                    <InputLabel for="email" value="Email Address" />

                    <TextInput id="email" type="email" class="mt-1 block w-64" v-model="form.email" required
                        :disabled="!($page.props.auth.isPayroll || $page.props.auth.isHr || $page.props.auth.isAdmin)" />

                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <!-- Bank Account text input -->
                <div>
                    <InputLabel for="bank_account_number" value="BDO Account Number" />

                    <TextInput id="bank_account_number" type="bank_account_number" class="mt-1 block w-64" v-model="form.bank_account_number" required
                        :disabled="!($page.props.auth.isPayroll || $page.props.auth.isHr || $page.props.auth.isAdmin)" />

                    <InputError class="mt-2" :message="form.errors.bank_account_number" />
                </div>
            </div>

            <!-- Checkbox -->
            <div class="block mt-4">
                <label class="flex items-center">
                    <Checkbox :disabled="!($page.props.auth.isPayroll || $page.props.auth.isHr || $page.props.auth.isAdmin)" name="active"
                        v-model:checked="form.active" />
                    <span class="ms-2 text-sm text-gray-600">Active</span>
                </label>
            </div>

            <!-- Save button -->
            <div v-if="($page.props.auth.isPayroll || $page.props.auth.isHr || $page.props.auth.isAdmin)" class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
