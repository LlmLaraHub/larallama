<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { usePage } from '@inertiajs/vue3';


const people = [


  { name: 'Lindsay Walton', title: 'Front-end Developer', email: 'lindsay.walton@example.com', role: 'Member' },
  // More people...
]
</script>

<template>
    <div>
        <div class="py-12 px-10 bg-white border-b border-gray-200">
            <ApplicationLogo class="block h-12 w-auto" />

            <h1 class="mt-8 text-2xl font-medium text-gray-900">
                Welcome to {{  usePage().props.app_name }}, where your data transformation journey begins! Dive into a seamless experience of managing and analyzing your data with precision and ease.
            </h1>

            <p class="mt-6 text-gray-500 leading-relaxed">
                Get started by creating a new 'Collection'—your first step towards organizing diverse data sources like PDFs, website data, and more, into a cohesive unit. Each 'Collection' serves as a centralized hub for your team's data, making it easier to collaborate, analyze, and derive actionable insights. Simply select or switch your team from the menu, and create or access your Collections to embark on a streamlined data management journey. Ready to unlock the full potential of your data? Let's create your first Collection and set the foundation for unparalleled team collaboration and insight discovery.
            </p>
        </div>

       
    </div>
</template>
