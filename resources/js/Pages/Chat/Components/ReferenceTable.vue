<template>
    <table class="table table-zebra">
        <!-- head -->
        <thead>
            <tr>
                <th></th>
                <th>Document Name</th>
                <th>Page</th>
                <th>Distance</th>
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
                <td>{{ reference.distance }}</td>
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