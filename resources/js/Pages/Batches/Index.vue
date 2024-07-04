<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from "@/Pages/Collection/Components/Pagination.vue";
import {useForm, Link} from "@inertiajs/vue3";
import {useToast} from "vue-toastification";
import SectionTitle from "@/Components/SectionTitle.vue";

const toast = useToast();

const props = defineProps({
    batches: {
        type: Object,
    },
});

const form = useForm({})

const cancel = (batch) => {
    form.post(route('batches.cancel', {
        batchId: batch.id
    }), {
        onStart: params => {
          toast("Cancelling batch");
        },
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Batch Cancelled');
        }
    });
}

</script>

<template>
    <AppLayout title="Batch Jobs">

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="border-secondary border rounded-lg p-10 mb-10 w-full">
                    <SectionTitle>
                        <template #title>
                            Batch Jobs
                        </template>
                        <template #description>
                            <div class="w-full">
                                <div class="w-full">
                                    This area can help you cancel a running job.
                                    More details can be seen at <a
                                    class="link"
                                    :href="route('horizon.index')">Queue Area</a>
                                </div>
                                <div class="justify-end flex">
                                    <Link
                                        class="btn btn-secondary btn-circle btn-sm"
                                        :href="route('batches.index')" >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                        </svg>

                                    </Link>
                                </div>
                            </div>
                        </template>
                    </SectionTitle>
                </div>


                <div class="overflow-x-auto">
                    <table class="table">
                        <!-- head -->
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Total Jobs</th>
                            <th>Pending Jobs</th>
                            <th>Failed Jobs</th>
                            <th>
                                Created At\Cancelled At\Finished At
                            </th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <!-- row 1 -->
                        <tr v-for="batch in batches.data" :key="batch.id">
                            <td>
                                {{ batch.name }}
                            </td>
                            <td>
                               {{ batch.total_jobs }}
                            </td>
                            <td>
                               {{ batch.pending_jobs }}
                            </td>
                            <td>
                               {{ batch.failed_jobs }}
                            </td>
                            <td>
                                {{ batch.created_at }} \ {{ batch.cancelled_at }} \ {{ batch.finished_at }}
                            </td>
                            <td>
                                <button type="button"
                                        :disabled="form.processing"
                                        class="btn btn-sm btn-secondary rounded-none"
                                        @click="cancel(batch)">Cancel</button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div>
                    <Pagination
                        :meta="batches" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
