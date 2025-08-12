<template>
    <Head title="Admin - Create User" />

    <BreezeAuthenticatedLayout :isAdmin="true">
        <div class="bg-white px-4 md:px-6 py-3">
            <PageSection header="Create User">
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
                    <button type="submit" class="rounded bg-indigo-500 text-white p-2">Create User</button>
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
        organizations: Array,
        errors: Object
    },

    data() {
        return {
            form: {
                name: '',
                email: '',
                is_admin: false,
                organization_id: null
            }
        }
    },

    mounted() {
    },

    methods: {
        onSubmit(e) {
            this.$inertia.post(this.route('admin.users.store', this.form))
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