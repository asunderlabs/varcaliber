<script setup>
import Table from '@/Components/Table.vue';
import { dayjsLocal } from '@/helpers';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  organizations: Array
})

const page = usePage()

const columns = [
  'Name',
  'Stripe Customer ID',
  '',
]

const organizationRows = computed(() => {
  let rows = []

  props.organizations.forEach(organization => {
    let actions = []

    if (!organization.stripe_customer_id) {
      actions.push({
        label: 'Enable Stripe',
        href: route('admin.organizations.enableStripe', { organization: organization.id }),
        method: 'post'
      })
    }

    if (organization.can_view_stats) {
      actions.push({
        label: 'Stats',
        href: route('admin.organizations.show', { organization: organization.id })
      })
    }

    actions = actions.concat([
      {
        label: 'Edit',
        href: route('admin.organizations.edit', { organization: organization.id })
      },
      {
        label: 'Go to dashboard',
        href: route('admin.changeOrganization', { organization_id: organization.id }),
        method: 'post'
      }
    ])

    rows.push({
      columns: [
        {
          text: organization.name,
        },
        {
          badge: {
            text: organization.stripe_customer_id ? `${organization.stripe_customer_id}` : 'Stripe Disabled',
            class: organization.stripe_customer_id ? 'badge badge-green' : 'badge'
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
    <Table :columns="columns" :rows="organizationRows" :options="{ cellClass: 'py-1' }" />
  </div>

</template>