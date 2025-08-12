<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { Head, usePage } from '@inertiajs/vue3'
import PageHeader from '@/Components/PageHeader.vue'
import { computed, ref } from 'vue'
import { getOrganizationPageFilters } from '@/helpers'
import HoursTable from '@/Components/HoursTable.vue'
import PaginationNav from '@/Components/PaginationNav.vue'
import AppCard from '@/Components/AppCard.vue'

const props = defineProps({
  organization: Object,
  workEntries: Object,
})

const page = usePage()
const pageFilters = ref(getOrganizationPageFilters(props.organization, page.props.organizations, 'admin.hours.index'))

const actions = computed(() => {
  return [{
    text: 'Add Work',
    url: route('admin.hours.create'),
    rawLink: false,
  }]
})

</script>

<template>

  <Head title="Admin Hours" />

  <BreezeAuthenticatedLayout>
    <template #header>
      <PageHeader header="Hours" :filters="pageFilters" :actions="actions"></PageHeader>
    </template>

    <AppCard>
      <HoursTable :workEntries="workEntries.data" />
      <PaginationNav v-if="workEntries.data.length" :pagination="workEntries" class="mt-4" />
    </AppCard>

  </BreezeAuthenticatedLayout>
</template>
