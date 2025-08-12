<script setup>
import { ref } from 'vue'
import AppCard from '@/Components/AppCard.vue'

const expanded = ref(false)
</script>

<template>
  <AppCard>
    <div class="flex items-center justify-between">
      <slot name="header" />
      <div class="p-3" @click.stop="expanded = !expanded">
        <svg v-if="expanded" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-up" viewBox="0 0 16 16">
          <path fill-rule="evenodd" d="M7.646 4.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1-.708.708L8 5.707l-5.646 5.647a.5.5 0 0 1-.708-.708z"/>
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-down"
          viewBox="0 0 16 16">
          <path fill-rule="evenodd"
            d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708" />
        </svg>
      </div>
    </div>
    <div v-if="expanded">
      <slot />
    </div>
  </AppCard>

</template>

<style scoped></style>