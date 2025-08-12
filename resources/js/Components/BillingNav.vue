<template>
    <nav class="space-y-6 print:hidden">
        <div class="border-b-2 border-gray-600 py-2">Billing</div>
        <ul class="space-y-2">
            <li>
                <Link :href="route('billing.index')" :class="linkClasses('billing.index')">Summary</Link>
            </li>
            <li>
                <Link :href="route('invoices.index')" :class="linkClasses('invoices.index')">Invoices</Link>
            </li>
            <li>
                <Link :href="route('payments')" :class="linkClasses('payments')">Payments</Link>
            </li>
            <li>
                <Link :href="route('paymentMethods.index')" :class="linkClasses('paymentMethods')">Payment Methods</Link>
            </li>
        </ul>
    </nav>
</template>

<script>
import { Link } from '@inertiajs/vue3';

export default {
    components: {
        Link
    },

    props: {
        active: String,
    },

    methods: {
        linkClasses(linkName) {
            return this.active === linkName
                ? 'font-bold'
                : 'text-indigo-500 hover:text-indigo-700'
        }
    }
}
</script>

