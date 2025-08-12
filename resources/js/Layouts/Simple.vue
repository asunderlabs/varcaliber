<template>
  <div class="bg-gray-50">
    <div class="min-h-screen bg-gray-50 page">
      <!-- Page Content -->
      <main>
        <div class="flex flex-col items-center justify-center md:py-6 md:px-6">
          <div class="flex flex-col w-full max-w-2xl bg-white p-6 md:p-12 md:rounded-lg justify-center items-center w-full">
            <slot />
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script>

import NavLink from '@/Components/NavLink.vue'
import { Link } from '@inertiajs/vue3';

export default {
    components: {
      NavLink,
      Link,
    },

    props: {
        isAdmin: Boolean,
        organization: Object,
        fullWidth: Boolean
    },

    data() {
        return {
        }
    }
}
</script>
