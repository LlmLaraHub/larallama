<template>
    <bubble-menu
        :editor="editor"
        :tippy-options="{ duration: 100 }"
        v-if="editor">

        <div class="control-group">
            <div class="button-group">
                <button type="button" @click="editor.chain().focus().toggleBold().run()" :disabled="!editor.can().chain().focus().toggleBold().run()" :class="{ 'is-active': editor.isActive('bold') }">
                    Bold
                </button>
                <button type="button" @click="editor.chain().focus().toggleItalic().run()" :disabled="!editor.can().chain().focus().toggleItalic().run()" :class="{ 'is-active': editor.isActive('italic') }">
                    Italic
                </button>
                <button type="button" @click="editor.chain().focus().toggleStrike().run()" :disabled="!editor.can().chain().focus().toggleStrike().run()" :class="{ 'is-active': editor.isActive('strike') }">
                    Strike
                </button>
                <button type="button" @click="editor.chain().focus().toggleCode().run()" :disabled="!editor.can().chain().focus().toggleCode().run()" :class="{ 'is-active': editor.isActive('code') }">
                    Code
                </button>
                <button type="button" @click="editor.chain().focus().setParagraph().run()" :class="{ 'is-active': editor.isActive('paragraph') }">
                    Paragraph
                </button>
                <button type="button" @click="editor.chain().focus().toggleHeading({ level: 1 }).run()" :class="{ 'is-active': editor.isActive('heading', { level: 1 }) }">
                    H1
                </button>
                <button type="button" @click="editor.chain().focus().toggleHeading({ level: 2 }).run()" :class="{ 'is-active': editor.isActive('heading', { level: 2 }) }">
                    H2
                </button>
                <button type="button" @click="editor.chain().focus().toggleHeading({ level: 3 }).run()" :class="{ 'is-active': editor.isActive('heading', { level: 3 }) }">
                    H3
                </button>
                <button type="button" @click="editor.chain().focus().toggleBulletList().run()" :class="{ 'is-active': editor.isActive('bulletList') }">
                    Bullet list
                </button>
                <button type="button" @click="editor.chain().focus().toggleOrderedList().run()" :class="{ 'is-active': editor.isActive('orderedList') }">
                    Ordered list
                </button>
                <button type="button" @click="editor.chain().focus().toggleCodeBlock().run()" :class="{ 'is-active': editor.isActive('codeBlock') }">
                    Code block
                </button>
                <button type="button" @click="editor.chain().focus().toggleBlockquote().run()" :class="{ 'is-active': editor.isActive('blockquote') }">
                    Blockquote
                </button>
                <button type="button" @click="editor.chain().focus().setHorizontalRule().run()">
                    Horizontal rule
                </button>
                <button type="button" @click="editor.chain().focus().setHardBreak().run()">
                    Hard break
                </button>
                <button type="button" @click="editor.chain().focus().undo().run()" :disabled="!editor.can().chain().focus().undo().run()">
                    Undo
                </button>
                <button type="button" @click="editor.chain().focus().redo().run()" :disabled="!editor.can().chain().focus().redo().run()">
                    Redo
                </button>
            </div>
        </div>
        </bubble-menu>


    <editor-content
        @input="$emit('update:modelValue', $event.target.getHTML())"
        :editor="editor" />

    <div>
    </div>

</template>

<script setup>
import { BubbleMenu, Editor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import {ref, watch, onMounted, onBeforeUnmount} from "vue";

const editor = ref(null);

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
});

onMounted(() => {
    editor.value = new Editor({
        content: '<p>Edit here using Markdown</p>',
        extensions: [StarterKit],
    })
})

onBeforeUnmount(() => {
    editor.value.destroy()
})




</script>

<style scoped>
.tiptap :first-child {
    margin-top: 0;
}

.tiptap ul,
.tiptap ol {
    padding: 0 1rem;
    margin: 1.25rem 1rem 1.25rem 0.4rem;
}

.tiptap ul li p,
.tiptap ol li p {
    margin-top: 0.25em;
    margin-bottom: 0.25em;
}

.tiptap h1,
.tiptap h2,
.tiptap h3,
.tiptap h4,
.tiptap h5,
.tiptap h6 {
    line-height: 1.1;
    margin-top: 2.5rem;
    text-wrap: pretty;
}

.tiptap h1,
.tiptap h2 {
    margin-top: 3.5rem;
    margin-bottom: 1.5rem;
}

.tiptap h1 {
    font-size: 1.4rem;
}

.tiptap h2 {
    font-size: 1.2rem;
}

.tiptap h3 {
    font-size: 1.1rem;
}

.tiptap h4,
.tiptap h5,
.tiptap h6 {
    font-size: 1rem;
}

.tiptap code {
    background-color: var(--purple-light);
    border-radius: 0.4rem;
    color: var(--black);
    font-size: 0.85rem;
    padding: 0.25em 0.3em;
}

.tiptap pre {
    background: var(--black);
    border-radius: 0.5rem;
    color: var(--white);
    font-family: 'JetBrainsMono', monospace;
    margin: 1.5rem 0;
    padding: 0.75rem 1rem;
}

.tiptap pre code {
    background: none;
    color: inherit;
    font-size: 0.8rem;
    padding: 0;
}

.tiptap blockquote {
    border-left: 3px solid var(--gray-3);
    margin: 1.5rem 0;
    padding-left: 1rem;
}

.tiptap hr {
    border: none;
    border-top: 1px solid var(--gray-2);
    margin: 2rem 0;
}

.bubble-menu {
    background-color: var(--white);
    border: 1px solid var(--gray-1);
    border-radius: 0.7rem;
    box-shadow: var(--shadow);
    display: flex;
    padding: 0.2rem;
}

.bubble-menu button {
    background-color: unset;
}

.bubble-menu button:hover {
    background-color: var(--gray-3);
}

.bubble-menu button.is-active {
    background-color: var(--purple);
}

.bubble-menu button.is-active:hover {
    background-color: var(--purple-contrast);
}
</style>
