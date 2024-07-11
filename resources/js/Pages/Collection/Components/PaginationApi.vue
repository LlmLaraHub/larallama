<template>
    <div class=" -mb-1 w-full">
        <nav
            class="flex items-center justify-between border-t border-ring px-4 py-3 sm:px-6"
            aria-label="Pagination">
            <div class="hidden sm:block" v-if="showing !== false">
                <p class="text-sm text-gray-400">
                    Showing
                    <span class="font-medium">{{ meta.meta.from }}</span>
                    to
                    <span class="font-medium">{{ meta.meta.to }}</span>
                    of
                    <span class="font-medium">{{ meta.meta.total }}</span>
                    results
                </p>
            </div>
            <div class="flex flex-1 justify-between sm:justify-end gap-2"
            :class="{'sm:justify-center': showing === false}"
            >

                <Link class="btn btn-secondary rounded-none" v-if="meta.links.prev"
                      :href="meta.links.prev">Previous</Link>

                <button
                    disabled
                    class="btn btn-secondary rounded-none" v-else
                >Previous</button>


                <Link class="btn btn-secondary rounded-none" v-if="meta.links.next"
                      :href="meta.links.next">Next</Link>
                <button
                    disabled
                    class="btn btn-secondary rounded-none" v-else
                        >Next</button>
            </div>
        </nav>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    meta: Object,
    showing: {
        type: Boolean,
        default: true
    }
})
</script>
