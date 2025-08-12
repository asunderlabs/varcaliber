<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import PageHeader from '@/Components/PageHeader.vue'
import { Button } from '@/Components/ui/button'
import { Label } from '@/Components/ui/label'
import { Input } from '@/Components/ui/input'
import Select from '@/Components/Select.vue'
import { computed } from 'vue'
import AppCard from '@/Components/AppCard.vue'

const props = defineProps({
  issue: Object,
  organizations: Array,
  errors: Object
})

const mode = computed(() => props.issue ? 'edit' : 'create')
const title = computed(() => mode.value === 'edit' ? 'Edit Issue' : 'Create Issue')

const form = useForm({
  title: props.issue ? props.issue.title : '',
  organization_id: '',
})

const onSubmit = () => {
  if (mode.value === 'edit') {
    form.put(route('admin.issues.update', props.issue.id))
    return
  }

  form.post(route('admin.issues.store'))
}

const confirmDelete = (text, url) => {
  if (confirm(text)) {
    router.delete(url)
  }
}

</script>

<template>

  <Head :title="title" />

  <BreezeAuthenticatedLayout :isAdmin="true">
    <template #header>
      <PageHeader :header="title"></PageHeader>
    </template>

    <AppCard :title="title">
      <form @submit.prevent="onSubmit">
        <div class="space-y-6">
          <div class="flex flex-col gap-2">
            <Label for="title">Title</Label>
            <Input type="text" name="title" v-model="form.title" />
            <div v-if="errors.title" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{ errors.title }}</div>
          </div>
          <div v-if="mode === 'create'" class="flex flex-col gap-2">
            <Label for="organization_id">Organization</Label>
            <Select v-model="form.organization_id" name="organization_id" :items="organizations"
              placeholder="Select organization" />
            <div v-if="errors.organization_id" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{
              errors.organization_id
            }}</div>
          </div>
          <Button type="submit">Submit</Button>
        </div>
      </form>
    </AppCard>

    <AppCard v-if="mode === 'edit'" title="Delete Issue"
      description="Delete this issue and all of its associated report items and work entries.">
      <Button type="submit" variant="destructive"
        @click="confirmDelete('Delete this issue and all attached work?', route('admin.issues.destroy', issue.id));">
        Permanently Delete Issue
      </Button>
    </AppCard>
  </BreezeAuthenticatedLayout>
</template>