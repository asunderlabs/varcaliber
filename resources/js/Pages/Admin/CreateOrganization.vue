<template>
    <Head title="Create Organization" />

    <BreezeAuthenticatedLayout :isAdmin="true">
        <div class="bg-white px-4 md:px-6 py-3">
            <PageSection header="Create Organization">
                <form @submit.prevent="onSubmit" class="space-y-2">
                    <div v-if="Object.keys(errors).length" class="bg-red-100 p-4 text-red-700 text-sm">
                        Please fix the errors below
                    </div>
                    <div>
                        <label for="title">Name</label>
                        <input type="text" name="name" v-model="form.name" class="w-full" />
                        <div v-if="errors.name" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{ errors.name }}</div>
                    </div>
                    <div>
                        <label for="title">Client ID</label>
                        <input type="text" name="client_id" v-model="form.client_id" class="w-full" />
                        <div v-if="errors.client_id" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{ errors.client_id }}</div>
                    </div>
                    <div>
                        <label for="title">Stripe Customer ID</label>
                        <input type="text" name="stripe_customer_id" v-model="form.stripe_customer_id" class="w-full" />
                        <div v-if="errors.stripe_customer_id" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{ errors.stripe_customer_id }}</div>
                    </div>
                    <div>
                        <label for="title">Billing Contact</label>
                        <input type="text" name="billing_contact" v-model="form.billing_contact" class="w-full" />
                        <div v-if="errors.billing_contact" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{ errors.billing_contact }}</div>
                    </div>
                    <div>
                        <label for="title">Email</label>
                        <input type="text" name="email" v-model="form.email" class="w-full" />
                        <div v-if="errors.email" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{ errors.email }}</div>
                    </div>
                    <div>
                        <label for="title">Address Line 1</label>
                        <input type="text" name="address_line_1" v-model="form.address_line_1" class="w-full" />
                        <div v-if="errors.address_line_1" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{ errors.address_line_1 }}</div>
                    </div>
                    <div>
                        <label for="title">Address Line 1</label>
                        <input type="text" name="address_line_2" v-model="form.address_line_2" class="w-full" />
                        <div v-if="errors.address_line_2" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{ errors.address_line_2 }}</div>
                    </div>
                    <div>
                        <label for="title">City</label>
                        <input type="text" name="city" v-model="form.city" class="w-full" />
                        <div v-if="errors.city" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{ errors.city }}</div>
                    </div>
                    <div>
                        <label for="title">State</label>
                        <input type="text" name="state" v-model="form.state" class="w-full" />
                        <div v-if="errors.state" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{ errors.state }}</div>
                    </div>
                    <div>
                        <label for="title">Zip Code</label>
                        <input type="text" name="zip_code" v-model="form.zip_code" class="w-full" />
                        <div v-if="errors.zip_code" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{ errors.zip_code }}</div>
                    </div>
                    <div>
                        <label for="title">Hourly Rate</label>
                        <input type="text" name="hourly_rate" v-model="form.hourly_rate" class="w-full" />
                        <div v-if="errors.hourly_rate" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{ errors.hourly_rate }}</div>
                    </div>
                    <button type="submit" class="rounded bg-indigo-500 text-white p-2">Create Organization</button>
                </form>
            </PageSection>
        </div>
    </BreezeAuthenticatedLayout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { Head } from '@inertiajs/vue3'
import Table from '@/Components/Table.vue'
import PageHeader from '@/Components/PageHeader.vue'
import PageSection from '@/Components/PageSection.vue'

export default {
    components: {
        BreezeAuthenticatedLayout,
        Head,
        Table,
        PageHeader,
        PageSection
    },

    props: {
        defaultHourlyRate: Number,
        errors: Object
    },

    data() {
        return {
            form: {
                name: '',
                client_id: '',
                stripe_customer_id: '',
                billing_contact: '',
                email: '',
                address_line_1: '',
                address_line_2: '',
                city: '',
                state: '',
                zip_code: '',
                hourly_rate: this.defaultHourlyRate
            }
        }
    },

    mounted() {
    },

    methods: {
        onSubmit(e) {
            console.log(this.form)
            this.$inertia.post("/admin/organizations", this.form)
        },
    },
}
</script>

<style scoped>

form label {
    display: block;
}
</style>