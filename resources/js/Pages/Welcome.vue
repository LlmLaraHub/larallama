<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

import { ArrowPathIcon, CloudArrowUpIcon, LightBulbIcon, FingerPrintIcon, LockClosedIcon, BoltIcon } from '@heroicons/vue/24/outline'


defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

const cases = [


    {
        name: 'Harmonize Your Data',
        description:
            'Seamless integration and organization of various data sources into a unified, coherent system.',
        icon: CloudArrowUpIcon,
    },
    {
        name: 'Empower',
        description:
            'Our Platform enables secure, efficient teamwork and sharing of insights within and across teams.',
        icon: BoltIcon,
    },
    {
        name: 'Illuminate',
        description:
            'Actionable intelligence, thereby enlightening decision-making processes.',
        icon: LightBulbIcon,
    },
]

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}

const activeTab = ref('hormonize');

const setActiveTab = (tab) => {
    activeTab.value = tab;
};

</script>

<template>
    <Head>
        <title>Welcome</title>
    </Head>
    <div class="bg-gray-900">
        <main>
            <div class="flex justify-end p-2">
                <Link class="btn btn-secondary link text-sm" href="/login">Login</Link>
            </div>
            <!-- Hero section -->
            <div class="relative isolate overflow-hidden">
                <svg class="absolute inset-0 -z-10 h-full w-full stroke-white/10 [mask-image:radial-gradient(100%_100%_at_top_right,white,transparent)]"
                    aria-hidden="true">
                    <defs>
                        <pattern id="983e3e4c-de6d-4c3f-8d64-b9761d1534cc" width="200" height="200" x="50%" y="-1"
                            patternUnits="userSpaceOnUse">
                            <path d="M.5 200V.5H200" fill="none" />
                        </pattern>
                    </defs>
                    <svg x="50%" y="-1" class="overflow-visible fill-gray-800/20">
                        <path
                            d="M-200 0h201v201h-201Z M600 0h201v201h-201Z M-400 600h201v201h-201Z M200 800h201v201h-201Z"
                            stroke-width="0" />
                    </svg>
                    <rect width="100%" height="100%" stroke-width="0"
                        fill="url(#983e3e4c-de6d-4c3f-8d64-b9761d1534cc)" />
                </svg>
                <div class="absolute left-[calc(50%-4rem)] top-10 -z-10 transform-gpu blur-3xl sm:left-[calc(50%-18rem)] lg:left-48 lg:top-[calc(50%-30rem)] xl:left-[calc(50%-24rem)]"
                    aria-hidden="true">
                    <div class="aspect-[1108/632] w-[69.25rem] bg-gradient-to-r from-[#80caff] to-[#4f46e5] opacity-20"
                        style="clip-path: polygon(73.6% 51.7%, 91.7% 11.8%, 100% 46.4%, 97.4% 82.2%, 92.5% 84.9%, 75.7% 64%, 55.3% 47.5%, 46.5% 49.4%, 45% 62.9%, 50.3% 87.2%, 21.3% 64.1%, 0.1% 100%, 5.4% 51.1%, 21.4% 63.9%, 58.9% 0.2%, 73.6% 51.7%)">
                    </div>

                </div>
                <div class="mx-auto max-w-7xl px-6 pb-24 pt-10 sm:pb-40 lg:flex lg:px-8 lg:pt-40">
                    <div class="mx-auto max-w-2xl flex-shrink-0 lg:mx-0 lg:max-w-xl lg:pt-8">
                        <img class="h-11" src="https://tailwindui.com/img/logos/mark.svg?color=indigo&shade=500"
                            alt="Your Company">
                        <div class="mt-24 sm:mt-32 lg:mt-16">
                            <a href="https://github.com/LlmLaraHub/laralamma" target="_blank" class="inline-flex space-x-6">
                                <span
                                    class="rounded-full bg-indigo-500/10 px-3 py-1 text-sm font-semibold leading-6 text-indigo-400 ring-1 ring-inset ring-indigo-500/20">Latest
                                    updates</span></a>
                                <span
                                    class="ml-2 inline-flex items-center space-x-2 text-sm font-medium leading-6 text-gray-300">
                                    <a href="https://github.com/LlmLaraHub/laralamma/releases" target="_blank">See Releases</a>
                                    <svg class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor"
                                        aria-hidden="true">
                                        <path fill-rule="evenodd"
                                            d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </span>
                        </div>
                        <h1 class="mt-10 text-4xl font-bold tracking-tight text-white sm:text-6xl">Transform Your Data into Insights</h1>
                        <p class="mt-6 text-lg leading-8 text-gray-300">
                            Simplify complex data processes with an intuitive platform designed to empower your
                            decisions.
                        </p>
                    </div>
                    <div
                        class="mx-auto mt-16 flex max-w-2xl sm:mt-24 lg:ml-10 lg:mr-0 lg:mt-0 lg:max-w-none lg:flex-none xl:ml-32">
                        <div class="max-w-3xl flex-none sm:max-w-5xl lg:max-w-none">
                            <img src="https://tailwindui.com/img/component-images/dark-project-app-screenshot.png"
                                alt="App screenshot" width="2432" height="1442"
                                class="w-[76rem] rounded-md bg-white/5 shadow-2xl ring-1 ring-white/10">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cases Simplified -->
            <div class="py-24 sm:py-32">
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl lg:text-center">
                        <h2 class="text-base font-semibold leading-7 text-indigo-600">Your Data</h2>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-gray-300 sm:text-4xl">
                            Your Data, Your Rules, Our Tooling to make it happen
                        </p>
                        <p class="mt-6 text-lg leading-8 text-gray-400">
                            A comprehensive suite of tools tailored to optimize your data journey from ingestion to
                            insights.
                        </p>
                    </div>
                    <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-4xl">
                        <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-10 lg:max-w-none lg:grid-cols-2 lg:gap-y-16">
                            <div v-for="caseItem in cases" :key="caseItem.name" class="relative pl-16">
                                <dt class="text-base font-semibold leading-7 text-gray-200">
                                    <div
                                        class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600">
                                        <component :is="caseItem.icon" class="h-6 w-6 text-white" aria-hidden="true" />
                                    </div>
                                    {{ caseItem.name }}
                                </dt>
                                <dd class="mt-2 text-base leading-7 text-gray-300">{{ caseItem.description }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="text-2xl text-gray-500 flex mx-auto text-center justify-center font-semibold mt-10"
                v-auto-animate>

                <div v-if="activeTab == 'harmonize'">
                    Seamless integration and organization of various data sources into a unified, coherent system
                </div>
                <div v-if="activeTab == 'empower'">
                    Our Platform enables secure, efficient teamwork and sharing of insights within and across teams.
                </div>

                <div v-if="activeTab == 'illuminate'">
                    Actionable intelligence, thereby enlightening decision-making processes.
                </div>
            </div>


            <!-- Feature section -->
            <div class="mx-auto mt-32 max-w-7xl px-6 sm:mt-24 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-base font-semibold leading-7 text-indigo-400">Data Unleashed</h2>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-white sm:text-4xl">Everything You Need to
                        Unleash Data's Potential</p>
                    <p class="mt-6 text-lg leading-8 text-gray-300">
                        A comprehensive suite of tools tailored to optimize your data journey from ingestion to
                        insights.
                    </p>
                </div>
                <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-none">
                    <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-16 lg:max-w-none lg:grid-cols-3">
                        <div class="flex flex-col">
                            <dt class="text-base font-semibold leading-7 text-white">
                                <div class="mb-6 flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-500">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                    </svg>
                                </div>
                                Seamless Data Integration
                            </dt>
                            <dd class="mt-1 flex flex-auto flex-col text-base leading-7 text-gray-300">
                                <p class="flex-auto">Connect effortlessly with various data sources to streamline your
                                    workflow. Experience the ease of bringing all your data together.</p>
                                <p class="mt-6">
                                    <a href="#" class="text-sm font-semibold leading-6 text-indigo-400">Learn more <span
                                            aria-hidden="true">→</span></a>
                                </p>
                            </dd>
                        </div>
                        <div class="flex flex-col">
                            <dt class="text-base font-semibold leading-7 text-white">
                                <div class="mb-6 flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-500">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                    </svg>
                                </div>
                                Advanced Data Processing
                            </dt>
                            <dd class="mt-1 flex flex-auto flex-col text-base leading-7 text-gray-300">
                                <p class="flex-auto">
                                    Harness powerful analytics to transform raw data into actionable insights. Dive
                                    deeper with less effort.
                                </p>
                                <p class="mt-6">
                                    <a href="#" class="text-sm font-semibold leading-6 text-indigo-400">Learn more <span
                                            aria-hidden="true">→</span></a>
                                </p>
                            </dd>
                        </div>
                        <div class="flex flex-col">
                            <dt class="text-base font-semibold leading-7 text-white">
                                <div class="mb-6 flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-500">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />
                                    </svg>
                                </div>
                                Collaborative Data Environment
                            </dt>
                            <dd class="mt-1 flex flex-auto flex-col text-base leading-7 text-gray-300">
                               <p>
                                    Through 'Collaborate with Clarity' and 'Share Insights Seamlessly,' we make teamwork more efficient and communication crystal clear, empowering your projects with shared understanding and actionable insights.
                                </p>
                                <p class="mt-6">
                                    <a href="#" class="text-sm font-semibold leading-6 text-indigo-400">Learn more <span
                                            aria-hidden="true">→</span></a>
                                </p>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Feature section -->
            <div class="mt-32 sm:mt-56">
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl sm:text-center">
                        <h2 class="text-base font-semibold leading-7 text-indigo-400">Everything you need</h2>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-white sm:text-4xl">No server? No problem.
                        </p>
                        <p class="mt-6 text-lg leading-8 text-gray-300">Our cloud-based solution scales with your needs,
                            ensuring you have the power to handle data without the overhead</p>
                    </div>
                </div>
                <div class="relative overflow-hidden pt-16">
                    <div class="mx-auto max-w-7xl px-6 lg:px-8">
                        <img src="https://tailwindui.com/img/component-images/dark-project-app-screenshot.png"
                            alt="App screenshot" class="mb-[-12%] rounded-xl shadow-2xl ring-1 ring-white/10"
                            width="2432" height="1442">
                        <div class="relative" aria-hidden="true">
                            <div class="absolute -inset-x-20 bottom-0 bg-gradient-to-t from-gray-900 pt-[7%]"></div>
                        </div>
                    </div>
                </div>
                <div class="mx-auto mt-16 max-w-7xl px-6 sm:mt-20 md:mt-24 lg:px-8">
                    <dl
                        class="mx-auto grid max-w-2xl grid-cols-1 gap-x-6 gap-y-10 text-base leading-7 text-gray-300 sm:grid-cols-2 lg:mx-0 lg:max-w-none lg:grid-cols-3 lg:gap-x-8 lg:gap-y-16">
                        <div class="relative pl-9">
                            <dt class="inline font-semibold text-white">
                                <svg class="absolute left-1 top-1 h-5 w-5 text-indigo-500" viewBox="0 0 20 20"
                                    fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M5.5 17a4.5 4.5 0 01-1.44-8.765 4.5 4.5 0 018.302-3.046 3.5 3.5 0 014.504 4.272A4 4 0 0115 17H5.5zm3.75-2.75a.75.75 0 001.5 0V9.66l1.95 2.1a.75.75 0 101.1-1.02l-3.25-3.5a.75.75 0 00-1.1 0l-3.25 3.5a.75.75 0 101.1 1.02l1.95-2.1v4.59z"
                                        clip-rule="evenodd" />
                                </svg>
                                Easy upload or import
                            </dt>
                            <dd class="inline">Easy to upload documents and integrate data.</dd>
                        </div>
                        <div class="relative pl-9">
                            <dt class="inline font-semibold text-white">
                                <svg class="absolute left-1 top-1 h-5 w-5 text-indigo-500" viewBox="0 0 20 20"
                                    fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z"
                                        clip-rule="evenodd" />
                                </svg>
                                Organize and Secure
                            </dt>
                            <dd class="inline">Group documents around projects or teams for organized workflows.</dd>
                        </div>
                        <div class="relative pl-9">
                            <dt class="inline font-semibold text-white">
                                <svg class="absolute left-1 top-1 h-5 w-5 text-indigo-500" viewBox="0 0 20 20"
                                    fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M15.312 11.424a5.5 5.5 0 01-9.201 2.466l-.312-.311h2.433a.75.75 0 000-1.5H3.989a.75.75 0 00-.75.75v4.242a.75.75 0 001.5 0v-2.43l.31.31a7 7 0 0011.712-3.138.75.75 0 00-1.449-.39zm1.23-3.723a.75.75 0 00.219-.53V2.929a.75.75 0 00-1.5 0V5.36l-.31-.31A7 7 0 003.239 8.188a.75.75 0 101.448.389A5.5 5.5 0 0113.89 6.11l.311.31h-2.432a.75.75 0 000 1.5h4.243a.75.75 0 00.53-.219z"
                                        clip-rule="evenodd" />
                                </svg>
                                Search Smartly
                            </dt>
                            <dd class="inline">Query your data efficiently with fast, intuitive tools.</dd>
                        </div>
                        <div class="relative pl-9">
                            <dt class="inline font-semibold text-white">
                                <svg class="absolute left-1 top-1 h-5 w-5 text-indigo-500" viewBox="0 0 20 20"
                                    fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M10 2.5c-1.31 0-2.526.386-3.546 1.051a.75.75 0 01-.82-1.256A8 8 0 0118 9a22.47 22.47 0 01-1.228 7.351.75.75 0 11-1.417-.49A20.97 20.97 0 0016.5 9 6.5 6.5 0 0010 2.5zM4.333 4.416a.75.75 0 01.218 1.038A6.466 6.466 0 003.5 9a7.966 7.966 0 01-1.293 4.362.75.75 0 01-1.257-.819A6.466 6.466 0 002 9c0-1.61.476-3.11 1.295-4.365a.75.75 0 011.038-.219zM10 6.12a3 3 0 00-3.001 3.041 11.455 11.455 0 01-2.697 7.24.75.75 0 01-1.148-.965A9.957 9.957 0 005.5 9c0-.028.002-.055.004-.082a4.5 4.5 0 018.996.084V9.15l-.005.297a.75.75 0 11-1.5-.034c.003-.11.004-.219.005-.328a3 3 0 00-3-2.965zm0 2.13a.75.75 0 01.75.75c0 3.51-1.187 6.745-3.181 9.323a.75.75 0 11-1.186-.918A13.687 13.687 0 009.25 9a.75.75 0 01.75-.75zm3.529 3.698a.75.75 0 01.584.885 18.883 18.883 0 01-2.257 5.84.75.75 0 11-1.29-.764 17.386 17.386 0 002.078-5.377.75.75 0 01.885-.584z"
                                        clip-rule="evenodd" />
                                </svg>
                                Easy Secure Access
                            </dt>
                            <dd class="inline">Access your insights through a user-friendly UI or direct API calls.</dd>
                        </div>
                        <div class="relative pl-9">
                            <dt class="inline font-semibold text-white">
                                <svg class="absolute left-1 top-1 h-5 w-5 text-indigo-500" viewBox="0 0 20 20"
                                    fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M7.84 1.804A1 1 0 018.82 1h2.36a1 1 0 01.98.804l.331 1.652a6.993 6.993 0 011.929 1.115l1.598-.54a1 1 0 011.186.447l1.18 2.044a1 1 0 01-.205 1.251l-1.267 1.113a7.047 7.047 0 010 2.228l1.267 1.113a1 1 0 01.206 1.25l-1.18 2.045a1 1 0 01-1.187.447l-1.598-.54a6.993 6.993 0 01-1.929 1.115l-.33 1.652a1 1 0 01-.98.804H8.82a1 1 0 01-.98-.804l-.331-1.652a6.993 6.993 0 01-1.929-1.115l-1.598.54a1 1 0 01-1.186-.447l-1.18-2.044a1 1 0 01.205-1.251l1.267-1.114a7.05 7.05 0 010-2.227L1.821 7.773a1 1 0 01-.206-1.25l1.18-2.045a1 1 0 011.187-.447l1.598.54A6.993 6.993 0 017.51 3.456l.33-1.652zM10 13a3 3 0 100-6 3 3 0 000 6z"
                                        clip-rule="evenodd" />
                                </svg>
                                Deploy anywhere
                            </dt>
                            <dd class="inline">Deploy flexibly with options for hosted or self-hosted solutions,
                                tailored to your security needs.</dd>
                        </div>
                        <div class="relative pl-9">
                            <dt class="inline font-semibold text-white">
                                <svg class="absolute left-1 top-1 h-5 w-5 text-indigo-500" viewBox="0 0 20 20"
                                    fill="currentColor" aria-hidden="true">
                                    <path
                                        d="M4.632 3.533A2 2 0 016.577 2h6.846a2 2 0 011.945 1.533l1.976 8.234A3.489 3.489 0 0016 11.5H4c-.476 0-.93.095-1.344.267l1.976-8.234z" />
                                    <path fill-rule="evenodd"
                                        d="M4 13a2 2 0 100 4h12a2 2 0 100-4H4zm11.24 2a.75.75 0 01.75-.75H16a.75.75 0 01.75.75v.01a.75.75 0 01-.75.75h-.01a.75.75 0 01-.75-.75V15zm-2.25-.75a.75.75 0 00-.75.75v.01c0 .414.336.75.75.75H13a.75.75 0 00.75-.75V15a.75.75 0 00-.75-.75h-.01z"
                                        clip-rule="evenodd" />
                                </svg>
                                For Anyone
                            </dt>
                            <dd class="inline">Easy enough for the non-technical. Deep enough for the technical</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Stats -->
            <div class="mx-auto mt-32 max-w-7xl px-6 sm:mt-56 lg:px-8">
                <div class="mx-auto max-w-2xl lg:mx-0 lg:max-w-xl">
                    <h2 class="text-base font-semibold leading-8 text-indigo-400">Our track record</h2>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-white sm:text-4xl">Trusted by thousands of
                        developers&nbsp;worldwide</p>
                    <p class="mt-6 text-lg leading-8 text-gray-300">Lorem ipsum, dolor sit amet consectetur adipisicing
                        elit. Maiores impedit perferendis suscipit eaque, iste dolor cupiditate blanditiis ratione.</p>
                </div>
                <dl
                    class="mx-auto mt-16 grid max-w-2xl grid-cols-1 gap-x-8 gap-y-10 text-white sm:mt-20 sm:grid-cols-2 sm:gap-y-16 lg:mx-0 lg:max-w-none lg:grid-cols-4">
                    <div class="flex flex-col gap-y-3 border-l border-white/10 pl-6">
                        <dt class="text-sm leading-6">Developers on the platform</dt>
                        <dd class="order-first text-3xl font-semibold tracking-tight">8,000+</dd>
                    </div>
                    <div class="flex flex-col gap-y-3 border-l border-white/10 pl-6">
                        <dt class="text-sm leading-6">Daily requests</dt>
                        <dd class="order-first text-3xl font-semibold tracking-tight">900m+</dd>
                    </div>
                    <div class="flex flex-col gap-y-3 border-l border-white/10 pl-6">
                        <dt class="text-sm leading-6">Uptime guarantee</dt>
                        <dd class="order-first text-3xl font-semibold tracking-tight">99.9%</dd>
                    </div>
                    <div class="flex flex-col gap-y-3 border-l border-white/10 pl-6">
                        <dt class="text-sm leading-6">Projects deployed</dt>
                        <dd class="order-first text-3xl font-semibold tracking-tight">12m</dd>
                    </div>
                </dl>
            </div>

            <!-- CTA section -->
            <div class="relative isolate mt-32 px-6 py-32 sm:mt-56 sm:py-40 lg:px-8">
                <svg class="absolute inset-0 -z-10 h-full w-full stroke-white/10 [mask-image:radial-gradient(100%_100%_at_top_right,white,transparent)]"
                    aria-hidden="true">
                    <defs>
                        <pattern id="1d4240dd-898f-445f-932d-e2872fd12de3" width="200" height="200" x="50%" y="0"
                            patternUnits="userSpaceOnUse">
                            <path d="M.5 200V.5H200" fill="none" />
                        </pattern>
                    </defs>
                    <svg x="50%" y="0" class="overflow-visible fill-gray-800/20">
                        <path
                            d="M-200 0h201v201h-201Z M600 0h201v201h-201Z M-400 600h201v201h-201Z M200 800h201v201h-201Z"
                            stroke-width="0" />
                    </svg>
                    <rect width="100%" height="100%" stroke-width="0"
                        fill="url(#1d4240dd-898f-445f-932d-e2872fd12de3)" />
                </svg>
                <div class="absolute inset-x-0 top-10 -z-10 flex transform-gpu justify-center overflow-hidden blur-3xl"
                    aria-hidden="true">
                    <div class="aspect-[1108/632] w-[69.25rem] flex-none bg-gradient-to-r from-[#80caff] to-[#4f46e5] opacity-20"
                        style="clip-path: polygon(73.6% 51.7%, 91.7% 11.8%, 100% 46.4%, 97.4% 82.2%, 92.5% 84.9%, 75.7% 64%, 55.3% 47.5%, 46.5% 49.4%, 45% 62.9%, 50.3% 87.2%, 21.3% 64.1%, 0.1% 100%, 5.4% 51.1%, 21.4% 63.9%, 58.9% 0.2%, 73.6% 51.7%)">
                    </div>
                </div>
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Boost Your
                        Productivity<br>Simplify your data, amplify your insights.</h2>
                    <p class="mx-auto mt-6 max-w-xl text-lg leading-8 text-gray-300">Start harnessing the full potential
                        of your data with our platform today.</p>
                    <div class="mt-10 flex items-center justify-center gap-x-6">
                        <a href="#"
                            class="rounded-md bg-white px-3.5 py-2.5 text-sm font-semibold text-gray-900 shadow-sm hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">Get
                            started</a>
                        <a href="#" class="text-sm font-semibold leading-6 text-white">Learn more <span
                                aria-hidden="true">→</span></a>
                    </div>
                </div>
            </div>

            <!-- Logo cloud -->
            <div class="mx-auto mt-8 max-w-7xl px-6 sm:mt-16 lg:px-8">
                <h2 class="text-center text-lg font-semibold leading-8 text-white">The world’s most innovative companies
                    use our app</h2>
                <div
                    class="mx-auto mt-10 grid max-w-lg grid-cols-4 items-center gap-x-8 gap-y-10 sm:max-w-xl sm:grid-cols-6 sm:gap-x-10 lg:mx-0 lg:max-w-none lg:grid-cols-5">
                    <img class="col-span-2 max-h-12 w-full object-contain lg:col-span-1"
                        src="https://tailwindui.com/img/logos/158x48/transistor-logo-white.svg" alt="Transistor"
                        width="158" height="48">
                    <img class="col-span-2 max-h-12 w-full object-contain lg:col-span-1"
                        src="https://tailwindui.com/img/logos/158x48/reform-logo-white.svg" alt="Reform" width="158"
                        height="48">
                    <img class="col-span-2 max-h-12 w-full object-contain lg:col-span-1"
                        src="https://tailwindui.com/img/logos/158x48/tuple-logo-white.svg" alt="Tuple" width="158"
                        height="48">
                    <img class="col-span-2 max-h-12 w-full object-contain sm:col-start-2 lg:col-span-1"
                        src="https://tailwindui.com/img/logos/158x48/savvycal-logo-white.svg" alt="SavvyCal" width="158"
                        height="48">
                    <img class="col-span-2 col-start-2 max-h-12 w-full object-contain sm:col-start-auto lg:col-span-1"
                        src="https://tailwindui.com/img/logos/158x48/statamic-logo-white.svg" alt="Statamic" width="158"
                        height="48">
                </div>
            </div>

        </main>



        <!-- Footer -->
        <footer aria-labelledby="footer-heading" class="relative">
            <h2 id="footer-heading" class="sr-only">Footer</h2>
            <div class="mx-auto max-w-7xl px-6 pb-8 pt-4 lg:px-8">
                <div class="border-t border-white/10 pt-8 md:flex md:items-center md:justify-between">
                    <div class="flex space-x-6 md:order-2">
                        <a href="#" class="text-gray-500 hover:text-gray-400">
                            <span class="sr-only">Facebook</span>
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"
                                    clip-rule="evenodd" />
                            </svg>
                        </a>
                        <a href="#" class="text-gray-500 hover:text-gray-400">
                            <span class="sr-only">Instagram</span>
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </a>
                        <a href="#" class="text-gray-500 hover:text-gray-400">
                            <span class="sr-only">Twitter</span>
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path
                                    d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                            </svg>
                        </a>
                        <a href="#" class="text-gray-500 hover:text-gray-400">
                            <span class="sr-only">GitHub</span>
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"
                                    clip-rule="evenodd" />
                            </svg>
                        </a>
                        <a href="#" class="text-gray-500 hover:text-gray-400">
                            <span class="sr-only">YouTube</span>
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M19.812 5.418c.861.23 1.538.907 1.768 1.768C21.998 8.746 22 12 22 12s0 3.255-.418 4.814a2.504 2.504 0 0 1-1.768 1.768c-1.56.419-7.814.419-7.814.419s-6.255 0-7.814-.419a2.505 2.505 0 0 1-1.768-1.768C2 15.255 2 12 2 12s0-3.255.417-4.814a2.507 2.507 0 0 1 1.768-1.768C5.744 5 11.998 5 11.998 5s6.255 0 7.814.418ZM15.194 12 10 15V9l5.194 3Z"
                                    clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                    <p class="mt-8 text-xs leading-5 text-gray-400 md:order-1 md:mt-0">&copy; 2020 Your Company, Inc.
                        All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
