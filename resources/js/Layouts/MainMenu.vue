<script setup>
import {router, Link} from "@inertiajs/vue3";

const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <li>
        <details>
            <summary>{{ $page.props.auth.user.current_team.name }}</summary>
            <ul class="p-2">
                <li><Link :href="route('teams.show', $page.props.auth.user.current_team)">Team Settings</Link></li>
                <li
                    v-if="$page.props.jetstream.canCreateTeams"
                ><Link :href="route('teams.create')">Create New Team</Link></li>

                <li v-if="$page.props.auth.user.all_teams.length > 1">
                    <details>
                        <summary>Switch Team</summary>
                        <ul class="p-2">
                            <li v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                <form @submit.prevent="switchToTeam(team)">
                                    <button>
                                        <div class="flex items-center">
                                            <svg v-if="team.id == $page.props.auth.user.current_team_id" class="me-2 h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>

                                            <div>{{ team.name }}</div>
                                        </div>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </details>
                </li>
            </ul>
        </details>
    </li>
    <li>
        <details>
            <summary>
                Main Menu
            </summary>
            <ul class="p-2 z-50">
                <li class="flex sm:hidden"><Link :href="route('collections.index')">Collections</Link></li>
                <li class="flex sm:hidden"><Link :href="route('style_guide.show')">Style Guides</Link></li>
                <li><Link :href="route('profile.show')">Profile</Link></li>
                <li><Link :href="route('settings.show')">Settings</Link></li>
                <li><a :href="route('horizon.index')">Queue</a></li>
                <li>
                    <Link v-if=false :href="route('api-tokens.index')">API Tokens</Link>
                </li>
                <li>
                    <form @submit.prevent="logout">
                        <button>
                            Log Out
                        </button>
                    </form>
                </li>
            </ul>
        </details>
    </li>
</template>

<style scoped>

</style>
