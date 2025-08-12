<script setup>
import { ArrowLongLeftIcon, ArrowLongRightIcon } from '@heroicons/vue/20/solid'
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  pagination: Object
})

const previousLink = computed(() => {
  let links = JSON.parse(JSON.stringify(props.pagination.links))
  return links.splice(0, 1)[0]
})

const nextLink = computed(() => {
  let links = JSON.parse(JSON.stringify(props.pagination.links))
  return links.splice(links.length - 1, 1)[0]
})

const numberLinks = computed(() => {
  let links = JSON.parse(JSON.stringify(props.pagination.links))
  links.splice(0, 1)
  links.splice(links.length - 1, 1)
  return links
})
</script>

<template>
  <nav class="flex items-center justify-between border-t border-gray-200 px-4 sm:px-0">
    <div class="-mt-px flex w-0 flex-1">
      <Link v-if="previousLink.url" :href="previousLink.url"
        preserve-scroll 
        class="inline-flex items-center border-t-2 pl-1 pt-4 text-sm font-medium border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700">
        <ArrowLongLeftIcon class="mr-3 size-5 text-gray-400" aria-hidden="true" />
        Previous
      </Link>
      <div v-else></div>
    </div>
    <div v-if="numberLinks" class="hidden md:-mt-px md:flex">
      <template v-for="(link, key) in numberLinks">
        <div v-if="link.url === null" :key="key" class="inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium border-transparent text-gray-500" v-html="link.label" />
        <Link v-else preserve-scroll class="inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium" 
          :class="{ 'border-indigo-500 text-indigo-600': link.active, 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700': !link.active }" :href="link.url" v-html="link.label" />
      </template>
    </div>
    <div class="-mt-px flex w-0 flex-1 justify-end">
      <Link v-if="nextLink.url" :href="nextLink.url ?? '#'"
        preserve-scroll
        class="inline-flex items-center border-t-2 pl-1 pt-4 text-sm font-medium border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700">
        Next
        <ArrowLongRightIcon class="ml-3 size-5 text-gray-400" aria-hidden="true" />
      </Link>
      <div v-else></div>
    </div>
  </nav>
</template>