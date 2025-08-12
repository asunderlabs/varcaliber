<script setup>
import BreezeGuestLayout from '@/Layouts/Guest.vue'
import BreezeValidationErrors from '@/Components/ValidationErrors.vue'
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button'
import { Label } from '@/Components/ui/label'
import { Input } from '@/Components/ui/input'

const props = defineProps({
  status: String,
})

const form = useForm({
  email: ''
})

const submit = () => {
  form.post(route('password.email'))
}

</script>

<template>

  <Head title="Forgot Password" />

  <BreezeGuestLayout>
    <div class="mb-4 text-sm text-gray-600">
      Forgot your password? No problem. Just let us know your email address and we will email you a password reset link
      that will allow you to choose a new one.
    </div>

    <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
      {{ status }}
    </div>

    <BreezeValidationErrors class="mb-4" />

    <form @submit.prevent="submit">
      <div>
        <Label for="email">Email</Label>
        <Input id="email" type="email" class="mt-2" v-model="form.email" required autofocus
          autocomplete="username" />
      </div>

      <div class="flex items-center justify-end mt-4">
        <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
          Email Password Reset Link
        </Button>
      </div>
    </form>
  </BreezeGuestLayout>

</template>
