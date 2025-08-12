<template>
    <Head title="Add Payment" />

    <BreezeAuthenticatedLayout :isAdmin="true">

        <template #breadcrumbs>
            Invoices / Add Payment / Invoice #{{ invoice.invoice_number }} / <Link :href="route('admin.invoices.index')" class="text-indigo-500 hover:text-indigo-700">Back to Invoices</Link>
        </template>

        <div class="bg-white px-4 md:px-6 py-3">

            <PageSection>
                <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-6">
                    <div class="md:w-1/2">
                        <h3 class="text-xl font-bold mb-3">Create New Payment</h3>
                        <form @submit.prevent="onSubmit">
                            <div class="space-y-3">
                                <div>
                                    <label for="startDate">Paid At</label>
                                    <input type="date" name="paidAtDate" v-model="form.paidAtDate" class="mr-3" />
                                    <input type="time" name="paidAtTime" v-model="form.paidAtTime" class="" />
                                    <div v-if="errors.paidAtDate || errors.paidAtTime" class="bg-red-100 text-red-500 text-sm mt-2 p-2 flex flex-col">
                                        <span>{{ errors.paidAtDate }}</span>
                                        <span>{{ errors.paidAtTime }}</span>
                                    </div>
                                </div>
                                <div>
                                    <label for="amount">Payment Amount</label>
                                    <input type="number" name="amount" step="0.01" min="0" v-model="form.amount" class="w-full" />
                                    <div v-if="errors.amount" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{ errors.amount }}</div>
                                </div>
                                <div>
                                    <label for="amount_applied">Amount Applied to Invoice</label>
                                    <input type="number" name="amount_applied" step="0.01" min="0" v-model="form.amount_applied" class="w-full" />
                                    <div v-if="errors.amount_applied" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{ errors.amount_applied }}</div>
                                </div>
                                <div>
                                    <input type="checkbox" v-model="form.paid" />  Mark invoice as paid
                                </div>
                                <button type="submit" class="rounded bg-indigo-500 text-white p-2">Create Payment</button>
                            </div>
                            
                        </form>
                    </div>
                    <div class="flex flex-col justify-center">Or</div>
                    <div class="md:w-1/2">
                        <h3 class="text-xl font-bold mb-3">Add Existing Payment</h3>
                        <form @submit.prevent="onPaymentSelectSubmit">
                            <div class="space-y-3">
                                <div>
                                    <label for="paymentId">Payment</label>
                                    <select class="w-full" v-model="paymentSelectForm.paymentId" name="paymentId">
                                        <option v-for="payment in payments" :key="payment.id" :value="payment.id">{{ formatAmount(payment.amount/100) }} - {{ paymentDate(payment) }}</option>
                                    </select>
                                    <div v-if="errors.paymentId" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{ errors.paymentId }}</div>
                                </div>
                                <div>
                                    <label for="amount_applied">Amount Applied to Invoice</label>
                                    <input type="number" name="amount_applied" step="0.01" min="0" v-model="paymentSelectForm.amount_applied" class="w-full" />
                                    <div v-if="errors.amount_applied" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{ errors.amount_applied }}</div>
                                </div>
                                <div>
                                    <input type="checkbox" v-model="paymentSelectForm.paid" />  Mark invoice as paid
                                </div>
                                <button type="submit" class="rounded bg-indigo-500 text-white p-2">Add Payment</button>
                            </div>
                            
                        </form>
                    </div>
                </div>
            </PageSection>
        </div>
    </BreezeAuthenticatedLayout>
</template>

<script>
import dayjs from 'dayjs'
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { Head } from '@inertiajs/vue3';
import Table from '@/Components/Table.vue';
import PageHeader from '@/Components/PageHeader.vue'
import PageSection from '@/Components/PageSection.vue';
import { Link } from '@inertiajs/vue3';

export default {
    components: {
        BreezeAuthenticatedLayout,
        Head,
        Table,
        PageHeader,
        PageSection,
        Link
    },

    props: {
        invoice: Object,
        payments: Array,
        errors: Object
    },

    data() {
        return {
            form: {
                paidAtDate: '',
                paidAtTime: '',
                amount: '',
                amount_applied: null,
                invoices: [],
                paid: true
            },
            paymentSelectForm: {
                paymentId: '',
                amount_applied: null,
                paid: true
                
            }
        }
    },

    mounted() {
        if (this.invoice) {
            this.form.amount = this.invoice.total/100
            this.form.amount_applied = this.form.amount
            this.form.invoices.push(this.invoice.id)
        }

        this.form.paidAtDate = dayjs().format('YYYY-MM-DD')
        this.form.paidAtTime = dayjs().format('HH:MM')
    },

    methods: {
        onSubmit() {
            this.$inertia.post("/admin/payments", {
                paid_at: dayjs(this.form.paidAtDate + ' ' + this.form.paidAtTime).tz('UTC').format('YYYY-MM-DD HH:MM:ss'),
                amount: this.form.amount * 100,
                amount_applied: this.form.amount_applied * 100,
                organization_id: this.invoice.organization_id,
                invoices: [
                    {
                        id: this.invoice.id,
                        paid: this.form.paid
                    }
                ],
            })
        },

        onPaymentSelectSubmit(e) {
            console.log(this.paymentSelectForm)
            let data = Object.assign({}, this.paymentSelectForm)
            data.amount_applied = data.amount_applied * 100
            this.$inertia.post(`/admin/payments/${this.paymentSelectForm.paymentId}/invoice/${this.invoice.id}`, data)
        },
        formatAmount(amount) {
            return (new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2 })).format(amount)
        },

        paymentDate(payment) {
            return dayjs.tz(payment.paid_at).tz('America/Chicago').format('MM/DD/YY')
        }
    },

    computed: {
    }
}
</script>

<style scoped>

form label {
    display: block;
}
</style>