<script setup>
import BreezeGuestLayout from '@/Layouts/Guest.vue'
import BreezeValidationErrors from '@/Components/ValidationErrors.vue'
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button'
import { Label } from '@/Components/ui/label'
import { Input } from '@/Components/ui/input'

const props = defineProps({
  user: Object,
  email: String
})

const form = useForm({
  password: '',
  password_confirmation: '',
  email: props.user.email
})

const submit = () => {
  form.post(window.location.href)
}

</script>

<template>

  <Head title="Set Password" />

  <BreezeGuestLayout>
    <div class="mb-4 text-sm text-gray-600">
      Please set a password to access your account.
    </div>

    <BreezeValidationErrors class="mb-4" />

    <form @submit.prevent="submit">
      <div>
        <Label for="password">Password</Label>
        <Input id="password" type="password" class="mt-2" v-model="form.password" required autofocus
          autocomplete="new-password" />
      </div>
      <div class="mt-4">
        <Label for="password-confirm">Confirm Password</Label>
        <Input id="password-confirm" type="password" class="mt-2"
          v-model="form.password_confirmation" required autofocus autocomplete="new-password" />
      </div>

      <div class="flex justify-end mt-4">
        <Button class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
          Save password and login
        </Button>
      </div>
    </form>
  </BreezeGuestLayout>


</template>