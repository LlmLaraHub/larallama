<script setup>
import {onMounted, ref} from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ChatWidget from '@/Components/ChatWidget.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import MainMenu from "@/Layouts/MainMenu.vue";

import { themeChange } from 'theme-change'
import SettingsWarning from "@/Components/SettingsWarning.vue";

defineProps({
    title: String,
});

onMounted(() => {
    //themeChange(false);
})

const theme = ref('dark')


</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />
        <SettingsWarning />

        <div class="min-h-screen">
            <nav class="navbar bg-base-100 shadow shadow-bottom">
                <div class="navbar-start">
                    <Link :href="route('dashboard')">
                        <ApplicationMark class="block h-9 w-auto" />
                    </Link>
                    <ul class="menu menu-horizontal px-1">
                        <li class="hidden lg:flex">
                            <a href="https://larallama.io" target="_blank">
                                LaraLlama.io
                            </a>
                        </li>
                        <li class="hidden sm:flex">
                            <Link :href="route('dashboard')"
                                  :class="{ 'underline' : route().current('collections.index') }">
                                Collections
                            </Link>
                        </li>
                        <li class="hidden sm:flex">
                            <Link :href="route('style_guide.show')"
                                  :class="{ 'underline' : route().current('style_guide.show') }">
                                Style Guides
                            </Link>
                        </li>
                        <li class="hidden sm:flex">
                            <Link :href="route('settings.show')"
                                  :class="{ 'underline' : route().current('settings.show') }">
                                Settings
                            </Link>
                        </li>
                    </ul>
                </div>
                <div class="navbar-center hidden lg:flex">
                    <ul class="menu menu-horizontal px-1">
                        <li>
                        </li>
                    </ul>
                </div>

                <div class="navbar-end">
                    <div class="dropdown">
                        <div tabindex="0" role="button" class="btn btn-ghost lg:hidden">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" /></svg>
                        </div>
                        <ul tabindex="0" class="-ml-48 menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                            <MainMenu/>
                        </ul>
                    </div>
                    <ul class="menu menu-horizontal px-1 items-center   hidden lg:flex">
                        <MainMenu/>

                        <li>
                            <label class="flex cursor-pointer gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/></svg>
                                <input type="checkbox" value="light" class="toggle theme-controller"/>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                            </label>
                        </li>
                    </ul>
                </div>
            </nav>


            <!-- Page Content -->
            <main>
                <slot />
            </main>

            <ChatWidget
                v-if="$page.props.features.chat_widget"

                :user="$page.props.auth"></ChatWidget>
        </div>
    </div>
</template>
