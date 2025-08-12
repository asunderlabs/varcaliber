<script setup>
import Table from '@/Components/Table.vue';
import { dayjsLocal } from '@/helpers';
import { computed } from 'vue';

const props = defineProps({
  emails: Array,
  mailgunLogsUrl: String
})

const columns = [
  'Date',
  'Time',
  'Status',
  'Type',
  'Organization',
  'To',
  ''
]

const emailRows = computed(() => {
  let rows = []

  props.emails.forEach(email => {
    let actions = []

    if (email.mailgun_message_id) {
      actions.push({
        'label': 'Quick View',
        'rawLink': true,
        'newTab': true,
        'href': `${props.mailgunLogsUrl}/${email.mailgun_message_id}/quick-view`
      })
    }

    const organizationColumn = () => email.organization ? { badge: { text: email.organization.short_code }} : {text: '-'}
    const toColumn = () => email.cc ? {text: email.to, subtext: "cc: " + email.cc} : {text: email.to}

    rows.push({
      columns: [
        {
          text: dayjsLocal(email.created_at).format('MMM D, YYYY'),
          classes: "whitespace-nowrap"
        },
        {
          text: dayjsLocal(email.created_at).format('h:mm a'),
          classes: "whitespace-nowrap"
        },
        {
          badge: {
            text: email.status,
            class: email.status === 'created' ? 'badge' : (email.status === 'delivered' ? 'badge badge-green' : 'badge badge-red')
          }
        },
        {
          text: email.type,
        },
        organizationColumn(),
        toColumn(),
        {
          actions
        }
      ]
    })
  })
  return rows
})

</script>

<template>

  <div>
    <Table :columns="columns" :rows="emailRows" :options="{ cellClass: 'py-1' }" />
  </div>

</template>