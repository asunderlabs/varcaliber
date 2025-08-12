<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { Head, usePage } from '@inertiajs/vue3'
import PageHeader from '@/Components/PageHeader.vue'
import { computed, ref } from 'vue'
import { getOrganizationPageFilters } from '@/helpers'
import PaginationNav from '@/Components/PaginationNav.vue'
import AppCard from '@/Components/AppCard.vue'
import IssuesTable from '@/Components/IssuesTable.vue'

const props = defineProps({
  organization: Object,
  issues: Object,
})

const page = usePage()
const pageFilters = ref(getOrganizationPageFilters(props.organization, page.props.organizations, 'admin.issues.index'))

const actions = computed(() => {
  return [{
    text: 'Create Issue',
    url: route('admin.issues.create'),
    rawLink: false,
  }]
})

</script>

<template>

  <Head title="Admin Issues" />

  <BreezeAuthenticatedLayout>
    <template #header>
      <PageHeader header="Issues" :filters="pageFilters" :actions="actions"></PageHeader>
    </template>

    <AppCard>
      <IssuesTable :issues="issues.data" />
      <PaginationNav v-if="issues.data.length" :pagination="issues" class="mt-4" />
    </AppCard>

  </BreezeAuthenticatedLayout>
</template>
