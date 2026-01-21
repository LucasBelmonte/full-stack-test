<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { watch } from 'vue'

/* Breadcrumbs */
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Customers', href: '/customers' },
  { title: 'Create', href: '/customers/create' },
]

/* Form */
const form = useForm({
  name: '',
  email: '',
  phone: '',
  address: '',
  document: '',
})

/* Máscara CPF / CNPJ */
function maskCpfCnpj(value: string): string {
  if (!value) return ''

  const numbers = value.replace(/\D/g, '')

  // CPF
  if (numbers.length <= 11) {
    return numbers
      .replace(/(\d{3})(\d)/, '$1.$2')
      .replace(/(\d{3})(\d)/, '$1.$2')
      .replace(/(\d{3})(\d{1,2})$/, '$1-$2')
  }

  // CNPJ
  return numbers
    .replace(/^(\d{2})(\d)/, '$1.$2')
    .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
    .replace(/\.(\d{3})(\d)/, '.$1/$2')
    .replace(/(\d{4})(\d{1,2})$/, '$1-$2')
}

/* Watch da máscara */
watch(
  () => form.document,
  (value) => {
    form.document = maskCpfCnpj(value)
  }
)

/* Submit */
const submit = () => {
  form.post('/customers')
}
</script>

<template>
    <Head title="Create Customer" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <h1 class="text-3xl font-bold">Create Customer</h1>

            <form @submit.prevent="submit" class="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Customer Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">Name *</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    placeholder="John Doe"
                                    required
                                />
                                <p v-if="form.errors.name" class="text-sm text-destructive">
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="email">Email *</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    v-model="form.email"
                                    placeholder="john@example.com"
                                    required
                                />
                                <p v-if="form.errors.email" class="text-sm text-destructive">
                                    {{ form.errors.email }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="phone">Phone</Label>
                                <Input
                                    id="phone"
                                    v-model="form.phone"
                                    placeholder="+1 (555) 123-4567"
                                />
                                <p v-if="form.errors.phone" class="text-sm text-destructive">
                                    {{ form.errors.phone }}
                                </p>
                            </div>

                          <div class="space-y-2">
                            <Label for="document">CPF/CNPJ</Label>

                            <Input
                              id="document"
                              v-model="form.document"
                              placeholder="CPF ou CNPJ"
                              maxlength="18"
                            />

                            <p v-if="form.errors.document" class="text-sm text-destructive">
                              {{ form.errors.document }}
                            </p>
                          </div>

                            <div class="space-y-2 md:col-span-2">
                                <Label for="address">Address</Label>
                                <Textarea
                                    id="address"
                                    v-model="form.address"
                                    placeholder="123 Main St, Apt 4B&#10;New York, NY 10001"
                                    rows="3"
                                />
                                <p v-if="form.errors.address" class="text-sm text-destructive">
                                    {{ form.errors.address }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div class="flex justify-end gap-2">
                    <Button type="button" variant="outline" @click="router.visit('/customers')">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Creating...' : 'Create Customer' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
