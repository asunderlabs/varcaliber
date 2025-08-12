<template>
    <Head :title="title" />

    <BreezeAuthenticatedLayout :organization="organization">
        <template #header>
            <PageHeader header="Invoice" class="print:hidden"></PageHeader>
        </template>

        <template #subnav>
            <BillingNav active="invoices.index"></BillingNav>
        </template>

        <div class="bg-white px-4 md:px-6 py-3">
            <ReportHeader :title="title" :lastUpdatedAt="lastUpdate" :actions="actions">
                <template #status>
                    <div v-if="invoice.paid" class="flex flex-row space-x-1 text-xl items-center text-green-500 p-3 border border-green-200">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-check2-circle w-8 h-8" viewBox="0 0 16 16">
                            <path d="M2.5 8a5.5 5.5 0 0 1 8.25-4.764.5.5 0 0 0 .5-.866A6.5 6.5 0 1 0 14.5 8a.5.5 0 0 0-1 0 5.5 5.5 0 1 1-11 0z"/>
                            <path d="M15.354 3.354a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0l7-7z"/>
                        </svg>
                        <span>Paid</span>
                    </div>
                    <div v-else-if="invoice.payment_is_pending" class="text-xl p-3 text-gray-400 border border-gray-200">
                        Payment Pending
                    </div>
                    <div v-else-if="! invoice.paid" class="text-xl p-3 text-gray-400 border border-gray-200">
                        Not Paid
                    </div>
                </template>
            </ReportHeader>
            <PageSection>
                <Table :columns="columns" :rows="rows" :options="{cellClass: 'last:w-28 last:text-right'}"></Table>
                <div class="flex flex-row justify-end mt-6">
                    <div class="md:w-1/2 lg:w-2/5">
                        <Table :rows="totalsRows" :options="{noRowLines: true, noRowHover: true, cellClass: 'last:w-28 text-right py-1'}"></Table>
                    </div>
                </div>
            </PageSection>
            <div v-if="! invoice.paid && ! invoice.payment_is_pending && invoice.organization.stripe_customer_id && ! invoice.payments.length" class="flex justify-end items-center">
                <a :href="route('invoices.pay', invoice.id)" class="rounded bg-indigo-500 text-white text-sm px-2 py-1">Pay Invoice</a>
            </div>
            <PageSection v-if="invoice.payments.length" header="Payments">
                <Table :rows="paymentRows" :options="{cellClass: 'first:w-28', noRowLines: true, noRowHover: true}"></Table>
            </PageSection>
            <PageSection v-if="invoice.note" header="">
                <div class="bg-blue-50 text-blue-600 border border-blue-600 rounded p-3">
                    <h4 class="font-bold">Notes:</h4>
                    <p>{{ invoice.note }}</p>
                </div>
            </PageSection>
        </div>
    </BreezeAuthenticatedLayout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { Head, Link } from '@inertiajs/vue3';
import PageHeader from '@/Components/PageHeader.vue'
import ReportHeader from '@/Components/ReportHeader.vue'
import BillingNav from '@/Components/BillingNav.vue'
import PageSection from '@/Components/PageSection.vue'
import Table from '@/Components/Table.vue'
import dayjs from 'dayjs'
import { dayjsLocal } from '@/helpers';

export default {
    components: {
        BreezeAuthenticatedLayout,
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
        invoices: Array,
    },

    data() {
        return {
            columns: [
                'Items',
                'Quantity',
                'Rate',
                'Amount',
            ],
            rows: null,
            total: 0,
        }
    },

    mounted() {
        this.rows = this.rowsFromItems(this.invoice.items)
        this.total = this.invoice.total/100
    },

    computed: {
        actions() {
            let actions = []
            
            actions.push({
                text: 'View PDF',
                href: route('invoices.preview', {invoice: this.invoice.id, filename: this.invoice.filename}),
                rawLink: true,
                newTab: true
            })
            actions.push({
                text: 'Download PDF',
                href: route('invoices.download', {invoice: this.invoice.id}),
                rawLink: true
            })
            
            return actions
        },
        title() {
            return 'Invoice #' + this.invoice.number.toString().padStart(4, '0') + " - " + dayjsLocal(this.invoice.issue_at).format('MMM D, YYYY')
        },
        lastUpdate() {
            let format = 'M/D/YYYY h:mm A'
            return dayjs(this.invoice.updated_at).format(format)
        },
        totalsRows() {
            
            let values = {
                Subtotal: Math.round(this.invoice.subtotal)/100,
                Tax: Math.round(this.invoice.tax * 100)/100,
            }

            if (this.invoice.processing_fee) {
                values["Processing Fee"] = Math.round((this.invoice.processing_fee))/100
            }

            if (this.invoice.amount_discounted) {
                values.Discount = Math.round((this.invoice ? -this.invoice.amount_discounted : 0))/100
            }

            values.Total = Math.round(this.invoice.total)/100
            
            let totalPayments = 0
            if (this.invoice.payments) {
                this.invoice.payments.forEach( payment => totalPayments += payment.paid_at ? ( payment.pivot.amount ? payment.pivot.amount : payment.amount ) : 0 )
            }
            values.Payments = -totalPayments/100,
            values['Amount Due'] = Math.round((this.invoice.total - totalPayments))/100

            let rows = []

            for (let key in values) {
                let classes = ''

                if (key === 'Amount Due') {
                    classes = 'text-md font-bold'
                }

                rows.push({
                    columns: [
                        {
                            text: key,
                            classes
                        },
                        {
                            text: this.formatCurrency(values[key]),
                            classes
                        }
                    ]
                })
            }

            return rows
        },
        status() {
            return this.invoice.paid ? "Paid" : ''
        },
        statusClass() {
            if (!this.status) {
                return
            }
            
            return this.status === 'Paid' ? 'p-6 text-green-500 text-2xl font-bold' : 'p-6 text-2xl font-bold'
        },
        paymentRows() {
            let rows = []

            if (!this.invoice.payments) {
                return
            }

            this.invoice.payments.forEach(payment => {
                let amount, isApplied
                if (payment.pivot.amount) {
                    amount = payment.pivot.amount
                    isApplied = true
                } else {
                    amount = payment.amount
                }
                rows.push({
                    columns: [
                        {
                            text: dayjs.tz(payment.paid_at ? payment.paid_at : payment.created_at).tz('America/Chicago').format('MMM D, YYYY')
                        },
                        {
                            text: (new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2 })).format(amount/100),
                            badge: {
                                text: payment.paid_at ? "Complete" : "Pending",
                                class: payment.paid_at ? 'badge badge-green' : 'badge'
                            }
                        }
                    ]
                })
            })

            return rows
        }
    },

    methods: {

        rowsFromItems(items) {
            let rows = []

            // items = aggregate ? this.aggregateItems(items) : items
            items.forEach(item => {

                let quantity = ''
                quantity = item['hours'] ? (Math.round(item['hours'] * 100)/100).toFixed(2) : ''

                let amount = item['amount']/100
                this.total += amount

                rows.push({
                    columns: [
                        {
                            text: item.description,
                        },
                        {
                            text: quantity,
                        },
                        {
                            text: item.hourly_rate,
                        },
                        {
                            text: (new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2 })).format(Math.round(amount * 100)/100)
                        },
                    ]
                })
            })

            return rows
        },

        formatCurrency(amount) {
            return (new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2 })).format(amount)
        }
    }
}
</script>
