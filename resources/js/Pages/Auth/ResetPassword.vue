<script setup>
import BreezeGuestLayout from '@/Layouts/Guest.vue'
import BreezeValidationErrors from '@/Components/ValidationErrors.vue'
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button'
import { Label } from '@/Components/ui/label'
import { Input } from '@/Components/ui/input'

const props = defineProps({
  email: String,
  token: String,
})

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.post(route('password.update'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}

</script>


<template>

  <Head title="Reset Password" />

  <BreezeGuestLayout>
    <BreezeValidationErrors class="mb-4" />

    <form @submit.prevent="submit">
      <div>
        <Label for="email">Email</Label>
        <Input id="email" type="email" class="mt-2" v-model="form.email" required autofocus
          autocomplete="username" />
      </div>

      <div class="mt-4">
        <Label for="password">Password</Label>
        <Input id="password" type="password" class="mt-2" v-model="form.password" required
          autocomplete="new-password" />
      </div>

      <div class="mt-4">
        <Label for="password_confirmation">Confirm Password</Label>
        <Input id="password_confirmation" type="password" class="mt-2"
          v-model="form.password_confirmation" required autocomplete="new-password" />
      </div>

      <div class="flex items-center justify-end mt-4">
        <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
          Reset Password
        </Button>
      </div>
    </form>
  </BreezeGuestLayout>
</template>
