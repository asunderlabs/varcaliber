<script setup lang="ts">
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/Components/ui/card'

const props = defineProps({
  title: String,
  description: String,
  footer: String,
})
</script>

<template>
  <Card>
    <CardHeader v-if="title">
      <CardTitle v-if="title">
        {{ title }}
      </CardTitle>
      <CardDescription v-if="description">
        {{ description }}
      </CardDescription>
    </CardHeader>
    <CardContent class="space-y-4">
      <slot />
    </CardContent>
    <CardFooter v-if="footer">
      {{ footer }}
    </CardFooter>
  </Card>
</template>