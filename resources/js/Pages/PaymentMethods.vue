<template>
  <Head title="Bills" />

  <BreezeAuthenticatedLayout :organization="organization">

    <template #subnav>
      <BillingNav active="paymentMethods"></BillingNav>
    </template>

    <div class="bg-white px-4 md:px-6 py-3">
      <PageSection header="Payment Methods" :actions="actions">
        <div v-if="!paymentsEnabled" class="flex space-x-2 bg-red-50 text-red-800 p-4 rounded">
          Electronic payments are not enabled for your account.
        </div>
        <div v-else>
          <div v-if="organization.stripe_payment_methods.length === 0">
            No payment methods
          </div>
          <div v-else class="space-y-2">
            <div v-for="paymentMethod in organization.stripe_payment_methods" :key="paymentMethod.id" class="p-4 rounded-lg bg-white flex items-center justify-between border border-gray-200 hover:shadow-lg hover:border-indigo-500">
              <div>
                <div class="flex items-center space-x-2">
                  <div>
                    {{ paymentMethod.name }}
                  </div>
                  <div class="text-xs">
                    (x{{ paymentMethod.last4 }})
                  </div>
                  <div v-if="paymentMethod.exp_month && paymentMethod.exp_year" class="text-sm text-gray-500" :class="{'text-red-500': paymentMethod.expired}" >
                    - {{ paymentMethod.expired ? 'Expired' : 'Expires' }} {{ paymentMethod.exp_month }}/{{  paymentMethod.exp_year }}
                  </div>
                </div>
                <div class="text-sm text-gray-500">
                  {{ paymentMethodType(paymentMethod.type) }}
                </div>
              </div>
              <div class="flex items-center space-x-4">
                <form @submit.prevent="deletePaymentMethod(paymentMethod)">
                  <button type="submit" class="rounded bg-red-100 hover:bg-red-500 text-red-600 hover:text-white p-2 text-sm">Delete</button>
                </form> 
              </div>
            </div>
          </div>
        </div>
      </PageSection>
    </div>
  </BreezeAuthenticatedLayout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { Head, Link } from '@inertiajs/vue3';
import PageSection from '@/Components/PageSection.vue'
import BillingNav from '@/Components/BillingNav.vue'

export default {
    components: {
        BreezeAuthenticatedLayout,
        Head,
        PageSection,
        BillingNav,
        Link
    },

    props: {
        organization: Object
    },

    data() {
      return {
        actions: null,
      }
    },

    created() {
      if (this.paymentsEnabled) {
        this.actions = [{
          'text': 'Add Payment Method',
          'url': route('paymentMethods.create'),
          'rawLink': true
        }]
      }
    },

    mounted() {
      
    },

    computed: {
      paymentsEnabled() {
        return this.organization.stripe_customer_id
      }
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
