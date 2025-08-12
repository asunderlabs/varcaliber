<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { Head } from '@inertiajs/vue3'
import PageHeader from '@/Components/PageHeader.vue'
import { computed } from 'vue'
import AppCard from '@/Components/AppCard.vue'
import OrganizationsTable from '@/Components/OrganizationsTable.vue'
import PaginationNav from '@/Components/PaginationNav.vue'

const props = defineProps({
  paginatedOrganizations: Object,
})

const actions = computed(() => {
  return [{
    text: 'Create Organization',
    url: route('admin.organizations.create'),
    rawLink: false,
  }]
})

</script>

<template>

  <Head title="Admin Organizations" />

  <BreezeAuthenticatedLayout>
    <template #header>
      <PageHeader header="Organizations" :actions="actions"></PageHeader>
    </template>

    <AppCard>
      <OrganizationsTable :organizations="paginatedOrganizations.data" />
      <PaginationNav v-if="paginatedOrganizations.data.length" :pagination="paginatedOrganizations" class="mt-4" />
    </AppCard>

  </BreezeAuthenticatedLayout>
</template>
