<template>

    <div :class="{ 'opacity-50 dark:bg-neutral': dragOver }"
        class="rounded-md shadow-lg px-10 py-10 my-2 max-w-2xl justify-center mx-auto border border-secondary text-center">
        <div v-bind="getRootProps({
        class: 'dropzone',
        onDragover: onDragover,
        onDragLeave: onDragLeave,
    })">
            <input v-bind="getInputProps()" class="h-40" />
            <p v-if="isDragActive">Drop the files here ...</p>
            <div class="w-full text-center flex gap-2" v-else>Drag 'n' drop some Documents, Images here, or click <span class="hover:cursor-pointer underline flex">
                here</span> to upload files</div>
            </div>

        <div class="flex mt-5 justify-center ">
            <button class="btn btn-primary"
                v-if="form.files.length > 0"
                type="button" @click="submitFiles()">
                Start Importing ({{ form.files.length }}) Files
            </button>

        </div>
    </div>

</template>


<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { computed, onMounted, ref } from 'vue';

import { useDropzone } from "vue3-dropzone";
import { router, useForm } from '@inertiajs/vue3';


const props = defineProps({
    collection: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    files: [],
});
const saveFiles = (files) => {
    for (var x = 0; x < files.length; x++) {
        form.files.push(files[x]);
    }
}

const submitFiles = () => {
    form.post(route("collections.upload", {
        collection: props.collection.data.id,
    }), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
}

const onDrop = (acceptFiles, rejectReasons) => {
    saveFiles(acceptFiles);
    console.log(rejectReasons)
    submitFiles()
}

const dragOver = ref(false);
const onDragover = (event) => {
    dragOver.value = true;
}

const onDragLeave = (event) => {
    dragOver.value = false;
}
const { getRootProps, getInputProps, ...rest } = useDropzone({ onDrop });

</script>
