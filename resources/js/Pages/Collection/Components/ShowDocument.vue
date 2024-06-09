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
                  text-gray-200 dark:text-gray-400
                  bg-base-100 dark:bg-base-200
                  border-b border-gray-100 dark:border-gray-700 py-6 shadow-xl">
                    <div class="px-4 sm:px-6">
                      <div class="flex items-start justify-between">
                        <DialogTitle class="text-base font-semibold leading-6 ">Document {{ document.file_path }}</DialogTitle>
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
                    <div class="relative mt-6 flex-1 px-4 sm:px-6">
                        <Tags :document="document"></Tags>
                      <h2 class="font-bold">Summary:</h2>
                      <div class="prose  mb-10 mt-5" v-html="document.summary_markdown"></div>



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
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'
import Tags from '@/Components/Tags.vue';

const props = defineProps({
    document: Object,
    open: Boolean,
});

const emit = defineEmits(['closing']);

function closeSlideOut() {
    open.value = false;
    emit('closing');
}


</script>
