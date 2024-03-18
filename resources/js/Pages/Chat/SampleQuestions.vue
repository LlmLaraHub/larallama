<template>
    <Popover class="relative" >
      <PopoverButton class="inline-flex items-center gap-x-1 text-sm font-semibold leading-6 text-gray-900">
        <span>Example Questions</span>
        <ChevronDownIcon class="h-5 w-5" aria-hidden="true" />
      </PopoverButton>
  
      <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-1">
        <PopoverPanel class="absolute left-1/2 z-10 mt-5 flex w-screen max-w-max -translate-x-1/2 px-4">
          <div class="w-screen max-w-md flex-auto overflow-hidden rounded-3xl bg-white text-sm leading-6 shadow-lg ring-1 ring-gray-900/5">
            <div class="p-4">
              <div v-for="item in questions" :key="item" 
              class="hover:cursor-pointer group relative flex gap-x-6 rounded-lg p-4 hover:bg-gray-50">
                <div @click="chooseQuestion(item)">
                  <PopoverButton class="mt-1 text-gray-600">{{ item }}</PopoverButton>
                </div>
              </div>
            </div>
          </div>
        </PopoverPanel>
      </transition>
    </Popover>
  </template>
  
  <script setup>
  import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue'
  import { ChevronDownIcon, PhoneIcon, PlayCircleIcon } from '@heroicons/vue/20/solid'
  import {
    ArrowPathIcon,
    ChartPieIcon,
    CursorArrowRaysIcon,
    FingerPrintIcon,
    SquaresPlusIcon,
  } from '@heroicons/vue/24/outline'
  
    const props = defineProps({
        questions: Array
    })

    const emits = defineEmits(['chosenQuestion'])

    const chooseQuestion = (question) => {
        console.log(question);
        emits('chosenQuestion', question)
    }

  </script>