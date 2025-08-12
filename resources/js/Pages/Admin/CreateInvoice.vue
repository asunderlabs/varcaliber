<template>
    <Head title="Admin Edit Invoice" />

    <BreezeAuthenticatedLayout :isAdmin="true">
        <template #breadcrumbs>
            Invoices / <Link :href="route('admin.invoices.index')" class="text-indigo-500 hover:text-indigo-700">Back to Invoices</Link>
        </template>

        <div class="bg-white px-4 md:px-6 py-3">
            <PageSection header="New Invoice">
                <form @submit.prevent="onSubmit">
                    <div class="flex flex-col">
                        <div class="mb-4">
                            <div>
                                <label for="organization">Organization</label>
                                <select class="w-full" v-model="form.organization" name="organization">
                                    <option value="">Select organization</option>
                                    <option v-for="organization in organizations" :key="organization.id" :value="organization.id">{{ organization.name }}</option>
                                </select>
                                <div v-if="errors.organization" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{ errors.organization }}</div>
                            </div>
                            <div>
                                <label>Delivery</label>
                                <input type="radio" name="delivery" value="automatic" v-model="form.delivery" @change="changeDelivery()" /> Automatic
                                <input type="radio" name="delivery" value="manual" v-model="form.delivery" @change="changeDelivery()" /> Manual
                                <div v-if="errors.delivery" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{ errors.delivery }}</div>
                            </div>
                            <div>
                                <label for="billingStart">Billing Start</label>
                                <input type="date" name="billingStart" v-model="form.billingStart" class="" />
                                <div v-if="errors.billingStart || errors.billingStart" class="bg-red-100 text-red-500 text-sm mt-2 p-2 flex flex-col">
                                    <span>{{ errors.billingStart }}</span>
                                </div>
                            </div>
                            <div>
                                <label for="billingEnd">Billing End</label>
                                <input type="date" name="billingEnd" v-model="form.billingEnd" class="" />
                                <div v-if="errors.billingEnd || errors.billingEnd" class="bg-red-100 text-red-500 text-sm mt-2 p-2 flex flex-col">
                                    <span>{{ errors.billingEnd }}</span>
                                </div>
                            </div>
                            <div>
                                <label for="issueAt">Issue At</label>
                                <input type="date" name="issueAt" v-model="form.issueAt" class="" />
                                <div v-if="errors.issueAt || errors.issueAt" class="bg-red-100 text-red-500 text-sm mt-2 p-2 flex flex-col">
                                    <span>{{ errors.issueAt }}</span>
                                </div>
                            </div>
                            <div>
                                <label for="dueAt">Due At</label>
                                <input type="date" name="dueAt" v-model="form.dueAt" class="" />
                                <div v-if="errors.dueAt || errors.dueAt" class="bg-red-100 text-red-500 text-sm mt-2 p-2 flex flex-col">
                                    <span>{{ errors.dueAt }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <button type="submit" class="rounded bg-green-500 text-white px-4 py-2 hover:bg-green-700">Create Invoice</button>
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
import PageHeader from '@/Components/PageHeader.vue'
import PageSection from '@/Components/PageSection.vue';
import { Link } from '@inertiajs/vue3';

export default {
    components: {
        BreezeAuthenticatedLayout,
        Head,
        PageHeader,
        PageSection,
        Link
    },

    props: {
        organizations: Array,
        billingStart: String,
        billingEnd: String,
        errors: Object
    },

    data() {
        return {
            form: {
                organization: null,
                billingStart: null,
                billingEnd: null,
                issueAt: null,
                dueAt: null,
                delivery: 'automatic'
            }
        }
    },

    mounted() {
        this.resetDates()
    },

    methods: {
        onSubmit(e) {
            console.log(this.form)
            let form = Object.assign({}, this.form)
            this.$inertia.post("/admin/invoices", form)
        },

        changeDelivery() {
            if (this.form.delivery === 'manual') {
                this.form.billingStart = this.form.billingEnd = this.form.issueAt = dayjs().format('YYYY-MM-DD')
                this.form.dueAt = dayjs().add(7, 'day').format('YYYY-MM-DD')
                return
            }

            this.resetDates()
        },

        resetDates() {
            this.form.billingStart = dayjs.tz(this.billingStart).tz('America/Chicago').format('YYYY-MM-DD')
            this.form.billingEnd = dayjs.tz(this.billingEnd).tz('America/Chicago').format('YYYY-MM-DD')
            this.form.issueAt = dayjs.tz(this.billingEnd).tz('America/Chicago').add(1, 'day').format('YYYY-MM-DD')
            this.form.dueAt = dayjs.tz(this.billingEnd).tz('America/Chicago').add(4, 'day').format('YYYY-MM-DD')
        }
    }
}
</script>
