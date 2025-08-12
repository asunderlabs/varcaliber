<template>
    <Head title="Admin Organizations" />

    <BreezeAuthenticatedLayout :isAdmin="true">
        <template #header>
            <PageHeader header="Organization"></PageHeader>
        </template>

        <div class="bg-white px-4 md:px-6 py-3">
            <PageSection :header="organization.name">
                <Table :columns="columns" :rows="rows"></Table>
            </PageSection>
        </div>
    </BreezeAuthenticatedLayout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { Head } from '@inertiajs/vue3';
import Table from '@/Components/Table.vue';
import PageHeader from '@/Components/PageHeader.vue'
import PageSection from '@/Components/PageSection.vue';

export default {
    components: {
        BreezeAuthenticatedLayout,
        Head,
        Table,
        PageHeader,
        PageSection
    },

    props: {
        organization: Object,
        stats: Array
    },

    data() {
        return {
            columns: [
                'Stat',
                'value'
            ],
            rows: null,
        }
    },

    mounted() {
        this.rows = []
        this.stats.forEach( stat => {
            this.rows.push({
                columns: [
                    {
                        text: stat.name,
                    },
                    {
                        text: stat.text
                    }
                ]
            })
        })
    }
}
</script>
