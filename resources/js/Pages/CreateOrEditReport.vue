<script setup>
import AppCard from '@/Components/AppCard.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { dayjsLocal } from '@/helpers';
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button'
import { Label } from '@/Components/ui/label'
import { Input } from '@/Components/ui/input'
import Select from '@/Components/Select.vue'
import { computed } from 'vue';

const props = defineProps({
  organization: Object,
  organizations: Array,
  report: {
    type: Object,
    default: null
  },
  errors: Object
})

const mode = computed(() => props.report ? 'edit' : 'create')
const title = computed(() => mode.value === 'edit' ? 'Edit Report' : 'Create Report')

const form = useForm({
  name: props.report?.name ?? '',
  organization_id: props.organization?.id ?? '',
  starts_at: props.report ? dayjsLocal(props.report.starts_at).format('YYYY-MM-DD') : dayjsLocal().format('YYYY-MM-DD'),
  ends_at: props.report ? dayjsLocal(props.report.ends_at).format('YYYY-MM-DD') : dayjsLocal().add(1, 'day').format('YYYY-MM-DD'),
  report_type: 'custom_report'
})

const submit = () => {

  if (mode.value === 'edit') {
    router.put(route('admin.reports.update', props.report.id), { name: form.name })
    return
  }

  form.post(route('admin.reports.store'))
}

const confirmDelete = (text, url) => {
  if (confirm(text)) {
    router.delete(url)
  }
}
</script>

<template>

  <Head :title="title" />

  <BreezeAuthenticatedLayout :organization="organization">
    <template #header>
      <PageHeader :header="title" />
    </template>

    <AppCard :title="title">
      <form @submit.prevent="submit">
        <div class="space-y-6">
          <div class="flex flex-col gap-2">
            <Label for="name">Name</Label>
            <Input type="text" name="name" v-model="form.name" />
            <div v-if="errors.name" class="bg-red-100 text-red-500 text-sm p-2">{{ errors.name }}</div>
          </div>
          <div v-if="mode === 'create'" class="flex flex-col gap-2">
            <Label for="organization">Organization</Label>
            <Select v-model="form.organization_id" name="organization_id" :items="organizations" placeholder="Select organization" />
            <div v-if="errors.organization_id" class="bg-red-100 text-red-500 text-sm p-2">{{
              errors.organization_id
              }}
            </div>
          </div>
          <div v-if="mode === 'create'" class="flex flex-col gap-2">
            <Label for="starts_at">Starts At</Label>
            <div class="flex flex-col md:flex-row space-y-2 md:space-y-0">
              <Input type="date" name="starts_at" v-model="form.starts_at" class="w-fit" />
            </div>
            <div v-if="errors.starts_at" class="bg-red-100 text-red-500 text-sm p-2">{{ errors.starts_at }}</div>
          </div>
          <div v-if="mode === 'create'" class="flex flex-col gap-2">
            <Label for="ends_at">Ends At</Label>
            <div class="flex flex-col md:flex-row space-y-2 md:space-y-0">
              <Input type="date" name="ends_at" v-model="form.ends_at" class="w-fit" />
            </div>
            <div v-if="errors.ends_at" class="bg-red-100 text-red-500 text-sm p-2">{{ errors.ends_at }}</div>
          </div>
          <Button type="submit">
            {{ mode === 'edit' ? 'Update Report' : 'Create Report' }}
          </Button>
        </div>
      </form>
    </AppCard>

    <AppCard v-if="mode === 'edit'" title="Delete Report"
      description="Delete this report and all of its associated report items and work entries.">
      <Button type="submit" variant="destructive"
        @click="confirmDelete('Delete this report and all attached work?', route('admin.reports.destroy', report.id));">
        Permanently Delete Report
      </Button>
    </AppCard>
  </BreezeAuthenticatedLayout>
</template>