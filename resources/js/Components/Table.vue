<script setup>
import { Link, router } from '@inertiajs/vue3'
import { computed } from 'vue';

const props = defineProps({
  columns: Array,
  rows: Array,
  options: Object,
  emptyText: String
})

const tableClass = computed(() => {
  let classes = []

  if (!props.options || !props.options.noRowLines) {
    classes.push('divide-y')
    classes.push('divide-gray-200')
  }

  return classes.join(' ')
})

const tbodyClass = computed(() => {
  let classes = []

  if (!props.options || !props.options.noRowLines) {
    classes.push('divide-y')
    classes.push('divide-gray-200')
  }

  return classes.join(' ')
})

const rowClass = computed(() => {
  let classes = []

  if (!props.options || !props.options.noRowHover) {
    classes.push('hover:bg-gray-50')
  }

  return classes.join(' ')
})

const cellClass = computed(() => {
  let classes = []

  if (props.options && props.options.cellClass) {
    classes.push(props.options.cellClass)
  }

  return classes
})

const confirmDelete = (text, action) => {
  if (confirm(text)) {
    router.delete(action.href)
  }
}
</script>

<template>
  <div>
    <table v-if="rows && rows.length" class="w-full print:border" :class="tableClass">
      <thead v-if="columns">
        <tr class="text-gray-400">
          <th v-for="header in columns" :key="header" align="left" class="py-2 px-2 text-xs uppercase">{{ header }}</th>
        </tr>
      </thead>
      <tbody :class="tbodyClass">
        <tr v-for="(row, rowIndex) in rows" :key="rowIndex" class="text-sm" :class="rowClass">
          <td v-for="(column, columnIndex) in row.columns" :key="rowIndex + '.' + columnIndex" class="py-2 px-2"
            :class="cellClass" align="left">
            <div v-if="column.component">
              <component :is="column.component" :data="column.data" />
            </div>
            <div v-else-if="column.actions" :class="column.actions.classes ?? ''">
              <ul class="whitespace-nowrap flex space-x-4"
                :class="{ 'justify-end': columnIndex === (row.columns.length - 1) }">
                <li v-for="(action, actionIndex) in column.actions" :key="actionIndex" :class="action.classes">
                  <a v-if="action.confirmDelete" href="javascript:void(0);"
                    @click="confirmDelete(action.confirmDelete, action);"
                    class="text-indigo-500 hover:text-indigo-700 underline">
                    {{ action.label }}
                  </a>
                  <a v-else-if="action.rawLink" :href="action.href"
                    class="text-indigo-500 hover:text-indigo-700 underline" :target="action.newTab ? '_blank' : ''">
                    {{ action.label }}
                  </a>
                  <Link v-else :href="action.href" class="text-indigo-500 hover:text-indigo-700 underline" as="button"
                    :method="action.method ? action.method : 'get'" @click="action.event ? $emit(action.event) : null">
                  {{ action.label }}
                  </Link>
                </li>
              </ul>
            </div>
            <div v-else class="flex flex-col" :class="column.classes ? column.classes : ''" :title="column.tooltip">
              <div class="flex items-center space-x-2" :class="column.textClasses ?? ''">
                <span v-if="column.text">{{ column.text }}</span>
                <div v-if="column.badge" :class="column.badge.class ?? 'badge'">{{ column.badge.text }}</div>
              </div>
              <div v-if="column.subtext" class="column-subtext">{{ column.subtext }}</div>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
    <p v-else class="text-gray-600">{{ emptyText ?? 'No records' }}</p>
  </div>
</template>

<style scoped>
.lastColumnIsAmount tbody>tr>td:last-child {
  width: 100px;
  text-align: right;
}
</style>
