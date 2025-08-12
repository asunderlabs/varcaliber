<template>
    <Head title="Pay Invoice" />

    <SimpleLayout :organization="invoice.organization">
      <div class="w-full flex flex-col space-y-6">
        <div class="flex items-center justify-between">
          <BreezeApplicationLogo class="w-auto h-10 fill-current text-gray-500" />
          <h1 class="text-2xl font-bold">Pay Invoice</h1>
        </div>
        <div class="flex items-center justify-between border border-gray-100 rounded p-6">
          <div>
            <span class="font-bold">Invoice</span> <span class="text-gray-500">#{{ (invoice.number.toString()).padStart(4, '0') }}</span><br>
            <span class="text-sm">{{ invoice.organization.name }}</span>
          </div>
          <div>
            <div class="text-right">{{ formatCurrency(invoice.amount_due/100) }}</div>
          </div>
        </div>
        
        <div v-if="!selectedPaymentType" class="space-y-3">
          <div class="text-sm">
            Choose payment method:
          </div>
          <div class="flex flex-col md:flex-row w-full md:w-auto justify-center md:justify-start items-center space-y-6 md:space-y-0 md:space-x-3 md:h-24">
            <div v-for="paymentMethod in paymentMethods" :key="paymentMethod.id" class="payment-option relative" @click="selectedPaymentType = paymentMethod.id">
              <span>{{ paymentMethod.name }}</span>
              <span class="text-xs">({{ paymentMethod.fee_percent ? (paymentMethod.fee_percent * 100) + "% processing fee" : 'No Fee' }})</span>
              <div v-if="paymentMethod.discount" class="rounded-full text-sm bg-green-500 text-white absolute px-2 h-6 flex items-center font-bold" style="bottom: -12px;">
                Save {{ formatCurrency(paymentMethod.discount/100) }}
              </div>
            </div>
          </div>
        </div>

        <div v-if="selectedPaymentType === 'bank_account'" class="space-y-3">
          <div class="flex items-center justify-between">
            <div class="text-sm">
              Pay with Bank Account
            </div>
            <div @click="selectedPaymentType = null" class="underline text-blue-500 text-sm cursor-pointer hover:text-blue-600">
              Back
            </div>
          </div>
          <p>Sign in or create an account.</p>
          <div class="flex flex-row items-center space-x-3">
            <Link :href="`/invoices/${invoice.id}/pay`" class="block rounded bg-indigo-500 hover:bg-indigo-700 text-white py-1 px-2 text-sm cursor-pointer w-32 text-center">
              Sign in
            </Link>
            <Link :href="`/register?redirect=/invoices/${invoice.id}/pay`" class="block rounded border border-indigo-500 hover:bg-indigo-50 text-indigo-500 py-1 px-2 text-sm cursor-pointer w-32 text-center">
              Create account
            </Link>
          </div>
        </div>

        <div v-if="selectedPaymentType === 'credit_card'" class="space-y-3">
          <div class="flex items-center justify-between">
            <div class="text-sm">
              Pay with Credit Card
            </div>
            <div @click="selectedPaymentType = null" class="underline text-blue-500 text-sm cursor-pointer hover:text-blue-600">
              Back
            </div>
          </div>
          Show credit card form
        </div>

      </div>
    </SimpleLayout>
</template>

<script>
import dayjs from 'dayjs'
import BreezeApplicationLogo from '@/Components/ApplicationLogo.vue'
import SimpleLayout from '@/Layouts/Simple.vue'
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/Components/PageHeader.vue'
import ReportHeader from '@/Components/ReportHeader.vue'
import BillingNav from '@/Components/BillingNav.vue'
import PageSection from '@/Components/PageSection.vue'
import Table from '@/Components/Table.vue'
import { formatCurrency } from '@/helpers'
import { Link } from '@inertiajs/vue3';

export default {
    components: {
      BreezeApplicationLogo,
      SimpleLayout,
      Head,
      PageHeader,
      ReportHeader,
      BillingNav,
      PageSection,
      Table,
      Link,
    },

    props: {
        invoice: Object,
        paymentMethods: Array
    },

    data() {
        return {
          selectedPaymentType: null,
          selectedPaymentMethod: null,
          isSubmitting: false
        }
    },

    computed: {
      step() {
        if (this.selectedPaymentType === null) {
          return 1;
        }

        if (this.selectedPaymentType !== null) {
          return 2;
        }
      },
      usablePaymentMethods() {
          return this.invoice.organization.stripe_payment_methods.filter( method => !method.expired )
      },
      processingFee() {
          if (!this.selectedPaymentMethod) {
              return 0
          }

          return this.paymentMethodIsCard ? Math.round(this.invoice.amount_due * this.$page.props.settings.card_processing_fee_percent) : 0
      },
      paymentMethodIsCard() {
          if (!this.selectedPaymentMethod) {
              return null
          }
          return this.paymentMethods.find( paymentMethod => paymentMethod.id === this.selectedPaymentMethod).type === 'card'
      },
      paymentAmount() {
          return this.invoice.amount_due + this.processingFee
      }
    },

    methods: {
        submit() {
            if (this.isSubmitting) {
                return
            }
            if (!this.selectedPaymentMethod) {
                return
            }
            this.isSubmitting = true
            this.$inertia.post(`/invoices/${this.invoice.id}/pay`, {
                payment_method_id: this.selectedPaymentMethod,
                processing_fee: this.processingFee,
            })
        },
        formatCurrency
    }
}
</script>
