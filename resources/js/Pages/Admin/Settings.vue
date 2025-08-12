<template>
    <Head title="Admin - Settings" />

    <BreezeAuthenticatedLayout :isAdmin="true">
        <div class="bg-white px-4 md:px-6 py-3">
            <PageSection header="Edit Settings">
                <form @submit.prevent="onSubmit" class="space-y-2">
                    <div v-if="Object.keys(errors).length" class="bg-red-100 p-4 text-red-700 text-sm">
                        Please fix the errors below
                    </div>
                    <div v-for="setting in settings" :key="setting.id">
                        <label for="title">{{ setting.key }}</label>
                        <input type="text" :name="setting.key" v-model="form[setting.key]" class="w-full" />
                        <div v-if="errors[setting.key]" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{ errors[setting.key] }}</div>
                    </div>
                    <button type="submit" class="rounded bg-indigo-500 text-white p-2">Update Settings</button>
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
        settings: Array,
        errors: Object
    },

    data() {
        return {
            form: {}
        }
    },


    created() {
        this.settings.forEach( setting => {
            this.form[setting.key] = setting.value
        })
    },

    methods: {
        onSubmit(e) {
            this.$inertia.put(this.route('admin.settings.update'), this.form)
        },
    },
}
</script>

<style scoped>

form label {
    display: block;
}
</style>