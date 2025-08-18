<script setup>
import dayjs from 'dayjs'
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { Head, useForm } from '@inertiajs/vue3';
import PageHeader from '@/Components/PageHeader.vue'
import { onMounted } from 'vue';
import { Button } from '@/Components/ui/button'
import { Label } from '@/Components/ui/label'
import { Input } from '@/Components/ui/input'
import Select from '@/Components/Select.vue'
import AppCard from '@/Components/AppCard.vue';

const props = defineProps({
  organizations: Array,
  billingStart: String,
  billingEnd: String,
  errors: Object
})

const title = "Create Invoice"

const form = useForm({
  organization_id: null,
  billingStart: null,
  billingEnd: null,
  issueAt: null,
  dueAt: null,
  delivery: 'automatic'
})

onMounted(() => {
  resetDates()
})

const onSubmit = () => {
  form.post(route('admin.invoices.store'))
}

const changeDelivery = () => {
  console.log('changedeliver')
  if (form.delivery === 'manual') {
    form.billingStart = form.billingEnd = form.issueAt = dayjs().format('YYYY-MM-DD')
    form.dueAt = dayjs().add(7, 'day').format('YYYY-MM-DD')
    return
  }

  resetDates()
}

const resetDates = () => {
  form.billingStart = dayjs.tz(props.billingStart).tz('America/Chicago').format('YYYY-MM-DD')
  form.billingEnd = dayjs.tz(props.billingEnd).tz('America/Chicago').format('YYYY-MM-DD')
  form.issueAt = dayjs.tz(props.billingEnd).tz('America/Chicago').add(1, 'day').format('YYYY-MM-DD')
  form.dueAt = dayjs.tz(props.billingEnd).tz('America/Chicago').add(4, 'day').format('YYYY-MM-DD')
}

const deliveryOptions = [
  {
    id: 'automatic',
    name: 'Automatic'
  },
  {
    id: 'manual',
    name: 'Manual'
  },
]
</script>

<template>

  <Head :title="title" />

  <BreezeAuthenticatedLayout>
    <template #header>
      <PageHeader :header="title"></PageHeader>
    </template>

    <AppCard :title="title">
      <form @submit.prevent="onSubmit">
        <div class="flex flex-col">
          <div class="mb-4 space-y-6">
            <div class="flex flex-col gap-2">
              <Label for="organization_id">Organization</Label>
              <Select v-model="form.organization_id" name="organization_id" :items="organizations"
                placeholder="Select organization" />
              <div v-if="errors.organization_id" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{
                errors.organization_id
              }}</div>
            </div>

            <div class="flex flex-col gap-2">
              <Label for="delivery">Delivery</Label>
              <Select v-model="form.delivery" name="delivery" :items="deliveryOptions"
                placeholder="Select organization" @update:modelValue="changeDelivery()"/>
              <div v-if="errors.delivery" class="bg-red-100 text-red-500 text-sm mt-2 p-2">{{ errors.delivery }}</div>
            </div>

            <div class="flex flex-col gap-2">
              <Label for="billingStart">Billing Start</Label>
              <Input type="date" name="billingStart" v-model="form.billingStart" class="w-fit" />
              <div v-if="errors.billingStart || errors.billingStart"
                class="bg-red-100 text-red-500 text-sm mt-2 p-2 flex flex-col">
                <span>{{ errors.billingStart }}</span>
              </div>
            </div>

            <div class="flex flex-col gap-2">
              <Label for="billingEnd">Billing End</Label>
              <Input type="date" name="billingEnd" v-model="form.billingEnd" class="w-fit" />
              <div v-if="errors.billingEnd || errors.billingEnd"
                class="bg-red-100 text-red-500 text-sm mt-2 p-2 flex flex-col">
                <span>{{ errors.billingEnd }}</span>
              </div>
            </div>

            <div class="flex flex-col gap-2">
              <Label for="issueAt">Issue At</Label>
              <Input type="date" name="issueAt" v-model="form.issueAt" class="w-fit" />
              <div v-if="errors.issueAt || errors.issueAt"
                class="bg-red-100 text-red-500 text-sm mt-2 p-2 flex flex-col">
                <span>{{ errors.issueAt }}</span>
              </div>
            </div>

            <div class="flex flex-col gap-2">
              <Label for="dueAt">Due At</Label>
              <Input type="date" name="dueAt" v-model="form.dueAt" class="w-fit" />
              <div v-if="errors.dueAt || errors.dueAt" class="bg-red-100 text-red-500 text-sm mt-2 p-2 flex flex-col">
                <span>{{ errors.dueAt }}</span>
              </div>
            </div>

            <div>
              <Button type="submit">Create
                Invoice</Button>
            </div>
          </div>
        </div>
      </form>
    </AppCard>
  </BreezeAuthenticatedLayout>
</template>
