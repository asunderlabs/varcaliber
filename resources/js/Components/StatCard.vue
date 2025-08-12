<template>
    <div class="w-full md:w-72 h-56 shadow bg-white rounded-lg overflow-hidden flex flex-col">
        <div class="bg-indigo-400 text-white p-3 text-center">
            <h5 class="text-md">{{ title }}</h5>
        </div>
        <div class="flex flex-col justify-center items-center grow space-y-2">
            <span class="text-4xl">{{ stat }}</span>
            <Link v-if="statLinkUrl" :href="statLinkUrl" class="hover:text-indigo-700 text-indigo-500 underline">
                {{ statLinkText ? statLinkText : "Link" }}
            </Link>
        </div>
    </div>
</template>

<script>
import { Link } from '@inertiajs/vue3';

export default {
    components: {
        Link
    },

    props: {
        title: String,
        stat: String,
        statLinkText: String,
        statLinkUrl: String,
    },
}
</script>

