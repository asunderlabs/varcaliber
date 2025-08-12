<script setup>
import { ref, onMounted, computed } from 'vue'
import { dayjsLocal } from '@/helpers'
import PageSection from '@/Components/PageSection.vue'
import Table from '@/Components/Table.vue'
import dayjs from 'dayjs'
import lodash from 'lodash'
import ExpandableCard from '@/Components/ExpandableCard.vue'
import AppTooltip from '@/Components/AppTooltip.vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
  report: Object,
  showOrganization: {
    type: Boolean,
    default: false
  },
  enableAdminControls: {
    type: Boolean,
    default: false
  }
})

const total = ref(0)
const sections = ref([])

onMounted(() => {
  if (props.report) {
    sections.value = sectionsFromItems(props.report.items)

    props.report.items.forEach(item => {
      total.value += (Math.round(item.minutes / 60 * 100) / 100)
    })
  }
})

const stat = computed(() => {
  return {
    number: total.value ? total.value.toFixed(2) : '0.00',
    unit: 'hrs',
  }
})

const start = computed(() => {
  let date = props.report ? props.report.starts_at : '' //this.billingCycle['start']
  let format = 'MMM D, YYYY'
  return dayjs.tz(date).tz('America/Chicago').format(format)
})

const end = computed(() => {
  let date = props.report ? props.report.ends_at : '' //this.billingCycle['end']
  let format = 'MMM D, YYYY'
  return dayjs.tz(date).tz('America/Chicago').format(format)
})

const sectionsFromItems = (items) => {
  let sections = []
  let issuesSection = getIssuesSection(items)
  const phoneSection = getPhoneSection(items)

  if (issuesSection) {
    sections.push(issuesSection)
  }

  if (phoneSection) {
    sections.push(phoneSection)
  }

  return sections
}

const getIssuesSection = (items) => {
  let issueItems = lodash.groupBy(items.filter(item => item.issue_id), 'issue_id')

  let sectionRows = []

  if (!issueItems) {
    return
  }

  let sortedIssues = []

  for (const issueId in issueItems) {
    let issue = issueItems[issueId][0].issue
    
    sortedIssues.push({
      ...issue,
      hours: getHoursFromItems(issueItems[issueId]).toFixed(2)
    })
    
    sortedIssues.sort((a, b) => {
      if (a.hours === b.hours) {
        return 0
      }
      return a.hours < b.hours ? 1 : -1
    })
  }

  sortedIssues.forEach((issue) => {
    sectionRows.push({
      columns: [
        {
          text: issue.title
        },
        {
          text: issue.hours
        }
      ],
      reportItems: items.filter(item => item.issue_id === issue.id)
    })
  })

  return {
    header: 'Issues',
    type: 'issues',
    rows: sectionRows
  }
}

const getPhoneSection = (items) => {
  const phoneItems = items.filter(item => item.is_remote_interaction).sort((a, b) => {
    if (a.work_entry.starts_at === b.work_entry.starts_at) {
      return 0
    }
    return a.work_entry.starts_at > b.work_entry.starts_at ? 1 : -1
  })

  let sectionRows = []

  if (!phoneItems) {
    return
  }

  phoneItems.forEach(item => {
    const date = dayjs.tz(item.work_entry.starts_at).tz('America/Chicago').format('M/D')

    sectionRows.push({
      columns: [
        {
          text: `${date} - ${item.work_entry.description}`
        },
        {
          text: hoursFromMinutes(item.minutes).toFixed(2)
        }
      ]

    })
  })

  return {
    header: 'Virtual Meetings and Phone Calls',
    type: 'remote_interactions',
    rows: sectionRows
  }
}

const getHoursFromItems = (items) => {
  let hours = 0
  items.forEach(item => {
    hours += hoursFromMinutes(item.minutes)
  })
  return hours
}

const hoursFromMinutes = (minutes) => {
  return Math.round(minutes / 60 * 100) / 100
}

const handleNameClick = () => {
  if (!props.enableAdminControls) {
    return
  }

  router.visit(route('admin.reports.edit', props.report.id))
}

