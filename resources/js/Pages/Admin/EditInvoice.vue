<template>
    <Head title="Admin Edit Invoice" />

    <BreezeAuthenticatedLayout :isAdmin="true">
        <template #breadcrumbs>
            Invoices / <Link :href="route('admin.invoices.index')" class="text-indigo-500 hover:text-indigo-700">Back to Invoices</Link>
        </template>

        <div class="bg-white px-4 md:px-6 py-3">
            <PageSection :header="'Invoice #' + invoice.invoice_number">
                <form @submit.prevent="onSubmit">
                    <div class="flex flex-col">
                        <div class="mb-4">
                            <div>
                                Organization: {{ invoice.organization.name }}
                            </div>
                            <div>
                                Billing Period: {{ billingStart }} - {{ billingEnd }}
                            </div>
                            <div>
                                Created At: {{ createdAt }}
                            </div>
                            <div>
                                <label for="number">Invoice Number</label>
                                <input type="number" name="number" step="1" v-model="form.number" class="" />
                                <div v-if="errors.number || errors.number" class="bg-red-100 text-red-500 text-sm mt-2 p-2 flex flex-col">
                                    <span>{{ errors.number }}</span>
                                </div>
                            </div>
                            <div>
                                <label>Delivery</label>
                                <input type="text" readonly :value="invoice.manual_delivery ? 'Manual' : 'Automatic'" />
                            </div>
                            <div>
                                <label for="startDate">Issue At</label>
                                <input type="date" name="startDate" v-model="form.issueAtDate" class="" />
                                <input type="time" name="startTime" v-model="form.issueAtTime" class="" />
                                <div v-if="errors.issueAtDate || errors.issueAtTime" class="bg-red-100 text-red-500 text-sm mt-2 p-2 flex flex-col">
                                    <span>{{ errors.issueAtDate }}</span>
                                    <span>{{ errors.issueAtTime }}</span>
                                </div>
                            </div>
                            <div>
                                <label for="dueAtDate">Due At</label>
                                <input type="date" name="dueAtDate" v-model="form.dueAtDate" class="" />
                                <input type="time" name="dueAtTime" v-model="form.dueAtTime" class="" />
                                <div v-if="errors.dueAtDate || errors.dueAtTime" class="bg-red-100 text-red-500 text-sm mt-2 p-2 flex flex-col">
                                    <span>{{ errors.dueAtDate }}</span>
                                    <span>{{ errors.dueAtTime }}</span>
                                </div>
                            </div>
                        </div>
                        <table class="table-fixed mb-4">
                            <thead>
                                <tr>
                                    <th class="w-5/12">Description</th>
                                    <th class="w-2/12">Hours</th>
                                    <th class="w-2/12">Rate</th>
                                    <th class="w-2/12">Amount</th>
                                    <th class="w-1/12"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, itemIndex) in form.items" :key="itemIndex">
                                    <td>
                                        <input type="text" v-model="form.items[itemIndex].description" class="w-full" />
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" v-model="form.items[itemIndex].hours" class="w-full" />
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" v-model="form.items[itemIndex].hourly_rate" class="w-full" />
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" v-model="form.items[itemIndex].amount" class="w-full" />
                                    </td>
                                    <td align="right">


                                        <button type="button" @click="recalculateItem(itemIndex)" class="p-1 opacity-70 hover:opacity-100 text-indigo-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
                                                <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
                                            </svg>
                                        </button>

                                        
                                        <button type="button" @click="removeItem(itemIndex)" class="p-1 opacity-70 hover:opacity-100 text-indigo-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                                <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                            </svg>
                                        </button>
                                    </td>

                                </tr>
                            </tbody>
                        </table>

                        <div class="mb-2 flex space-x-4">
                            <button type="button" @click="addItem()" class="rounded bg-gray-100 text-gray-700 px-2 py-1 text-sm hover:bg-gray-200 flex items-center space-x-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2Z"/>
                                </svg>
                                <span>Add Item</span>
                            </button>
                        </div>

                        <table class="table-fixed mb-6">
                            <tbody>
                                <tr>
                                    <td class="w-9/12 p-2" align="right">Subtotal</td>
                                    <td class="w-1/12"></td>
                                    <td class="w-1/12 p-2" align="right">{{ subtotal.toFixed(2) }}</td>
                                    <td class="w-1/12"></td>
                                </tr>
                                <tr>
                                    <td class="w-9/12 p-2" align="right">Tax</td>
                                    <td class="w-1/12"></td>
                                    <td class="w-1/12 p-2 border-black border-b-2" align="right">{{ tax.toFixed(2) }}</td>
                                </tr>
                                <tr>
                                    <td class="w-9/12 p-2" align="right">Total</td>
                                    <td class="w-1/12"></td>
                                    <td class="w-1/12 p-2" align="right">{{ total.toFixed(2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" align="right">
                                        <button type="button" @click="recalculateAll()" class="rounded bg-gray-100 text-gray-700 px-2 py-1 text-sm hover:bg-gray-200 flex items-center space-x-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
                                                <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
                                            </svg>
                                            <span>Recalculate All</span>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div v-if="total > 0" class="bg-gray-50 rounded p-3">
                            <label for="note">Pay By Bank Discount</label>
                            <input type="number" step="0.01" name="pay_by_bank_discount" v-model="form.pay_by_bank_discount" />
                            <div v-if="errors.note || errors.note" class="bg-red-100 text-red-500 text-sm mt-2 p-2 flex flex-col">
                                <span>{{ errors.note }}</span>
                            </div>
                            <div class="text-sm">
                                Recommended discount: 
                                <a href="javascript:void(0);" class="text-blue-700 underline hover:text-indigo-700" @click="form.pay_by_bank_discount = recommendedDiscount">${{ recommendedDiscount.toFixed(2) }}</a>
                            </div>
                        </div>
                        
                        <div>
                            <label for="note">Note</label>
                            <textarea name="note" v-model="form.note" class="w-full" maxlength="255"></textarea>
                            <div v-if="errors.note || errors.note" class="bg-red-100 text-red-500 text-sm mt-2 p-2 flex flex-col">
                                <span>{{ errors.note }}</span>
                            </div>
                        </div>
                        
                        <div>
                            <button type="submit" class="rounded bg-green-500 text-white px-4 py-2 hover:bg-green-700">Save Changes</button>
                        </div>
                    </div>
                </form>
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
        errors: Object
    },

    data() {
        return {
            form: {
                number: null,
                issueAtDate: null,
                issueAtTime: null,
                dueAtDate: null,
                dueAtTime: null,
                items: [],
                pay_by_bank_discount: null,
                note: null,
            }
        }
    },

    mounted() {
        this.form.number = this.invoice.number
        this.form.items.push(this.emptyItem())

        if (this.invoice.items) {
            this.form.items = this.invoice.items.map( item => {
                item.hours = item.hours ? item.hours.toFixed(2) : null
                item.amount = (item.amount / 100).toFixed(2)
                return item
            })
        }

        this.form.issueAtDate = dayjs.tz(this.invoice.issue_at).tz('America/Chicago').format('YYYY-MM-DD')
        this.form.issueAtTime = dayjs.tz(this.invoice.issue_at).tz('America/Chicago').format('HH:mm')
        this.form.dueAtDate = dayjs.tz(this.invoice.due_at).tz('America/Chicago').format('YYYY-MM-DD')
        this.form.dueAtTime = dayjs.tz(this.invoice.due_at).tz('America/Chicago').format('HH:mm')
        this.form.pay_by_bank_discount = this.invoice.pay_by_bank_discount / 100
        this.form.note = this.invoice.note
    },

    methods: {
        onSubmit(e) {

            // TODO: Add validation to check that each field has a value when it should
            let form = Object.assign({}, this.form)

            form.subtotal = this.subtotal * 100
            form.tax = this.tax * 100
            form.total = this.total * 100
            form.pay_by_bank_discount = form.pay_by_bank_discount * 100

            let items = []

            form.items.forEach( item => {
                let itemCopy = Object.assign({}, item)
                itemCopy.hours = item.hours ? parseFloat(item.hours) : null
                itemCopy.hourly_rate = item.hourly_rate ? parseFloat(item.hourly_rate) : null
                itemCopy.amount = parseFloat(item.amount)*100
                items.push(itemCopy)
            })

            form.items = items

            this.$inertia.put("/admin/invoices/" + this.invoice.id, form)
        },

        addItem() {
            this.form.items.push(this.emptyItem())
        },

        removeItem(itemIndex) {
            this.form.items.splice(itemIndex, 1)
        },

        recalculateItem(itemIndex) {

            let item = Object.assign({}, this.form.items[itemIndex])

            if (!item.hourly_rate || !item.hours) {
                return
            }

            this.form.items[itemIndex].amount = (Math.round(item.hours * item.hourly_rate * 100) / 100).toFixed(2)
        },

        recalculateAll() {
            this.form.items.forEach ( (item, itemIndex) => {
                this.recalculateItem(itemIndex)
            })
        },

        emptyItem() {
            return {
                'description': '',
                'hours': 0,
                'hourly_rate': 0,
                'amount': 0
            }
        }
    },

    computed: {
        createdAt() {
            return dayjs(this.invoice.created_at).format('M/D/YY HH:mm A')
        },

        billingStart() {
            return dayjs.tz(this.invoice.billing_start).tz('America/Chicago').format('M/D/YY')
        },

        billingEnd() {
            return dayjs.tz(this.invoice.billing_end).tz('America/Chicago').format('M/D/YY')
        },

        subtotal() {
            if (!this.form.items) {
                return 0
            }

            let subtotal = 0
            this.form.items.forEach(item => {
                subtotal += parseFloat(item.amount)
            })
            return subtotal
        },

        tax() {
            return 0
        },

        total() {
            return this.subtotal + this.tax
        },

        recommendedDiscount() {
            let discount = Math.round(this.total * 0.03)

            if (discount < 5) {
                return 0
            }

            discount = Math.floor(discount / 5) * 5 // Make discount multiple of 5
            return Math.min(discount, 50)
        }
    }
}
</script>

<style scoped>

form label {
    display: block;
}
</style>