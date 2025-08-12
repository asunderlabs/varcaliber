<template>
    <Head title="Admin - Edit User" />

    <BreezeAuthenticatedLayout :isAdmin="true">
        <div class="bg-white px-4 md:px-6 py-3">
            <PageSection header="Edit User">
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
                        <label for="title">Email</label>
                        <input type="text" name="email" v-model="form.email" class="w-full" />
                        <div v-if="errors.email" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{ errors.email }}</div>
                    </div>
                    <div>
                        <input type="checkbox" name="is_admin" v-model="form.is_admin" @change="changeAdmin()"/> Admin
                        <div v-if="errors.is_admin" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{ errors.is_admin }}</div>
                    </div>
                    <div>
                        <select name="organization" v-model="form.organization_id" :disabled="form.is_admin > 0">
                            <option v-for="organization in organizations" :key="organization.id" :value="organization.id">{{ organization.name }}</option>
                        </select>
                        <div v-if="errors.organization" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{ errors.organization }}</div>
                    </div>
                    <br>
                    <div v-if="! user.is_admin" class="space-y-2">
                        <h3 class="text-lg font-bold">Preferences</h3>
                        <div>
                            <label>Invoice Notifications</label>
                            <input type="radio" name="invoice_notification" value="" v-model="form.invoice_notification" /> None
                            <input type="radio" name="invoice_notification" value="email" v-model="form.invoice_notification" /> Email
                            <input type="radio" name="invoice_notification" value="cc" v-model="form.invoice_notification" /> CC
                            <div v-if="errors.invoice_notification" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{ errors.invoice_notification }}</div>
                        </div>
                        <div>
                            <label>Account Notifications</label>
                            <input type="radio" name="account_notification" value="" v-model="form.account_notification" /> None
                            <input type="radio" name="account_notification" value="email" v-model="form.account_notification" /> Email
                            <input type="radio" name="account_notification" value="cc" v-model="form.account_notification" /> CC
                            <div v-if="errors.account_notification" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{ errors.account_notification }}</div>
                        </div>
                        <div>
                            <label>Account Notifications Day</label>
                            <select name="organization" v-model="form.account_notification_day">
                                <option v-for="(day, dayNumber) in daysOfWeek" :key="dayNumber" :value="dayNumber">{{ day }}</option>
                            </select>
                            <div v-if="errors.account_notification_day" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{ errors.account_notification_day }}</div>
                        </div>
                    </div>
                    
                    <button type="submit" class="rounded bg-indigo-500 text-white p-2">Update User</button>
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

const daysOfWeek = [
    'Sunday',
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
]

export default {
    components: {
        BreezeAuthenticatedLayout,
        Head,
        Table,
        PageHeader,
        PageSection
    },

    props: {
        user: Object,
        organizations: Array,
        errors: Object
    },

    data() {
        return {
            daysOfWeek,
            form: {
                name: '',
                email: '',
                is_admin: '',
                organization_id: null,
                invoice_notification: '',
                account_notification: '',
                account_notification_day: null,
            }
        }
    },


    created() {
        this.form.name = this.user.name
        this.form.email = this.user.email
        this.form.is_admin = this.user.is_admin ? true : false
        this.form.organization_id = this.user.organizations.length ? this.user.organizations[0].id : null

        const prefs = this.user.preferences

        if (prefs) {
            this.form.invoice_notification = prefs.invoice_notification_email ? 'email' : (prefs.invoice_notification_email_cc ? 'cc' : this.form.invoice_notification)
            this.form.account_notification = prefs.account_notification_email ? 'email' : (prefs.account_notification_email_cc ? 'cc' : this.form.account_notification)
            this.form.account_notification_day = prefs.account_notification_email_day ? daysOfWeek.findIndex( day => day === prefs.account_notification_email_day) : this.form.account_notification_day
        }
    },

    methods: {
        onSubmit(e) {
            this.$inertia.put(this.route('admin.users.update', this.user.id), this.form)
        },

        changeAdmin() {
            if (this.form.is_admin) {
                this.form.organization_id = null
            }
        }
    },
}
</script>

<style scoped>

form label {
    display: block;
}
</style>