<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { Head } from '@inertiajs/vue3'
import PageHeader from '@/Components/PageHeader.vue'
import { computed } from 'vue'
import AppCard from '@/Components/AppCard.vue'
import OrganizationsTable from '@/Components/OrganizationsTable.vue'
import PaginationNav from '@/Components/PaginationNav.vue'
import UsersTable from '@/Components/UsersTable.vue'

const props = defineProps({
  users: Object,
})

const actions = computed(() => {
  return [{
    text: 'Create User',
    url: route('admin.users.create'),
    rawLink: false,
  }]
})

</script>

<template>

  <Head title="Users" />

  <BreezeAuthenticatedLayout>
    <template #header>
      <PageHeader header="Users" :actions="actions"></PageHeader>
    </template>

    <AppCard>
      <UsersTable :users="users.data" />
      <PaginationNav v-if="users.data.length" :pagination="users" class="mt-4" />
    </AppCard>

  </BreezeAuthenticatedLayout>
</template>
