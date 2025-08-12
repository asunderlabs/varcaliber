<script setup>
import Table from '@/Components/Table.vue';
import { computed } from 'vue';

const props = defineProps({
  users: Array
})

const columns = [
  'Name',
  'Email',
  'Organization',
  ''
]

const userRows = computed(() => {
  let rows = []

  props.users.forEach(user => {
    let actions = []

    actions = actions.concat([
      {
        'label': 'Invite',
        'href': route('admin.users.invite', { 'user': user.id }),
        'method': 'post'
      },
      {
        'label': 'View',
        'href': route('admin.users.show', { 'user': user.id })
      },
      {
        'label': 'Edit',
        'href': route('admin.users.edit', { 'user': user.id })
      },
      {
        'label': 'Delete',
        'href': route('admin.users.destroy', { 'user': user.id }),
        'confirmDelete': true
      }
    ])

    rows.push({
      columns: [
        {
          text: user.name,
        },
        {
          text: user.email
        },
        {
          badge: {
            text: user.organizations.length ? user.organizations[0].short_code : (user.is_admin ? 'Admin' : '-'),
            class: user.is_admin ? 'badge badge-green' : 'badge'
          }
        },
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
    <Table :columns="columns" :rows="userRows" :options="{ cellClass: 'py-1' }" />
  </div>

</template>