<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { Head, router} from '@inertiajs/vue3'
import PageHeader from '@/Components/PageHeader.vue'
import { dayjsLocal, getOrganizationPageFilters } from '@/helpers'
import AppCard from '@/Components/AppCard.vue'

const props = defineProps({
    workEntry: Object,
    reports: Array
})

const submit = (reportId) => {
    router.post(route('admin.hours.report.store', props.workEntry.id), {report_id: reportId})
}
</script>

<template>

    <Head title="Report Work" />
  
    <BreezeAuthenticatedLayout>
      <template #header>
        <PageHeader header="Report Work"></PageHeader>
      </template>

      <h2>Work Entry</h2>

      <AppCard class="flex items-center justify-between">
        <div>
            <div>{{ workEntry.description }}</div>
            <div class="text-sm">
              {{ dayjsLocal(workEntry.starts_at).format('MMMM D, YYYY h:mm a') }} 
              </div>
        </div>
        <div class="badge">{{ (workEntry.minutes/60).toFixed(2) }} hrs</div>
      </AppCard>

      <h2>Choose a report</h2>

      <div v-if="reports.length" class="space-y-2">
          <AppCard v-for="report in reports" :key="report.id" class="flex items-center justify-between">
            <div>
                <div>{{ report.name }}</div>
                <div class="text-sm">
                    {{ dayjsLocal(report.starts_at).format('MMMM D, YYYY') }} - {{ dayjsLocal(report.ends_at).format('MMMM D, YYYY') }}
                </div>
            </div>
            <div>
                <form @submit.prevent="submit(report.id)">
                    <button type="submit" class="bg-indigo-500 text-white px-2 py-1 rounded text-sm">Add to report</button>
                </form>
            </div>
        </AppCard>
      </div>
      <div v-else>
        No reports
      </div>
  
      
    </BreezeAuthenticatedLayout>
  </template>
  