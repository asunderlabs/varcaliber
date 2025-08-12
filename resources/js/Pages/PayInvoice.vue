<template>
    <Head title="Pay Invoice" />

    <SimpleLayout :organization="invoice.organization">
        <div class="w-full flex flex-col space-y-6 relative">
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

            <div v-if="! selectedPaymentMethod" class="space-y-3">
                <div class="text-sm">
                    Choose payment method:
                </div>
                <div v-if="! paymentMethods.length" class="bg-gray-50 text-gray-500 w-full p-6 space-x-2 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                        <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                    </svg>
                    <span>Add a payment method to get started</span>
                </div>
                <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div v-for="paymentMethod in paymentMethods" :key="paymentMethod.id" class="payment-option relative w-full h-32 space-y-3" @click="selectedPaymentMethod = paymentMethod.id">
                        <div class="flex flex-col items-center w-full">
                            <span class="text-sm text-center font-bold whitespace-nowrap w-full overflow-hidden">{{ paymentMethod.name }}</span>
                            <span class="text-sm text-gray-500">(x{{ paymentMethod.last4 }})</span>
                        </div>
                        <div v-if="paymentMethod.type === 'card'" class="badge">
                            {{ ($page.props.settings.card_processing_fee_percent * 100).toFixed(1) + "% fee" }}
                        </div>
                        <div v-if="paymentMethod.type === 'us_bank_account' && invoice.pay_by_bank_discount" class="badge badge-green-inverted font-bold">
                            Save {{ formatCurrency(invoice.pay_by_bank_discount/100) }}
                        </div>
                    </div>
                </div>
                <div>
                    <a :href="route('paymentMethods.create', {method: 'bank_acount', redirectAfter: url})" class="text-indigo-500 underline text-sm">Add payment method</a>
                </div>
            </div>
                 
            <div v-else class="p-6 space-y-6">
                <table class="w-full">
                    <tbody>
                        <tr>
                            <td class="w-48 text-sm">Pay With</td>
                            <td class="text-right">{{ selectedPaymentMethodDescription }}</td>
                        </tr>
                        <tr>
                            <td align="right" colspan="2">
                                <div @click="selectedPaymentMethod = null" class="underline text-blue-500 text-sm cursor-pointer hover:text-blue-600">
                                    Reset method
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td class="text-sm">Subtotal</td>
                            <td class="text-right">{{ formatCurrency(invoice.amount_due/100) }}</td>
                        </tr>
                        <tr>
                            <td class="text-sm">Processing Fee</td>
                            <td class="text-right">{{ formatCurrency(processingFee/100) }}</td>
                        </tr>
                        <tr v-if="discount">
                            <td class="text-sm">Discount</td>
                            <td class="text-right">-{{ formatCurrency(discount/100) }}</td>
                        </tr>
                        <tr>
                            <td class="text-sm">Total</td>
                            <td class="text-right">{{ formatCurrency((paymentAmount)/100) }}</td>
                        </tr>
                    </tbody>
                </table>
                <div class="rounded-lg flex items-center justify-end" style="margin-right: -1.5rem">
                    <div class="border border-gray-100 rounded p-3 flex items-center space-x-3">
                        <span class="px-3 text-lg font-bold text-gray-600">{{ formatCurrency((paymentAmount)/100) }}</span>
                        <form @submit.prevent="submit">
                            <button type="submit" class="rounded bg-indigo-500 text-white py-3 px-6" :class="{'opacity-50 cursor-default': !selectedPaymentMethod || isSubmitting, 'hover:bg-indigo-600': selectedPaymentMethod && !isSubmitting}">Pay Now</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-center space-x-2 pt-6">
                <Link :href="route('invoices.show', invoice.id)" class="rounded bg-gray-50 hover:bg-gray-100 p-2 text-sm">Return to Invoice</Link>
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
        Link
    },

    props: {
        organization: Object,
        invoice: Object,
    },

    data() {
        return {
            selectedPaymentMethod: null,
            isSubmitting: false
        }
    },

    computed: {
        paymentMethods() {
            const methods = this.organization.stripe_payment_methods.filter( method => !method.expired )
            const bankAccounts = methods.filter( method => method.type === 'us_bank_account')
            return bankAccounts.concat(methods.filter( method => method.type === 'card'))
        },
        processingFee() {
            if (!this.selectedPaymentMethod) {
                return 0
            }

            return this.paymentMethodIsCard ? Math.round(this.invoice.amount_due * this.$page.props.settings.card_processing_fee_percent) : 0
        },
        discount() {
            return this.paymentMethodIsBankAccount && this.invoice.pay_by_bank_discount ? this.invoice.pay_by_bank_discount : 0
        },
        paymentMethodIsCard() {
            if (!this.selectedPaymentMethod) {
                return null
            }
            return this.paymentMethods.find( paymentMethod => paymentMethod.id === this.selectedPaymentMethod).type === 'card'
        },
        paymentMethodIsBankAccount() {
            if (!this.selectedPaymentMethod) {
                return null
            }
            return this.paymentMethods.find( paymentMethod => paymentMethod.id === this.selectedPaymentMethod).type === 'us_bank_account'
        },
        selectedPaymentMethodDescription() {
            if (! this.selectedPaymentMethod) {
                return ''
            }
            const method = this.paymentMethods.find( method => method.id === this.selectedPaymentMethod)
            return `${method.name} (x${method.last4})`
        },
        paymentAmount() {
            return this.invoice.amount_due + this.processingFee - this.discount
        },
        url() {
            return window.location.href
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
            this.$inertia.post(this.route('invoices.confirmPay', this.invoice.id ), {
                payment_method_id: this.selectedPaymentMethod,
                processing_fee: this.processingFee,
            })
        },
        formatCurrency
    }
}
</script>
