<script setup lang="ts">
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps([
    'cutoff',
]);

function format(dateString) {
    var options = {  year: 'numeric', month: 'long', day: 'numeric' };
    var date  = new Date(dateString);
    return date.toLocaleDateString("en-US", options);
}
</script>

<template>
    <tr>
        <td class="p-4 border-b border-blue-gray-50">
            {{ format(cutoff.cutoff_date) }}
        </td>
        <td class="p-4 border-b border-blue-gray-50">
            {{ format(cutoff.start_date) }}
        </td>
        <td class="p-4 border-b border-blue-gray-50">
            {{ format(cutoff.end_date) }}
        </td>
        <td class="p-4 border-b border-blue-gray-50">
            <PrimaryButton
                v-if="cutoff.end_date > $page.props.date"
            ><Link :href="route('cutoff.get', cutoff.id)">Reschedule</Link></PrimaryButton>
        </td>
    </tr>
</template>
