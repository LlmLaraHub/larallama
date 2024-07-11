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
const cancelAllForm = useForm({})

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

const cancelAll = () => {
    cancelAllForm.post(route('batches.cancel-all'), {
        onStart: params => {
          toast("Cancelling all batches");
        },
        preserveScroll: true,
        onSuccess: () => {
            toast.success('All Batches Cancelled');
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
                                <div class="justify-end flex gap-4 items-center">
                                    <Link
                                        class="btn btn-secondary rounded-none"
                                        :href="route('batches.index')" >
                                        Reload Page
                                    </Link>

                                    <button
                                        type="button"
                                        @click="cancelAll"
                                        class="btn btn-danger rounded-none">
                                        Cancel All
                                    </button>
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
                                Created At\Finished At\Cancelled At
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
                                {{ batch.created_at }} \ {{ batch.finished_at }} \ {{ batch.cancelled_at }}
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
