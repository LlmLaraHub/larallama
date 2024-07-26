<template>
    <TransitionRoot as="template" :show="open">
      <Dialog as="div" class="relative z-50" @close="open = false">
        <div class="fixed inset-0" />

        <div class="fixed inset-0 overflow-hidden ">
          <div class="absolute inset-0 overflow-hidden">
            <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10 sm:pl-16">
              <TransitionChild as="template" enter="transform transition ease-in-out duration-500 sm:duration-700" enter-from="translate-x-full" enter-to="translate-x-0" leave="transform transition ease-in-out duration-500 sm:duration-700" leave-from="translate-x-0" leave-to="translate-x-full">
                <DialogPanel class="pointer-events-auto w-screen max-w-2xl">
                  <div class="flex h-full flex-col overflow-y-scroll
                  bg-base-100 dark:bg-base-200
                  border border-neutral
                  py-6 shadow-xl">
                    <div class="px-4 sm:px-6">
                      <div class="flex items-start justify-between">
                        <DialogTitle class="text-base font-semibold leading-6 ">Document {{ documentToShow.file_path }}</DialogTitle>
                        <div class="ml-3 flex h-7 items-center">
                          <button
                          type="button" class="relative rounded-md  focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                          @click="closeSlideOut()">
                            <span class="absolute -inset-2.5" />
                            <span class="sr-only">Close panel</span>
                            <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                          </button>
                        </div>
                      </div>
                    </div>
                    <div class="relative mt-6 flex-1 px-4 sm:px-6" v-auto-animate>
                        <Tags :document="document"></Tags>
                        <div class="flex justify-end gap-2 items-center mt-2">
                            <UpdateSummary
                                @startingUpdate="startingUpdate"
                                @updateSummary="updateSummary"
                                :document="document"></UpdateSummary>
                            <button
                                v-if="false"
                                type="button" class="btn btn-ghost rounded-none" @click="toggleEdit">
                                edit</button>
                        </div>
                    <div role="tablist" class="tabs tabs-bordered mt-4 mb-4">
                        <a role="tab" class="tab"
                        :class="{ 'tab-active': activeTab === 'summary' }"
                        @click="toggleTab('summary')"
                        >Summary</a>
                        <a role="tab" class="tab"
                           :class="{ 'tab-active': activeTab === 'original_content' }"
                           @click="toggleTab('original_content')"
                        >Original Data</a>
                    </div>
                        <div v-if="activeTab === 'summary'">
                            <h2 class="font-bold">Summary:</h2>
                            <div v-if="updating" class="mt-10">
                                <div class="flex w-full flex-col gap-4">
                                    <div class="skeleton bg-gray-700 h-4 w-28"></div>
                                    <div class="skeleton bg-gray-700 h-4 w-full"></div>
                                    <div class="skeleton bg-gray-700 h-4 w-full"></div>
                                    <div class="skeleton bg-gray-700  h-32 w-full"></div>
                                </div>
                            </div>
                            <div
                                v-if="!updating && !showEdit"
                                class="prose  mb-10 mt-5" v-html="documentToShow.summary_markdown"></div>
                            <div v-if="showEdit && !updating && false">
                                <MdEditor
                                    theme="dark"
                                    language="en"
                                    :preview=false
                                    v-model="documentToShow.summary" />
                            </div>
                        </div>
                        <div v-if="activeTab === 'original_content'">
                            <div class="prose  mb-10 mt-5" v-html="documentToShow.original_content"></div>

                        </div>
                    </div>
                  </div>
                </DialogPanel>
              </TransitionChild>

            </div>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </template>


<script setup>

import { MdEditor } from 'md-editor-v3';
import 'md-editor-v3/lib/style.css';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'
import Tags from '@/Components/Tags.vue';
import UpdateSummary from "./UpdateSummary.vue";
import {router} from "@inertiajs/vue3";
import {onMounted, ref} from "vue";

const props = defineProps({
    document: Object,
    open: Boolean,
});

const activeTab = ref('summary');

const toggleTab = (tab) => {
    activeTab.value = tab;
}

const documentToShow = ref(props.document);

onMounted(() => {
    documentToShow.value = props.document;
});

const showEdit = ref(false);

const emit = defineEmits(['closing']);

const updating = ref(false);

const toggleEdit = () => {
    showEdit.value = !showEdit.value;
}

const startingUpdate = () => {
    updating.value = true;
}


function closeSlideOut() {
    open.value = false;
    emit('closing');
}

function updateSummary(documentUpdated) {
    console.log('updateSummary called');
    documentToShow.value = documentUpdated;
    updating.value = false;
}

</script>
