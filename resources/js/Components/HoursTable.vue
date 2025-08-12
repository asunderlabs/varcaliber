<script setup>
import Table from '@/Components/Table.vue';
import { computed } from 'vue';
import HoursColumn from '@/Components/HoursColumn.vue'

const props = defineProps({
  workEntries: Array
})

const unreportedWork = computed(() => {
  let rows = []
  let workEntries = JSON.parse(JSON.stringify(props.workEntries))

  workEntries.forEach(workEntry => {
    let actions = [
        {
          label: 'Edit',
          href: route('admin.hours.edit', workEntry.id),
        },
      ]

    if (workEntry.report_item_id) {
      actions.push({
        label: 'Unreport',
        href: route('admin.hours.unreport', workEntry.id),
        method: 'post'
      })
    } else {
      actions.push({
        label: 'Report',
        href: route('admin.hours.report.create', workEntry.id),
      })
    }

    rows.push({
      columns: [
        {
          component: HoursColumn,
          data: { workEntry }
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
    <Table :rows="unreportedWork" :options="{ cellClass: 'py-4 md:last:w-32' }"/>
  </div>

</template>