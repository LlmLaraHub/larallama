<template>
    <TransitionRoot as="template" :show="open">
      <Dialog as="div" class="relative z-50" @close="open = false">
        <div class="fixed inset-0" />

          <div class="fixed inset-0 overflow-hidden">
              <div class="absolute inset-0 overflow-hidden">
                  <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10 sm:pl-16">
                      <TransitionChild as="template" enter="transform transition ease-in-out duration-500 sm:duration-700" enter-from="translate-x-full" enter-to="translate-x-0" leave="transform transition ease-in-out duration-500 sm:duration-700" leave-from="translate-x-0" leave-to="translate-x-full">
                          <DialogPanel class="pointer-events-auto w-screen max-w-2xl">
                              <div class="flex h-full flex-col overflow-y-scroll

                  bg-base-100 dark:bg-base-200
                  border-b border-gray-100 dark:border-gray-700 py-6 shadow-xl">
                                  <div class="px-4 sm:px-6">
                                      <div class="flex items-start justify-between">
                                          <DialogTitle class="text-base font-semibold leading-6">Create a document using the text and title below</DialogTitle>
                        <div class="ml-3 flex h-7 items-center">
                            <button
                                type="button" class="
                                dark:text-neutral
                                relative rounded-md  focus:outline-none
                                focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                @click="closeSlideOut()">
                                <span class="absolute -inset-2.5" />
                                <span class="sr-only">Close panel</span>
                                <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                            </button>
                        </div>
                      </div>
                    </div>
                    <div class="relative mt-6 flex-1 px-4 sm:px-6">
                      <form @submit.prevent="submit()">
                        <ResourceForm v-model="form" />

                        <div class="mt-8 flex justify-between">

                          <PrimaryButton type="submit" class="ms-3" :disabled="form.processing">
                            <Spinner v-if="form.processing">running</Spinner>
                            Save
                          </PrimaryButton>
                          <SecondaryButton type="button" @click="closeSlideOut()">
                            Cancel
                          </SecondaryButton>
                        </div>
                        </form>
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
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { computed, inject, onMounted, ref, watch } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'
import { Head, Link, useForm } from '@inertiajs/vue3';
import ResourceForm from './TextDocumentResourceForm.vue';
import { useToast } from 'vue-toastification';
import Spinner from '@/Components/Spinner.vue';

const toast = useToast();

const props = defineProps({
    collection: Object,
    open: Boolean,
});

const emit = defineEmits(['closing'])

const closeSlideOut = () => {
    emit('closing')
}

const form = useForm({
  name: "",
  content: ""
})


const submit = () => {
    form.post(route('text-documents.store', {
        collection: props.collection.id
    }), {
        onSuccess: () => {
            form.reset();
            closeSlideOut()
        }
    })
}

</script>
