<template>
    <div class="bg-white py-2 md:py-4 space-y-4">
        <div v-if="header" class="py-2 flex items-center justify-between">
            <h2 class="text-gray-800 leading-tight font-bold">
                {{ header }}
            </h2>

            <div class="flex items-center">
            <!-- Page Filters -->
                <div v-if="filters" class="flex items-center space-x-3">
                    <div v-for="(filter, filterIndex) in filters" :key="filterIndex" class="flex items-center ml-6">
                                    <!-- Settings Dropdown -->
                        <div class="ml-3 relative">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <span class="inline-flex rounded-md">
                                        <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                            {{ filter.selected.text }}

                                            <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </span>
                                </template>
                                <template #content>
                                    <DropdownLink v-for="(selectable, selectableIndex) in filter.selectable" :key="selectableIndex" :href="selectable.href" method="get" as="button">
                                        {{ selectable.text }}
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </div>

                <div v-if="actions">
                    <div v-for="(action, actionIndex) in actions" :key="actionIndex">
                        <Link type="button" v-if="!action.rawLink" :href="action.url" class="rounded bg-indigo-500 hover:bg-indigo-700 text-white py-1 px-2 text-sm">
                        {{ action.text }}
                        </Link>
                        <a v-else :href="action.url" class="rounded bg-indigo-500 hover:bg-indigo-700 text-white py-1 px-2 text-sm" :target="action.newTab ? '_blank' : ''">
                            {{ action.text }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <slot />
        </div>
    </div>
</template>

<script>
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import { Link } from '@inertiajs/vue3'

export default {
    components: {
        Dropdown,
        DropdownLink,
        Link,
    },

    props: {
        header: String,
        filters: Array,
        actions: Array
    }
}
</script>
