<template>
    <Head title="Dashboard" />

    <BreezeAuthenticatedLayout :organization="organization">
        <template #header>
            <PageHeader header="Dashboard"></PageHeader>
        </template>

        <div class="flex flex-wrap -m-2">
            <StatCard v-for="(card, cardIndex) in statCards" :key="cardIndex" 
                class="m-2"
                :title="card['title']" 
                :stat="card['stat']" 
                :statLinkText="card['statLinkText']" 
                :statLinkUrl="card['statLinkUrl']">
            </StatCard>
        </div>
    </BreezeAuthenticatedLayout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/Components/PageHeader.vue'
import StatCard from '@/Components/StatCard.vue'

export default {
    components: {
        BreezeAuthenticatedLayout,
        Head,
        PageHeader,
        StatCard,
    },

    props: {
        organization: Object,
        statCards: Array
    },
}
</script>
