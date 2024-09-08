<script setup>
import {Head, Link} from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    projects: Object,
})


</script>

<template>
    <AppLayout title="projects">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Projects
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="flex justify-between items-start">
                        <div class="w-full pr-10 pl-5 pt-1">
                         Projects are "hubs" to all of your Assistant's abilities. Automations, Searches, exporting, and more will center around this.
                            For example, if you had a project "Marketing Campaign for Company X"
                            And you then start chatting with the assistant, you can then "Kick off" the project.
                            Let's say that starting chat is to kick off a campaign that last from 10/1/2024 to 10/31/2024 and you then
                            tell it about the company it will then make a plan for you under this project. All the tasks, milestones etc.
                            Then when you share documents and other data with it all of that will be under this project.
                            You can invite people ot the "Team or Company" and then all the projects will be under that team.
                        </div>
                        <div class="flex justify-end gap-4 p-4">
                            <Link :href="route('projects.create')" class="btn btn-primary rounded-none">Create</Link>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="overflow-x-auto">
                            <div v-if="projects.data.length == 0">
                                <div class="text-center">
                                    <div class="mb-4">
                                        No projects Yet!
                                    </div>
                                    <Link
                                        :href="route('projects.create')"
                                        class="btn btn-primary rounded-none">Create</Link>
                                </div>
                            </div>
                            <table class="table" v-else>
                                <!-- head -->
                                <thead>
                                <tr>
                                    <th></th>
                                    <th>Who</th>
                                    <th>Name</th>
                                    <th>Team</th>
                                    <th>View</th>
                                </tr>
                                </thead>
                                <tbody>
                                <!-- row 1 -->
                                <tr class="bg-base-200" v-for="project in projects.data" :key="project.id">
                                    <td>{{ project.id }}</td>
                                    <td>
                                        <div class="avatar-group -space-x-6 rtl:space-x-reverse">
                                            <div class="avatar" v-for="user in project.users" :key="user.id">
                                                <div class="w-12">
                                                    <img :src="user.profile_photo_url" />
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        {{ project.name }}
                                    </td>

                                    <td>
                                        {{ project.team?.name }}
                                    </td>
                                    <td>
                                        <Link
                                            class="link"
                                            :href="route('projects.show', project.id)">view</Link>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>

</style>
