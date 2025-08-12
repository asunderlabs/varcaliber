<template>
    <Head title="Bills" />

    <BreezeAuthenticatedLayout :organization="organization">
        <template #header>
            <PageHeader header="Payment Methods" class="print:hidden"></PageHeader>
        </template>

        <template #subnav>
            <BillingNav active="paymentMethods"></BillingNav>
        </template>

        <div class="flex space-x-2">
          <a :href="route('paymentMethods.create')" class="rounded bg-indigo-500 text-white p-2 text-sm">Add Payment Method</a>
        </div>

        <div v-if="organization.stripe_payment_methods.length === 0">
          No payment methods
        </div>
        <div v-else class="space-y-2">
          <div v-for="paymentMethod in organization.stripe_payment_methods" :key="paymentMethod.id" class="p-4 rounded-lg bg-white flex items-center justify-between">
            <div>
              <div class="flex items-center space-x-2">
                <div>
                  {{ paymentMethod.name }}
                </div>
                <div class="text-xs">
                  (x{{ paymentMethod.last4 }})
                </div>
              </div>
              <div class="text-sm text-gray-500">
                {{ paymentMethodType(paymentMethod.type) }}
              </div>
            </div>
            <div class="flex items-center space-x-4">
              <div v-if="paymentMethod.exp_month && paymentMethod.exp_year" class="text-sm text-gray-500" :class="{'text-red-500': paymentMethod.expired}" >
                {{ paymentMethod.expired ? 'Expired' : 'Expires' }} {{ paymentMethod.exp_month }}/{{  paymentMethod.exp_year }}
              </div>
              <form @submit.prevent="deletePaymentMethod(paymentMethod)">
                <button type="submit" class="rounded bg-red-100 hover:bg-red-500 text-red-600 hover:text-white p-2 text-sm">Delete</button>
              </form> 
            </div>
          </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { Head, Link } from '@inertiajs/vue3';
import PageHeader from '@/Components/PageHeader.vue'
import ReportHeader from '@/Components/ReportHeader.vue'
import BillingNav from '@/Components/BillingNav.vue'

export default {
    components: {
        BreezeAuthenticatedLayout,
        Head,
        PageHeader,
        ReportHeader,
        BillingNav,
        Link
    },

    props: {
        organization: Object
    },

    data() {
        return {
            //
        }
    },

    mounted() {

    },

    computed: {
        
    },

    methods: {
      paymentMethodType(string) {
        if (string === 'us_bank_account') {
          return 'US Bank Account'
        }
         
        if (string === 'card') {
          return 'Card'
        }
      },
      deletePaymentMethod(paymentMethod) {
        this.$inertia.delete(`/paymentMethods/${paymentMethod.id}`)
      }
    }
}
</script>
