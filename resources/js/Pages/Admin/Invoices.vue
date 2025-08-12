<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { Head, usePage } from '@inertiajs/vue3'
import PageHeader from '@/Components/PageHeader.vue'
import { computed, ref } from 'vue'
import { getOrganizationPageFilters } from '@/helpers'
import PaginationNav from '@/Components/PaginationNav.vue'
import AppCard from '@/Components/AppCard.vue'
import InvoicesTable from '@/Components/InvoicesTable.vue'

const props = defineProps({
  organization: Object,
  invoices: Object,
})

const page = usePage()
const pageFilters = ref(getOrganizationPageFilters(props.organization, page.props.organizations, 'admin.invoices.index'))

const actions = computed(() => {
  return [{
    text: 'Create Invoice',
    url: route('admin.invoices.create'),
    rawLink: false,
  }]
})

</script>

<template>

  <Head title="Admin Invoices" />

  <BreezeAuthenticatedLayout>
    <template #header>
      <PageHeader header="Invoices" :filters="pageFilters" :actions="actions"></PageHeader>
    </template>

    <AppCard>
      <InvoicesTable :invoices="invoices.data" />
      <PaginationNav v-if="invoices.data.length" :pagination="invoices" class="mt-4" />
    </AppCard>

  </BreezeAuthenticatedLayout>
</template>
