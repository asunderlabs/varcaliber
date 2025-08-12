<template>
    <Head title="Bills" />

    <BreezeAuthenticatedLayout :organization="organization">
        <template #header>
            <PageHeader header="Bills" :filters="pageFilters" class="print:hidden"></PageHeader>
        </template>

        <template #subnav>
            <BillingNav active="bills"></BillingNav>
        </template>

        <div class="bg-white px-4 md:px-6 py-3">
            <ReportHeader :title="reportHeaderTitle" :periodStart="start" :periodEnd="end" :lastUpdatedAt="lastUpdate" :actions="actions">
                <template #status>
                    <div v-if="invoice && invoice.paid" class="flex flex-row space-x-1 text-xl items-center text-green-500 p-3 border border-green-200">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-check2-circle w-8 h-8" viewBox="0 0 16 16">
                            <path d="M2.5 8a5.5 5.5 0 0 1 8.25-4.764.5.5 0 0 0 .5-.866A6.5 6.5 0 1 0 14.5 8a.5.5 0 0 0-1 0 5.5 5.5 0 1 1-11 0z"/>
                            <path d="M15.354 3.354a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0l7-7z"/>
                        </svg>
                        <span>Paid</span>
                    </div>
                    <div v-else-if="invoice && invoice.payment_is_pending" class="text-xl p-3 text-gray-400 border border-gray-200">
                        Payment Pending
                    </div>
                    <div v-else-if="invoice && !invoice.paid" class="text-xl p-3 text-gray-400 border border-gray-200">
                        Not Paid
                    </div>
                </template>
            </ReportHeader>
            <PageSection>
                <Table :columns="columns" :rows="rows" :options="{cellClass: 'last:w-28 last:text-right'}"></Table>
                <div class="flex flex-row justify-end mt-6">
                    <div class="md:w-1/2 lg:w-2/5">
                        <Table :rows="totalsRows" :options="{noRowLines: true, noRowHover: true, cellClass: 'last:w-28 text-right py-1'}"></Table>
                        <p class="text-xs text-gray-400 text-left mt-2">{{ invoice ? '' : "Actual amount will be on your invoice once it is generated" }}</p>
                    </div>
                </div>
            </PageSection>
            <div v-if="invoice && !invoice.paid && !invoice.payment_is_pending && organization.stripe_customer_id && !invoice.payments.length" class="flex justify-end items-center">
                <a :href="route('invoices.pay', invoice.id)" class="rounded bg-indigo-500 text-white text-sm px-2 py-1">Pay Invoice</a>
            </div>
            <PageSection v-if="invoice && invoice.payments.length" header="Payments">
                <Table :rows="paymentRows" :options="{cellClass: 'first:w-28', noRowLines: true, noRowHover: true}"></Table>
            </PageSection>
            <PageSection v-if="invoice && invoice.note" header="">
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
        billingCycle: Object,
        invoice: Object,
        report: Object,
        items: Array,
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
            pageFilters: [
                {
                    selected: {
                        text: 'Current Period'
                    },
                    selectable: []
                }
            ]
        }
    },

    mounted() {

        if (this.invoices) {
            this.invoices.forEach(invoice => {
                this.pageFilters[0].selectable.push({
                    text: "#" + invoice.number.toString().padStart(4, '0') + " - " + dayjs.tz(invoice.issue_at).tz('America/Chicago').format('MMM D, YYYY'),
                    href: route('bills', dayjs.tz(invoice.issue_at).tz('America/Chicago').format('YYYY-MM-DD'))
                })
            })
        }
        
        if (this.report) {
            this.rows = this.rowsFromItems(this.items)
        } else if (this.invoice) {
            this.rows = this.rowsFromItems(this.invoice.items)

            this.pageFilters[0].selected.text = "#" + this.invoice.number.toString().padStart(4, '0') + " - " + dayjs.tz(this.invoice.issue_at).tz('America/Chicago').format('MMM D, YYYY')

            this.pageFilters[0].selectable.unshift({
                text: 'Current Period',
                href: route('bills')
            })
        }
        
        this.total = this.invoice ? this.invoice.total/100 : this.total
    },

    computed: {
        actions() {
            let actions = []
            
            if (this.invoice) {
                actions.push({
                    text: 'Invoice #' + this.invoice.number.toString().padStart(4, '0'),
                    href: route('invoices.preview', {invoice: this.invoice.id, filename: this.invoice.filename}),
                    rawLink: true,
                    newTab: true
                })
                actions.push({
                    text: 'Download',
                    href: route('invoices.download', {invoice: this.invoice.id}),
                    rawLink: true
                })
            } else {
                actions.push({
                    text: 'Print',
                    callback: this.print
                })
            }
            return actions
        },
        reportHeaderTitle() {
            return this.invoice ? 'Bill for ' + dayjs.tz(this.invoice.issue_at).tz('America/Chicago').format('MMMM D, YYYY') : 'Current Bill'
        },
        start() {
            let format = 'MM/DD/YY'
            return this.invoice ? dayjs.tz(this.invoice.billing_start).tz('America/Chicago').format(format) : dayjs.tz(this.billingCycle['start']).tz('America/Chicago').format(format)
        },
        end() {
            let format = 'MM/DD/YY'
            return this.invoice ? dayjs.tz(this.invoice.billing_end).tz('America/Chicago').format(format) : dayjs.tz(this.billingCycle['end']).tz('America/Chicago').format(format)
        },
        lastUpdate() {
            let format = 'M/D/YYYY h:mm A'

            if (this.report) {
                return dayjs(this.report.updated_at).format(format)
            } else if (this.invoice) {
                return dayjs(this.invoice.updated_at).format(format)
            }
        },
        totalsRows() {
            
            let values = {
                Subtotal: Math.round((this.invoice ? this.invoice.subtotal : this.total * 100))/100,
                Tax: Math.round((this.invoice ? this.invoice.tax : 0) * 100)/100,
            }

            if (this.invoice && this.invoice.processing_fee) {
                values["Processing Fee"] = Math.round((this.invoice.processing_fee))/100
            }

            if (this.invoice && this.invoice.amount_discounted) {
                values.Discount = Math.round((this.invoice ? -this.invoice.amount_discounted : 0))/100
            }

            values.Total = Math.round((this.invoice ? this.invoice.total : this.total * 100))/100
            
            if (this.invoice) {
                let totalPayments = 0
                if (this.invoice.payments) {
                    this.invoice.payments.forEach( payment => totalPayments += payment.paid_at ? ( payment.pivot.amount ? payment.pivot.amount : payment.amount ) : 0 )
                }
                values.Payments = -totalPayments/100,
                values['Amount Due'] = Math.round((this.invoice.total - totalPayments))/100
            }

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
            if (!this.invoice) {
                return
            }

            return this.invoice.paid ? "Paid" : ''
        },
        statusClass() {
            if (!this.status) {
                return
            }
            
            return this.status === 'Paid' ? 'p-6 text-green-500 text-2xl font-bold' : 'p-6 text-2xl font-bold'
        },
        statusLink() {
            if (!this.invoice) {
                return
            }

            // TODO: return link to view receipt/payment record
        },
        paymentRows() {
            let rows = []

            if (!this.invoice || !this.invoice.payments) {
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
                if (this.invoice) {
                    quantity = item['hours'] ? (Math.round(item['hours'] * 100)/100).toFixed(2) : ''
                } else {
                    quantity = (Math.round(item['minutes'] / 60 * 100)/100).toFixed(2)
                }

                let amount = this.invoice ? item['amount']/100 : quantity * item.hourly_rate 
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

        print() {
            console.log('print')
            let format = 'YYYY-MM-DD'
            document.title = this.invoice ? 'Bill-' + dayjs.tz(this.invoice.issue_at).tz('America/Chicago').format(format) : 'Current-Bill-' + dayjs().format(format)
            window.print()
        },

        formatCurrency(amount) {
            return (new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2 })).format(amount)
        }
    }
}
</script>
