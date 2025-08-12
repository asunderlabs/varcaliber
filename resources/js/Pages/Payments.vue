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
  payments: Object,
})

const columns = computed(() => {
  return [
    'Date',
    'Description',
    'Amount',
  ]
})

const rows = computed(() => {
  if (!props.payments?.data) {
    return
  }

  const pendingPayments = props.payments.data.filter(payment => !payment.paid_at)
  const payments = pendingPayments.concat(props.payments.data.filter(payment => payment.paid_at))

  let rows = []
  payments.forEach(payment => {

    let columns = []

    columns.push({
      text: payment.paid_at ? dayjsLocal(payment.paid_at).format('MM/DD/YYYY') : '-',
      badge: {
          text: payment.paid_at ? 'COMPLETED' : 'PENDING',
          class: payment.paid_at ? 'hidden md:block badge badge-green' : 'hidden md:block badge'
        }
    })

    if (payment.invoices) {
      let actions = []

      payment.invoices.forEach(invoice => {
        actions.push({
          label: "Invoice #" + invoice.number.toString().padStart(4, '0'),
          href: route('invoices.show', invoice.id),
        })
      })
      columns.push({ 
        actions,
       })
    } else {
      columns.push({
        text: ''
      })
    }

    columns.push({
      text: (new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2 })).format(Math.round(payment.amount) / 100),
      textClasses: "justify-end"
    })

    rows.push({ columns })
  })
  return rows
})

</script>

<template>

  <Head title="Payments" />

  <BreezeAuthenticatedLayout :organization="organization">
    <template #subnav>
      <BillingNav active="payments"></BillingNav>
    </template>

    <div class="bg-white px-4 md:px-6 py-3">
      <PageSection header="Payments">
        <Table :columns="columns" :rows="rows" :options="{ cellClass: 'first:w-32 last:w-32' }"></Table>
        <PaginationNav v-if="payments.data.length" :pagination="payments" class="mt-4" />
      </PageSection>
    </div>
  </BreezeAuthenticatedLayout>
</template>