defineEmits(['unreport-work'])
</script>

<template>
  <ExpandableCard>
    <template v-slot:header>
      <div class="flex items-center justify-between w-full">
        <div @click="handleNameClick()" :class="{'hover:underline cursor-pointer': enableAdminControls}">
          <div class="font-bold">{{ report.name }}<span v-if="showOrganization"> - {{report.organization.name }}</span></div>
          <div class="text-sm">{{ start }} - {{ end }}</div>
        </div>
        <div class="flex items-center">
          <div class="bg-gray-50 text-gray-700 p-2 rounded w-24 text-right font-bold">
            {{ stat.number }} {{ stat.unit }}
          </div>
          <div v-if="report.is_current" class="ml-2">
            <AppTooltip>
              <div class="mb-2">
                Last updated {{ dayjsLocal(report.updated_at).format('MMMM D, YYYY') }} at {{ dayjsLocal(report.updated_at).format('h:mm a') }}
              </div>
              <hr class="mb-2">
              <div class="italic">
                <span v-if="report.report_type === 'billable_hours'">These are the total billable hours recorded for the current billing period. This number should be viewed as an estimate until your final total is reported at the end of this period.</span>
                <span v-else>The current hours reported should be viewed as an estimate. Please check your final statement at the end of the period for your exact hours.</span>
              </div>
            </AppTooltip>
          </div>
        </div>
      </div>
    </template>
    <hr class="mt-4 mb-2 opacity-40">
    <div>
      <div v-for="(section, sectionIndex) in sections" :key="sectionIndex">
        <PageSection v-if="section.rows.length">
          <h3 class="text-sm font-bold mb-3">{{ section.header }}</h3>
          <div v-if="section.type === 'issues'">
            <div v-for="row in section.rows">
              <div class="flex items-center justify-between text-sm">
                <div class="py-2">{{ row.columns[0].text }}</div>
                <div class="flex items-center">
                  <div class="py-2">{{ row.columns[1].text }}</div>
                  <div v-if="enableAdminControls" class="p-2 w-8" @click.stop="row.expanded = !row.expanded">
                    <svg v-if="row.expanded" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-up" viewBox="0 0 16 16">
                      <path fill-rule="evenodd" d="M7.646 4.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1-.708.708L8 5.707l-5.646 5.647a.5.5 0 0 1-.708-.708z"/>
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-down"
                      viewBox="0 0 16 16">
                      <path fill-rule="evenodd"
                        d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708" />
                    </svg>
                  </div>
                </div>
              </div>
              <div v-if="enableAdminControls && row.expanded" class="border-t border-gray-100 pt-3 pb-6 text-sm">
                <div v-for="reportItem in row.reportItems" class="flex items-center justify-between py-1">
                  <div class="flex items-center pl-3 space-x-2">
                    <div class="w-10 shrink-0">{{ dayjsLocal(reportItem.work_entry.starts_at).format('M/D') }}</div>
                    <div>{{ reportItem.description }}</div>
                    <div v-if="enableAdminControls">
                      <Link :href="route('admin.hours.edit', reportItem.work_entry_id)" class="text-indigo-500 underline cursor-pointer">Edit</Link>
                    </div>
                    <div v-if="enableAdminControls">
                      <div @click="$emit('unreport-work', reportItem.work_entry_id)" method="post" class="text-indigo-500 underline cursor-pointer">Unreport</div>
                    </div>
                  </div>
                  <div class="flex items-center pl-2">
                    <div>{{ (reportItem.minutes / 60).toFixed(2) }}</div>
                    <div class="w-8">&nbsp;</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <Table v-else :columns="[]" :rows="section.rows" :options="{ cellClass: 'last:w-24 last:text-right' }">
          </Table>
        </PageSection>
        <PageSection v-else>
          <h3 class="text-sm font-bold mb-3">{{ section.header }}</h3>
          <div class="text-sm">No items</div>
        </PageSection>
      </div>
    </div>
  </ExpandableCard>
</template>