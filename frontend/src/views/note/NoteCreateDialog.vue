<template>
  <v-dialog :model-value="modelValue" @update:model-value="emit('update:modelValue', $event)" max-width="560">
    <v-card>
      <v-card-title>Create Note</v-card-title>
      <v-card-text>
        <v-form ref="formRef" @submit.prevent="onSubmit">
          <v-text-field
            v-model="form.title"
            label="Title"
            :rules="[rules.required]"
            variant="outlined"
            density="comfortable"
            class="mb-3"
            autofocus
          />
          <v-textarea
            v-model="form.content"
            label="Content"
            :rules="[rules.required]"
            auto-grow
            rows="4"
            variant="outlined"
            density="comfortable"
          />
          <button type="submit" class="hidden"></button>
        </v-form>
      </v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn variant="text" @click="emit('update:modelValue', false)">Cancel</v-btn>
        <v-btn color="primary" :loading="loading" @click="onSubmit">Save</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'

export type CreateNoteDto = { title: string; content: string }

const props = defineProps<{
  modelValue: boolean
  loading?: boolean
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', v: boolean): void
  (e: 'submit', payload: CreateNoteDto): void
}>()

const formRef = ref()
const form = ref<CreateNoteDto>({ title: '', content: '' })

watch(
  () => props.modelValue,
  (open) => {
    if (open) form.value = { title: '', content: '' }
  }
)

const rules = {
  required: (v: any) => (!!v && String(v).trim().length > 0) || 'Required',
}

async function onSubmit() {
  const ok = await formRef.value?.validate?.()
  if (ok?.valid === false) return
  emit('submit', { ...form.value })
}
</script>
