<script setup>
import BreezeCheckbox from '@/Components/Checkbox.vue'
import BreezeGuestLayout from '@/Layouts/Guest.vue'
import BreezeValidationErrors from '@/Components/ValidationErrors.vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button'
import { Label } from '@/Components/ui/label'
import { Input } from '@/Components/ui/input'

const props = defineProps({
  canResetPassword: Boolean,
  status: String,
})

const page = usePage()

const form = useForm({
  email: page.props.app.env === 'local' ? 'test@example.com' : '',
  password: page.props.app.env === 'local' ? '12341234' : '',
  remember: false
})

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  })
}

</script>

<template>

  <Head title="Log in" />

  <BreezeGuestLayout>

    <BreezeValidationErrors class="mb-4" />

    <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
      {{ status }}
    </div>

    <form @submit.prevent="submit" class="py-2">
      <div>
        <Label for="email">Email</Label>
        <Input id="email" type="email" class="mt-2" v-model="form.email" required autofocus autocomplete="username" />
      </div>

      <div class="mt-4">
        <Label for="password">Password</Label>
        <Input id="password" type="password" class="mt-2" v-model="form.password" required
          autocomplete="current-password" />
      </div>

      <div class="block mt-4">
        <label class="flex items-center">
          <BreezeCheckbox name="remember" v-model:checked="form.remember" />
          <span class="ml-2 text-sm text-gray-600">Remember me</span>
        </label>
      </div>

      <div class="flex items-center justify-end mt-4">
        <Link v-if="canResetPassword" :href="route('password.request')"
          class="underline text-sm text-gray-600 hover:text-gray-900">
        Forgot your password?
        </Link>

        <Button class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
          Log in
        </Button>
      </div>
    </form>
  </BreezeGuestLayout>
</template>
