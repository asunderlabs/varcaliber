<script setup>
import Table from '@/Components/Table.vue';
import { dayjsLocal } from '@/helpers';
import { computed } from 'vue';

const props = defineProps({
  issues: Array
})

const columns = [
  'Date',
  'Organization',
  'Issue Title',
  'Hours',
]

const issueRows = computed(() => {
  let rows = []

  props.issues.forEach(issue => {
    let actions = [
      {
        label: 'Edit',
        href: route('admin.issues.edit', issue.id),
      },
      {
        label: issue.archived_at ? 'Unarchive' : 'Archive',
        href: issue.archived_at ? route('admin.issues.unarchive', issue.id) : route('admin.issues.archive', issue.id),
        method: issue.archived_at ? 'DELETE' : 'POST'
      }
    ]

    rows.push({
      columns: [
        {
          text: dayjsLocal(issue.created_at).format('MMM D, YYYY')
        },
        {
          badge: {
            text: issue.organization.short_code,
          }
        },
        {
          text: issue.title,
        },
        {
          text: issue.hours,
        },
        { actions }
      ]
    })
  })
  return rows
})

</script>

<template>

  <div>
    <Table :columns="columns" :rows="issueRows" :options="{ cellClass: 'md:first:w-32 py-1 md:last:w-32' }" />
  </div>

</template>