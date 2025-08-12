<script setup>
import { usePage } from '@inertiajs/vue3';

const companyLogo = usePage().props.settings.company_logo_path
const path = companyLogo ? `/storage${companyLogo}` : '/img/varcaliber.png'

</script>

<template>
    <div class="flex items-center" :class="{'max-w-32': !companyLogo}">
        <img v-if="path" :src="path">
    </div>
</template>
