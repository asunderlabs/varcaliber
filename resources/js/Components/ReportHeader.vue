<template>
    <div class="py-3 mb-3">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center space-y-2 md:space-y-0">
            <div class="text-lg md:text-2xl font-bold">{{ title }}</div>
            <div class="mb-4 w-auto" v-if="$slots.status">
                <slot name="status" />
            </div>
        </div>
        <div v-if="lastUpdatedAt" class="text-xs text-gray-400 mt-2">Last updated at {{ lastUpdatedAt }}</div>
    </div>
    <div v-if="periodStart || periodEnd || actions?.length" class="mb-4 md:mb-0">
        <div v-if="periodStart || periodEnd">{{ periodName }}: {{ periodStart }} - {{ periodEnd }}</div>
        <div v-if="actions" class="inline-flex space-x-3 md:justify-end print:hidden py-2">
            <div v-for="(action, actionIndex) in actions" :key="actionIndex">
                <a v-if="action.rawLink" :href="action.href" class="text-indigo-500 hover:text-indigo-700 underline text-sm" :target="action.newTab ? '_blank' : ''">
                    {{ action.text }}
                </a>
                <Link v-else-if="action.href" :href="action.href" class="text-sm" :class="linkClasses">{{ action.text }}</Link>
                <Link v-else-if="action.callback" @click="action.callback" as="button" type="button" class="text-sm" :class="linkClasses">{{ action.text }}</Link>
            </div>
        </div>
    </div>
    <div v-if="stat" class="bg-white flex flex-col py-3 md:py-4 space-y-2 w-64">
        <h3 class="text-lg text-gray-800 leading-tight font-bold">{{ stat.name }}</h3>
        <div class="text-xl bg-gray-50 rounded p-3">
            {{ stat.number }}
        </div>
        <p class="text-sm md:text-md text-gray-700">{{ stat.description }}</p>
    </div>
</template>

<script>
import { Link } from '@inertiajs/vue3';

export default {
    components: {
        Link,
    },
    props: {
        title: String,
        periodName: {
            type: String,
            default: 'Billing Period'
        },
        periodStart: String,
        periodEnd: String, 
        lastUpdatedAt: String,
        actions: Array,
        stat: Object
    },

    computed: {
        linkClasses() {
            return "text-indigo-500 hover:text-indigo-700 underline"
        }
    }
}
</script>
