<template>
    <Head title="Payment" />

    <BreezeAuthenticatedLayout :isAdmin="true">
        <template #header>
            <PageHeader header="Payment"></PageHeader>
        </template>

        <div class="bg-white px-4 md:px-6 py-3">

            <PageSection :header="'Payment for ' + payment.organization.name">
                <Table :rows="rows" :options="{cellClass: 'first:w-32 first:font-bold', noRowLines: true}" class="mb-6"></Table>
                <Link :href="route('admin.payments.notify', payment.id)" class="inline-flex items-center rounded bg-indigo-500 text-white p-2 cursor-pointer" method="post">
                    Notify {{ payment.organization.name }}
                </Link>
            </PageSection>
        </div>
    </BreezeAuthenticatedLayout>
</template>

<script>
import dayjs from 'dayjs'
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { Head, Link } from '@inertiajs/vue3';
import Table from '@/Components/Table.vue';
import PageHeader from '@/Components/PageHeader.vue'
import PageSection from '@/Components/PageSection.vue';

export default {
    components: {
        BreezeAuthenticatedLayout,
        Head,
        Link,
        Table,
        PageHeader,
        PageSection
    },

    props: {
        payment: Object
    },

    data() {
        return {
        }
    },

    mounted() {

    },

    methods: {

    },

    computed: {
        rows() {
            let invoiceDescriptions = []

            if (this.payment.invoices) {
                this.payment.invoices.forEach(invoice => {
                    invoiceDescriptions.push('Invoice #' + invoice.invoice_number + ' (' + (invoice.paid ? 'PAID' : 'NOT PAID') + ')')
                })
            }
            return [
                {
                    columns: [
                        {
                            text: 'Date'
                        },
                        {
                            text: dayjs.tz(this.payment.paid_at).tz('America/Chicago').format('MMM D, YYYY')
                        }
                    ]
                },
                {
                    columns: [
                        {
                            text: 'Amount'
                        },
                        {
                            text: (new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2 })).format(this.payment.amount/100)
                        }
                    ]
                },
                {
                    columns: [
                        {
                            text: 'Invoices'
                        },
                        {
                            text: invoiceDescriptions.join(',')
                        }
                    ]
                },
            ]
        }
    }
}
</script>

<style scoped>

form label {
    display: block;
}
</style>