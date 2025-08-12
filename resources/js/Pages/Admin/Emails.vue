<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { Head } from '@inertiajs/vue3'
import PageHeader from '@/Components/PageHeader.vue'
import PaginationNav from '@/Components/PaginationNav.vue'
import AppCard from '@/Components/AppCard.vue'
import EmailsTable from '@/Components/EmailsTable.vue'

const props = defineProps({
  emails: Object,
})

</script>

<template>

  <Head title="Emails" />

  <BreezeAuthenticatedLayout>
    <template #header>
      <PageHeader header="Emails"></PageHeader>
    </template>

    <AppCard>
      <EmailsTable :emails="emails.data" />
      <PaginationNav v-if="emails.data.length" :pagination="emails" class="mt-4" />
    </AppCard>

  </BreezeAuthenticatedLayout>
</template>
