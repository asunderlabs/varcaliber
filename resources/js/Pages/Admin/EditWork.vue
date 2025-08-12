<template>
    <Head title="Edit Work" />

    <BreezeAuthenticatedLayout :isAdmin="true">
        <template #breadcrumbs>
            Work Entry / <Link :href="route('admin.hours.index')" class="text-indigo-500 hover:text-indigo-700">Back to reports</Link>
        </template>

        <div class="bg-white px-4 md:px-6 py-3">
            <PageSection header="Edit Work Entry">
                <form @submit.prevent="onSubmit">
                    <div class="space-y-2">
                        <div>
                            <label for="description">Description</label>
                            <input type="text" name="description" v-model="form.description" class="w-full" />
                            <div v-if="errors.description" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{
                                errors.description }}</div>
                        </div>
                        <div>
                            <label for="startDate">Starts At</label>
                            <div class="flex flex-col md:flex-row space-y-2 md:space-y-0">
                                <div
                                    class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2 mr-8 w-full md:w-auto">
                                    <input type="date" name="startDate" v-model="form.startDate" @change="changedTime()" />
                                    <input type="time" name="startTime" v-model="form.startTime" @change="changedTime()" />
                                </div>
                                <label class="w-full md:w-1/3"><input type="number" name="minutes" v-model="form.minutes"
                                        step="1" min="0" class="w-32" @change="changedMinutes()" /> minutes</label>
                            </div>
                            <div v-if="errors.startDate || errors.startTime"
                                class="bg-red-100 text-red-500 text-sm mt-2 p-2 flex flex-col">
                                <span>{{ errors.startDate }}</span>
                                <span>{{ errors.startTime }}</span>
                            </div>
                        </div>
                        <div>
                            <label for="endDate">Ends At</label>
                            <div class="flex flex-col md:flex-row space-y-2 md:space-y-0">
                                <input type="date" name="endDate" v-model="form.endDate" class="mr-2"
                                    @change="changedTime()" />
                                <input type="time" name="endTime" v-model="form.endTime" class="" @change="changedTime()" />
                            </div>
                            <div v-if="errors.endDate || errors.endTime"
                                class="bg-red-100 text-red-500 text-sm mt-2 p-2 flex flex-col">
                                <span>{{ errors.endDate }}</span>
                                <span>{{ errors.endTime }}</span>
                            </div>
                        </div>
                        <div>
                            <label for="organization">Organization</label>
                            <select class="w-full" v-model="form.organization" name="organization"
                                @change="changedOrganization()">
                                <option value="">Select organization</option>
                                <option v-for="organization in organizations" :key="organization.id"
                                    :value="organization.id">{{ organization.name }}</option>
                            </select>
                            <div v-if="errors.organization" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{
                                errors.organization }}</div>
                        </div>
                        <div>
                            <div>
                                <div class="flex items-center justify-between">
                                    <label for="issue">Issue</label>
                                    <div class="text-blue-500 underline cursor-pointer" @click="form.newIssue = !form.newIssue">{{ form.newIssue ? 'Cancel' : 'New issue' }}</div>
                                </div>
                                <div v-if="form.newIssue">
                                    <input type="text" name="issueName" v-model="form.issueName" class="w-full" />
                                </div>
                                <select v-else class="w-full" v-model="form.issue" name="issue">
                                    <option value="">Select an issue</option>
                                    <option v-for="issue in issues" :key="issue.id" :value="issue.id">{{ issue.title }}</option>
                                </select>
                            </div>
                            <div v-if="errors.issue" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{ errors.issue }}
                            </div>
                            <div v-if="errors.issueName" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{ errors.issueName }}
                            </div>
                        </div>
                        <button type="submit" class="rounded bg-indigo-500 text-white p-2">Submit</button>
                    </div>
                </form>
            </PageSection>

            <div>
                <button type="button" @click="confirmDelete('Delete this work entry?', route('admin.hours.destroy', workEntry.id));" class="text-white bg-rose-500 p-3 rounded">
                    Delete Work Entry
                </button>
            </div>
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
        workEntry: Object,
        organizations: Array,
        errors: Object
    },

    data() {
        return {
            form: {
                description: '',
                startDate: '',
                startTime: '',
                endDate: '',
                endTime: '',
                minutes: 0,
                organization: '',
                issue: '',
                newIssue: false,
                issueName: '',
            }
        }
    },

    mounted() {
        this.form.description = this.workEntry.description
        this.form.startDate = this.workEntry.starts_at ? dayjs.tz(this.workEntry.starts_at).tz('America/Chicago').format('YYYY-MM-DD') : ''
        this.form.startTime = this.workEntry.starts_at ? dayjs.tz(this.workEntry.starts_at).tz('America/Chicago').format('HH:mm') : ''
        this.form.endDate = this.workEntry.ends_at ? dayjs.tz(this.workEntry.ends_at).tz('America/Chicago').format('YYYY-MM-DD') : ''
        this.form.endTime = this.workEntry.ends_at ? dayjs.tz(this.workEntry.ends_at).tz('America/Chicago').format('HH:mm') : ''
        this.changedTime()
        this.form.organization = this.workEntry.organization_id
        this.form.issue = this.workEntry.issue_id
    },

    methods: {
        onSubmit(e) {
            console.log(this.form)
            this.$inertia.put(`/admin/hours/${this.workEntry.id}`, this.form)
        },

        changedOrganization() {
            this.form.issue = ''
            this.form.newIssue = false
        },

        changedTypeOfWork() {
            this.form.issue = ''
            this.form.newIssue = false
            this.form.issueName = ''
        },

        changedTime() {
            let start = new dayjs(this.form.startDate + " " + this.form.startTime)
            let end = new dayjs(this.form.endDate + " " + this.form.endTime)
            this.form.minutes = end.diff(start, 'minute')
        },

        changedMinutes() {
            let start = new dayjs(this.form.startDate + " " + this.form.startTime).add(this.form.minutes, 'minute')
            this.form.endDate = start.format('YYYY-MM-DD')
            this.form.endTime = start.format('HH:mm')
        },

        confirmDelete(text, url) {
            if (confirm(text)) {
                this.$inertia.delete(url)
            }
        }
    },

    computed: {
        selectedOrganization() {
            return this.form.organization ? this.organizations.find(organization => organization.id === this.form.organization) : null
        },

        issues() {
            return this.selectedOrganization ? this.selectedOrganization.issues.filter( issue => ! issue.archived_at ) : null
        }
    },
}
</script>

<style scoped>form label {
    display: block;
}</style>