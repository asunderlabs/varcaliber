<script setup>
import { computed } from 'vue'
import { dayjsLocal } from '@/helpers'
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { Head } from '@inertiajs/vue3';
import BillingNav from '@/Components/BillingNav.vue'
import PageSection from '@/Components/PageSection.vue'
import Table from '@/Components/Table.vue'
import PaginationNav from '@/Components/PaginationNav.vue';


const props = defineProps({
  organization: Object,
  invoices: Object,
})

const columns = computed(() => {
  return [
    'Date',
    'Invoice',
    'Amount',
  ]
})

const rows = computed(() => {
  if (!props.invoices?.data) {
    return
  }

  let rows = []
  props.invoices.data.forEach(invoice => {

    let actions = [
      {
        label: "#" + invoice.number.toString().padStart(4, '0'),
        href: route('invoices.show', invoice.id)
      }
      // {
      //   label: 'View PDF',
      //   href: route('invoices.preview', { invoice: invoice.id, filename: invoice.filename }),
      //   rawLink: true,
      //   newTab: true
      // },
      // {
      //   label: 'Download PDF',
      //   href: route('invoices.download', { invoice: invoice.id }),
      //   rawLink: true
      // }
    ]

    if (!invoice.paid && !invoice.payment_is_pending && props.organization.stripe_customer_id && !invoice.payments.length) {
      actions.push({
        label: 'Pay Now',
        href: route('invoices.pay', { invoice: invoice.id }),
        rawLink: true,
        classes: "hidden md:block"
      })
    }

    let columns = [
      {
        text: dayjsLocal(invoice.issue_at).format('MM/DD/YYYY'),
        badge: {
          text: invoice.paid ? "PAID" : (invoice.payment_is_pending ? 'PAYMENT PENDING' : "DUE "),
          class: invoice.paid ? 'badge badge-green' : 'badge'
        }
      },
      {
        actions
      },
      {
        text: (new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2 })).format(invoice.total / 100),
        textClasses: "justify-end"
      },
    ]

    rows.push({ columns })
  })
  return rows

})

</script>

<template>

  <Head title="Invoices" />

  <BreezeAuthenticatedLayout :organization="organization">
    <template #subnav>
      <BillingNav active="invoices.index"></BillingNav>
    </template>

    <div class="bg-white px-4 md:px-6 py-3">
      <PageSection header="Invoices">
        <Table :columns="columns" :rows="rows" :options="{ cellClass: 'first:w-32 last:w-24' }">
        </Table>
        <PaginationNav v-if="invoices.data.length" :pagination="invoices" class="mt-4" />
      </PageSection>
    </div>
  </BreezeAuthenticatedLayout>
</template>
