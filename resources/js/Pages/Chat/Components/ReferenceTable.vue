<template>
    <div v-if="message.message_document_references.length === 0" class="flex justify-start items-start gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-12 text-gray-400">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
        </svg>

        <p>Since there are no references this must be a summary type query which means the system did a summary of all the
            documents in the collection and returned the summary. <br>
            <span class="text-gray-500">View "Prompt History" to see the prompt that was used to generate this summary.</span>
        </p>
    </div>
    <table class="table table-zebra" v-else>
        <!-- head -->
        <thead>
            <tr>
                <th></th>
                <th>Document Name</th>
                <th>Page</th>
                <th class="text-center">Section/<br>Record ID</th>
                <th>Summary</th>
            </tr>
        </thead>
        <tbody>
            <!-- row 1 -->
            <tr v-for="reference in message.message_document_references" :key="reference.id">
                <th>{{ reference.id }}</th>
                <td>
                    <a class="underline" :href="route('download.document', {
                        collection: message.collection_id,
                        document_name: reference.document_name
                    })">{{ reference.document_name }}</a>
                </td>
                <td>{{ reference.page }}</td>
                <td>{{ reference.section_number }}/{{ reference.document_chunk_id }}</td>
                <td>
                    <span v-html="reference.summary"></span>
                  </td>
            </tr>
        </tbody>
    </table>
</template>
<script setup>
const props = defineProps({
    message: Object
})
</script>
