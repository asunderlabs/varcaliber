<script setup>
import Table from '@/Components/Table.vue';
import { computed } from 'vue';
import HoursColumn from '@/Components/HoursColumn.vue'
import { dayjsLocal } from '@/helpers';

const props = defineProps({
  invoices: Array
})

const columns = [
  'Issue At',
  'Status',
  'Number',
  'Organization',
  'Amount',
  ''
]

const invoiceRows = computed(() => {
  let rows = []

  props.invoices.forEach(invoice => {

    let isDelivered = invoice.delivered_at ? true : false
    let isApproved = invoice.approved_at ? true : false
    let actions = []

    const editAction = {
      'label': 'Edit',
      'href': route('admin.invoices.edit', invoice.id)
    }

    const deleteAction = {
      'label': 'Delete',
      'href': route('admin.invoices.destroy', invoice.id),
      'confirmDelete': "Delete invoice?"
    }

    if (isDelivered) {
      if (!invoice.paid) {
        actions.push({
          'label': 'Add Payment',
          'href': route('admin.payments.create', invoice.id)
        })
      }

      actions.push(editAction)

      if (invoice.manual_delivery) {
        actions.push(deleteAction)
      }
    } else if (!isDelivered && isApproved) {
      actions.push({
        'label': 'Cancel',
        'href': route('admin.invoices.cancelApproval', invoice.id),
        'method': 'post'
      })
    } else {
      actions.push({
        'label': 'Approve',
        'href': route('admin.invoices.approve', invoice.id),
        'method': 'post'
      })

      actions.push(editAction)
      actions.push(deleteAction)
    }

    rows.push({
      columns: [
        {
          text: dayjsLocal(invoice.issue_at).format('MMM D, YYYY')
        },
        {
          badge: {
            text: invoice.organization.short_code
          }
        },
        {
          text: isDelivered ? (invoice.manual_delivery ? 'Manual Delivery' : 'Delivered') : (isApproved ? 'Awaiting delivery' : 'Awaiting approval'),
          classes: isApproved || invoice.manual_delivery ? '' : "text-orange-500"
        },
        {
          actions: [{
            'label': '#' + invoice.invoice_number,
            'href': route('admin.invoices.preview', { invoice: invoice.id, filename: invoice.filename }),
            'rawLink': true,
            'newTab': true
          }],
        },
        {
          text: "$" + (invoice.total / 100).toLocaleString('en-US'),
          textClasses: 'justify-end'
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
    <Table :columns="columns" :rows="invoiceRows" :options="{ cellClass: 'md:first:w-32 py-1' }" />
  </div>

</template>