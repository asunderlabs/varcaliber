<script setup>
import { computed, ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import PageHeader from '@/Components/PageHeader.vue'
import HoursReport from '@/Components/HoursReport.vue'
import PaginationNav from '@/Components/PaginationNav.vue';
import { getOrganizationPageFilters } from '@/helpers';
import AppCard from '@/Components/AppCard.vue';
import HoursTable from '@/Components/HoursTable.vue';

const props = defineProps({
  workEntries: Array,
  organization: Object,
  currentReports: Array,
  pastReports: Object
})

const page = usePage()
const isAdmin = computed(() => page.props.auth.user.is_admin === 1)
const activeOrganization = computed(() => page.props.activeOrganization)
const pageFilters = ref(getOrganizationPageFilters(props.organization, page.props.organizations, 'reports.index'))

const actions = computed(() => {
  let a = []

  if (isAdmin.value) {
    a.push({
      text: 'Create Report',
      url: route('admin.reports.create', {organization: props.organization?.id}),
      rawLink: false,
    })
  }

  return a
})

const handleUnreportWork = (workEntryId) => {
  // Have to not preserve state in order for the report to be updated without reload
  router.post(route('admin.hours.unreport', workEntryId), null, { preserveState: false })
}
</script>
<template>

  <Head title="Reports" />

  <BreezeAuthenticatedLayout :organization="organization">
    <template #header>
      <PageHeader header="Reports" :filters="isAdmin && !activeOrganization ? pageFilters : null" :actions="actions">
      </PageHeader>
    </template>

    <div v-if="isAdmin">
      <h2 class="text-xl mt-6 mb-4">Unreported Work</h2>
      <AppCard v-if="workEntries">
        <HoursTable :workEntries="workEntries" />
      </AppCard>
      <div v-else class="py-3 px-4 bg-white rounded text-gray-500">
        No unreported work entries
      </div>
    </div>

    <div>
      <h2 class="text-xl mt-6 mb-4">Current Reports</h2>

      <div v-if="currentReports.length" class="space-y-2">
        <HoursReport v-for="report in currentReports" :key="report.id" :report="report"
          :showOrganization="isAdmin && !activeOrganization" :enableAdminControls="isAdmin"
          :allow-work-entry-edits="true" @unreport-work="handleUnreportWork" />
      </div>
      <div v-else class="py-3 px-4 bg-white rounded text-gray-500">
        There are no current reports at this time
      </div>
    </div>

    <div class="pb-12">
      <h2 class="text-xl mt-6 mb-4">Past Reports</h2>
      <div v-if="pastReports?.data?.length" class="space-y-2">
        <HoursReport v-for="pastReport in pastReports.data" :key="pastReport.id" :report="pastReport"
          :showOrganization="isAdmin && !activeOrganization" :enableAdminControls="isAdmin"
          @unreport-work="handleUnreportWork" />
        <PaginationNav :pagination="pastReports" class="mt-4" />
      </div>
      <div v-else class="py-3 px-4 bg-white rounded text-gray-500">
        There are no past reports at this time
      </div>
    </div>

  </BreezeAuthenticatedLayout>
</template>
