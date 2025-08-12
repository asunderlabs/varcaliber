<template>
    <Head title="Billing - Summary" />

    <BreezeAuthenticatedLayout :organization="organization">
        <template #header>
            <PageHeader header="Summary" class="print:hidden"></PageHeader>
        </template>

        <template #subnav>
            <BillingNav active="billing.index"></BillingNav>
        </template>

        <div class="bg-white px-4 md:px-6 py-3">
            <ReportHeader :title="`Current Billing Period (${start} - ${end})`" :stat="stat"></ReportHeader>
            <PageSection header="Items">
                <Table :rows="itemRows" :options="{cellClass: ''}"></Table>
            </PageSection>
            <br>
            <hr>
            <br>
            <PageSection header="Invoices Due">
                <Table :columns="invoiceColumns" :rows="invoiceRows" :options="{cellClass: 'first:w-32 last:w-28 last:hidden last:md:table-cell'}" empty-text="No invoices due" class="mb-3"></Table>
                <div>
                    <Link :href="route('invoices.index')" class="text-indigo-500 hover:text-indigo-700 underline">View all invoices</Link>
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
        report: Object,
        items: Array,
        invoices: Array,
    },

    data() {
        return {
        }
    },

    created() {
    },

    mounted() {

        
    },

    computed: {
        start() {
            let format = 'MMM D, YYYY'
            return dayjs.tz(this.billingCycle['start']).tz('America/Chicago').format(format)
        },
        end() {
            let format = 'MMM D, YYYY'
            return dayjs.tz(this.billingCycle['end']).tz('America/Chicago').format(format)
        },
        total() {
            return this.items.reduce( (total, item) => {
                const quantity = (Math.round(item['minutes'] / 60 * 100)/100).toFixed(2)
                return total + (quantity * item.hourly_rate )
            }, 0)
        },
        stat() {
            return {
                name: 'Current Total',
                description: 'Actual amount will be on your invoice once it is generated',
                number: this.formatCurrency(this.total)
            }
        },
        itemRows() {
            if (! this.items) {
                return
            }

            let rows = []
            this.items.forEach( item => {
                rows.push({
                    columns: [
                        {
                            text: item.description,
                            badge: {
                                text: (item.minutes/60).toFixed(2) + ' hrs at $' + item.hourly_rate + '/hr',
                                class: 'badge'
                            }
                        },
                        {
                            actions: [
                                {
                                    label: 'View Report',
                                    href: route('reports.index')
                                }
                            ]
                        }
                    ]
                })
            })

            return rows
        },
        invoiceColumns() {
            return [
                'Date',
                'Invoice',
                'Amount',
            ]
        },
        invoiceRows() {
            if (!this.invoices) {
                return
            }

            let rows = []
            this.invoices.forEach( invoice => {

                let actions = [
                    {
                        label: 'View PDF',
                        href: route('invoices.preview', {invoice: invoice.id, filename: invoice.filename}),
                        rawLink: true,
                        newTab: true
                    },
                    {
                        label: 'Download PDF',
                        href: route('invoices.download', {invoice: invoice.id}),
                        rawLink: true
                    }
                ]

                if (!invoice.paid && !invoice.payment_is_pending && this.organization.stripe_customer_id && !invoice.payments.length) {
                    actions.push({
                        label: 'Pay',
                        href: route('invoices.pay', {invoice: invoice.id}),
                        rawLink: true
                    })
                }

                let columns = [
                    {
                        text: dayjs.tz(invoice.issue_at).format('MMM D, YYYY')
                    },
                    {
                        actions: [
                            {
                                label: "#" + invoice.number.toString().padStart(4, '0'),
                                href: route('invoices.show', invoice.id)
                            }
                        ]
                    },
                    {
                        text: (new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2 })).format(invoice.total/100),
                        badge: {
                            text: invoice.paid ? "PAID" : ( invoice.payment_is_pending ? 'PAYMENT PENDING' : "NOT PAID" ),
                            class: invoice.paid ? 'badge badge-green' : 'badge'
                        }
                    },
                    {
                        actions
                    },
                ]

                rows.push({columns})
            })
            return rows
        }

    },

    methods: {
        formatCurrency(amount) {
            return (new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2 })).format(amount)
        }
    }
}
</script>
