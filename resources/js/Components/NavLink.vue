<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>

<script>
import { Link } from '@inertiajs/vue3';

export default {
    components: {
        Link,
    },

    props: ['href', 'active'],

    computed: {
        classes() {
            return this.active
                ? 'inline-flex space-x-3 items-center px-3 py-3 border-l-2 border-indigo-400 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-indigo-700 hover:bg-indigo-100 transition duration-150 ease-in-out hover'
                : 'inline-flex space-x-3 items-center px-3 py-3 border-l-2 border-transparent text-sm font-medium leading-5 text-gray-600 hover:text-gray-700 hover:border-gray-500 hover:bg-indigo-100 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out'
        }
    }
}
</script>
