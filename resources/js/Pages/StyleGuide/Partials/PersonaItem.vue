<script setup>
import {useForm} from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";

const props = defineProps({
    persona: Object
})

const form = useForm({
    name: props.persona.name,
    content: props.persona.content,
});

const submit = () => {
    form.put(route('style_guide.update.persona', {
        persona: props.persona.id
    }), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {

        }
    })
}
</script>

<template>

    <form @submit.prevent="submit">

        <div class="col-span-6 sm:col-span-4">
            <InputLabel for="name" value="Title" />
            <input
                id="name"
                v-model="form.name"
                type="text"
                class="mt-1 block input input-bordered w-full "
                placeholder="In the voice of Mark Twain"
            />
            <InputError :message="form.errors.name" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <InputLabel for="data" value="Content" />
            <textarea
                rows="15"
                id="data"
                v-model="form.content"
                class="textarea textarea-bordered w-full"
                placeholder="To succeed in life, you need two things: ignorance and confidence. And always remember, the secret of getting ahead is getting started

The reports of my death are greatly exaggerated.

Always do right. This will gratify some people and astonish the rest
"
            />
            <InputError :message="form.errors.content" class="mt-2" />
        </div>
        <div class="flex justify-end gap-2 items-center mt-2">
            <button class="btn btn-secondary rounded-none">Update</button>
            <slot>

            </slot>
        </div>

    </form>


</template>

<style scoped>

</style>
