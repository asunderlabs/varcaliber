<script setup>
import { computed } from 'vue'
import { dayjsLocal } from '@/helpers';

const props = defineProps({
  data: Object
})

const hours = computed(() => (Math.round(props.data.workEntry.minutes / 60 * 100) / 100).toFixed(2))

const subtext = computed(() => {
  let items = [props.data.workEntry.organization.name]

  if (props.data.workEntry.report_item?.report?.name) {
    items.push(props.data.workEntry.report_item.report.name)
  } else {
    items.push('Unreported')
  }

  if (props.data.workEntry.issue) {
    items.push('Issues')
    items.push(props.data.workEntry.issue.title )
  } else if (props.data.workEntry.is_remote_interaction) {
    items.push('Remote Interactions')
  }

  return items.join(' / ')
})

</script>
<template>
  <div class="flex items-center space-x-4">
    <div :title="data.workEntry.report_item_id ? 'Reported' : 'Unreported'">
      <svg v-if="data.workEntry.report_item_id" title="Reported" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
        fill="currentColor" class="bi bi-check-circle-fill text-emerald-400" viewBox="0 0 16 16">
        <path
          d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
      </svg>
      <svg v-else title="Not reported" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
        class="bi bi-dash-circle-fill text-gray-300" viewBox="0 0 16 16">
        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M4.5 7.5a.5.5 0 0 0 0 1h7a.5.5 0 0 0 0-1z" />
      </svg>
    </div>
    <div>
      <div class="flex flex-col md:flex-row md:items-center md:space-x-1">
        <div class="font-bold">
          {{ dayjsLocal(data.workEntry.starts_at).format('dddd, M/D/YYYY') }}
        </div>
        <div class="hidden md:block">&mdash;</div>
        <div>{{ data.workEntry.description }} ({{ hours }} hrs)</div>
      </div>
      <div class="italic text-gray-500 mt-1 text-xs">
        {{ subtext }}
      </div>
    </div>
  </div>
</template>

<style scoped>

.truncate-text {
  display: inline-block;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  width: 150px; /* or any container width */
}

@media screen and (min-width: 768px) {
  .truncate-text {
    display: inline-block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    width: 400px; /* or any container width */
  }
}


</style>