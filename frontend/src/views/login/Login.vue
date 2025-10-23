<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-b from-slate-50 to-white">
    <v-card
      class="w-full max-w-md rounded-2xl"
      elevation="6"
    >
      <v-card-text class="p-6">
        <div class="flex items-center gap-3 mb-6">
          <div class="h-8 w-8 rounded-xl bg-primary/10 flex items-center justify-center">
            <v-icon size="20" color="primary">mdi-lock</v-icon>
          </div>
          <h1 class="text-2xl font-semibold tracking-tight">Sign in</h1>
        </div>

        <v-form v-model="isValid" @submit.prevent="onSubmit" class="space-y-4">
          <v-text-field
            v-model.trim="email"
            :rules="[rules.required, rules.email]"
            label="Email"
            type="email"
            autocomplete="email"
            prepend-inner-icon="mdi-email-outline"
            density="comfortable"
            variant="outlined"
            hide-details="auto"
          />

          <v-text-field
            v-model="password"
            :type="showPassword ? 'text' : 'password'"
            :append-inner-icon="showPassword ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
            @click:append-inner="showPassword = !showPassword"
            :rules="[rules.required, rules.min(6)]"
            label="Password"
            autocomplete="current-password"
            prepend-inner-icon="mdi-lock-outline"
            density="comfortable"
            variant="outlined"
            hide-details="auto"
          />

          <v-btn
            type="submit"
            color="primary"
            class="w-full"
            size="large"
            :loading="auth.loading"
            :disabled="!isValid || auth.loading"
          >
            Sign in
          </v-btn>
        </v-form>
      </v-card-text>
    </v-card>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const isValid = ref(false)

const rules = {
  required: (v: string) => (!!v && v.toString().trim().length > 0) || 'Required',
  email: (v: string) =>
    /.+@.+\..+/.test(v) || 'Invalid email',
  min: (n: number) => (v: string) =>
    (v?.length ?? 0) >= n || `Min ${n} characters`,
}

async function onSubmit() {
  try {
    await auth.login(email.value, password.value)
    router.replace('/notes')
  } catch (e: any) {
    
  }
}
</script>
