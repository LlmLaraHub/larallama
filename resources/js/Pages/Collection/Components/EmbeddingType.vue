<template>
    <Listbox as="div" v-model="selected">
      <ListboxLabel class="sr-only">Choose Embedding LLM Drier</ListboxLabel>
      <div class="relative">
        <div class="inline-flex divide-x divide-indigo-700 rounded-md shadow-sm">
          <div class="inline-flex items-center gap-x-1.5 rounded-l-md bg-indigo-600 px-3 py-2 text-white shadow-sm">
            <CheckIcon class="-ml-0.5 h-5 w-5" aria-hidden="true" />
            <p class="text-sm font-semibold">{{ selected.title }}</p>
          </div>
          <ListboxButton class="inline-flex items-center rounded-l-none rounded-r-md bg-indigo-600 p-2 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 focus:ring-offset-gray-50">
            <ChevronDownIcon class="h-5 w-5 text-white" aria-hidden="true" />
          </ListboxButton>
        </div>
  
        <transition leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
          <ListboxOptions 
          
          class="absolute left-0 z-10 mt-2 w-72 origin-top-right divide-y divide-gray-200 overflow-hidden rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
            <ListboxOption 
          :disabled="!option.active"
          as="template" v-for="option in publishingOptions" :key="option.title" :value="option" v-slot="{ active, selected }">
              <li :class="[active ? 'bg-indigo-600 text-white' : 'text-gray-900', 'cursor-default select-none p-4 text-sm']">
                <div class="flex flex-col">
                  <div class="flex justify-between">
                    <p :class="selected ? 'font-semibold' : 'font-normal'">{{ option.title }}
                      <span class="text-gray-600" v-if="!option.active">
                      (coming soon..)</span>
                    </p>
                    <span v-if="selected" :class="active ? 'text-white' : 'text-indigo-600'">
                      <CheckIcon class="h-5 w-5" aria-hidden="true" />
                    </span>
                  </div>
                  <p :class="[active ? 'text-indigo-200' : 'text-gray-500', 'mt-2']">{{ option.description }}</p>
                </div>
              </li>
            </ListboxOption>
          </ListboxOptions>
        </transition>
      </div>
    </Listbox>
  </template>
  
  <script setup>
  import { ref, watch } from 'vue'
  import { Listbox, ListboxButton, ListboxLabel, ListboxOption, ListboxOptions } from '@headlessui/vue'
  import { CheckIcon, ChevronDownIcon } from '@heroicons/vue/20/solid'
  
  const emit = defineEmits(['embeddingTypeChosen'])

  const publishingOptions = [
    { active: true, key: "mock", title: 'Mock LLM', description: 'This will mock all the LLM features great for local development', current: true },
    { active: true, key: "openai", title: 'OpenAi', description: 'This will work with the OpenAi Api', current: false },
    { active: false, key: "mock", title: 'OpenAi Azure', description: 'This will work with the Azure OpenAi Api', current: false },
    { active: true, key: "ollama", title: 'Ollama', description: 'This will work with the Ollam API', current: false },
    { active: false, key: "mock", title: 'Gemini', description: 'This will work with the Gemini Api', current: false },
    { active: true, key: "mock", title: 'Claude using Vonage', description: 'This will work with the Claude Api', current: false },
  ]
  const props = defineProps({
    default: {
      type: String,
      default: 'mock'
    }
  })

  const selected = ref(publishingOptions
    .find(option => option.key === props.default) || publishingOptions[0])

  watch(selected, (value) => {
    console.log("emit " , value)
    emit('embeddingTypeChosen', value.key)
  })

  </script>