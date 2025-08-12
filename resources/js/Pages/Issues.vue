<template>
    <Head title="Issues" />

    <BreezeAuthenticatedLayout :organization="organization">
        <template #header>
            <PageHeader header="Issues"></PageHeader>
        </template>

        <div class="px-2 sm:px-6 lg:px-8">
            
        </div>
    </BreezeAuthenticatedLayout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/Components/PageHeader.vue'

export default {
    components: {
        BreezeAuthenticatedLayout,
        Head,
        PageHeader,
    },

    props: {
        organization: Object
    },
}
</script>
