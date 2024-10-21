<script setup lang="ts">
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useFormat } from '@/Utils/FormatDate.js';
import { Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps([ 'cutoff' ]);

const form = useForm({
    start_date: props.cutoff.start_date,
    cutoff_date: props.cutoff.cutoff_date,
    end_date: props.cutoff.end_date,
});

function confirm(prompt){
    return window.confirm(prompt);
}
</script>

<template>
    <section class="max-w-xl">
        <header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Rechedule Cutoff for {{ useFormat(cutoff.end_date) }}</h2>

            <p class="mt-1 text-sm text-gray-600">
                Reschedule cutoff.
            </p>
        </header>
        <form @submit.prevent="form.patch(route('cutoff.update', cutoff.id))" class="mt-6 space-y-6">
            <div>
                <h3 class="font-semibold text-l text-gray-800 leading-tight">Start Date</h3>
                <InputLabel for="start_date" value="First day of the cutoff" />

                <TextInput
                    id="start_date"
                    type="date"
                    class="mt-1 block w-full"
                    v-model="form.start_date"
                    :max="form.end_date"
                    required
                    :readonly="form.end_date < $page.props.date"
                />

                <InputError class="mt-2" :message="form.errors.start_date" />
            </div>

            <div>
                <h3 class="font-semibold text-l text-gray-800 leading-tight">Cutoff Date</h3>
                <InputLabel for="cutoff_date" value="Final day for attendance adjustment requests (OT, OB, Leave, etc.)" />

                <TextInput
                    id="cutoff_date"
                    type="date"
                    class="mt-1 block w-full"
                    v-model="form.cutoff_date"
                    :min="form.start_date"
                    :max="form.end_date"
                    :readonly="form.end_date < $page.props.date"
                />

                <InputError class="mt-2" :message="form.errors.cutoff_date" />
            </div>

            <div>
                <h3 class="font-semibold text-l text-gray-800 leading-tight">End Date</h3>
                <InputLabel for="end_date" value="Final day for processing payroll" />

                <TextInput
                    id="end_date"
                    type="date"
                    class="mt-1 block w-full"
                    v-model="form.end_date"
                    required
                    :min="form.start_date > $page.props.date ? form.start_date : $page.props.date"
                    :readonly="form.end_date < $page.props.date"
                />

                <InputError class="mt-2" :message="form.errors.end_date" />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>
                <DangerButton v-if="cutoff.end_date > $page.props.date">
                    <Link class="uppercase"
                        :href="route('cutoff.delete', cutoff.id)"
                        method="delete"
                        as="button"
                        :onBefore="() => confirm(`Are you sure you want to delete the schedule for ${useFormat(new Date(cutoff.end_date))}?`)"
                    >Delete</Link>
                </DangerButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
