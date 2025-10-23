<template>
  <div class="min-h-screen flex flex-col p-6">
    <v-card class="pa-3 border">
      <div class="flex items-center gap-3 mb-3">
        <div class="h-7 w-1.5 rounded bg-blue-500"></div>
        <p class="text-3xl font-medium">Notes</p>
      </div>

      <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 mb-3">
        <div class="w-full">
          <v-text-field
            v-model="search"
            prepend-inner-icon="mdi-magnify"
            label="Search"
            density="comfortable"
            variant="outlined"
            hide-details
            class="w-full"
          />
        </div>

        <div class="flex flex-row-reverse gap-2 w-full md:w-auto md:justify-end">
          <v-btn density="comfortable" class="ml-2 bg-white" color="primary" @click="createOpen = true">
            Create
          </v-btn>

          <v-btn
            density="comfortable"
            class="ml-2 bg-white"
            :disabled="disableEdit"
            color="secondary"
            @click="editOpen = true"
          >
            Edit
          </v-btn>

          <v-btn
            density="comfortable"
            class="ml-2 bg-white"
            :disabled="disableDelete"
            color="error"
            @click="onDelete()"
          >
            Delete
          </v-btn>

          <v-btn density="comfortable" class="ml-2 bg-white" color="error" @click="logout">Logout</v-btn>
        </div>
      </div>

      <v-data-table
        class="border pa-2"
        :headers="headers"
        :items="items"
        :loading="loading"
        show-select
        item-value="id"
        :single-select="false"
        v-model="selectedIds"

        :page="page"
        :items-per-page="perPage"
        :sort-by="sortBy"

        :server-items-length="total"

        @update:page="onPage"
        @update:items-per-page="onItemsPerPage"
        @update:sort-by="onSort"
      >
        <template #top>
          <v-progress-linear v-if="loading" indeterminate color="primary" />
        </template>

        <template #item.edit="{ item }">
          <v-btn size="small" variant="tonal" color="primary" @click="openRowEdit(item)">EDIT</v-btn>
        </template>
        <template #item.delete="{ item }">
          <v-btn size="small" variant="tonal" color="error" class="ml-2" @click="onDelete(item)">
            DELETE
          </v-btn>
        </template>

        <template #no-data>
          <div class="py-10 text-center opacity-70">No notes. Click “Create”.</div>
        </template>
      </v-data-table>
    </v-card>
  </div>

  <NoteCreateDialog
    v-model="createOpen"
    :loading="saving"
    @submit="handleCreateSubmit"
  />

  <NoteEditDialog
    v-model="editOpen"
    :note="selected"
    :loading="saving"
    @submit="handleEditSubmit"
  />
  <v-snackbar
    v-model="snack.show"
    :color="snack.color"
    :timeout="2500"
    location="bottom center"
    elevation="2"
  >
    {{ snack.text }}

    <template #actions>
      <v-btn variant="text" @click="snack.show = false">Close</v-btn>
    </template>
  </v-snackbar>
</template>

<script setup lang="ts">
import { ref, computed, reactive, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/api/http'
import { useAuthStore } from '@/stores/auth'
import NoteCreateDialog, { type CreateNoteDto } from './NoteCreateDialog.vue'
import NoteEditDialog, { type Note, type EditNoteDto } from './NoteEditDialog.vue'

const router = useRouter()
const auth = useAuthStore()

const loading = ref(false)
const saving = ref(false)

let searchTimer: number | undefined
const search = ref('')
const page = ref(1)
const perPage = ref(10)
const total = ref(0)
const sortBy  = ref<{ key: string; order: 'asc' | 'desc' }[]>([
  { key: 'id', order: 'desc' }
])

function onPage(p: number) {
  page.value = p
  load()
}
function onItemsPerPage(n: number) {
  perPage.value = n
  page.value = 1
  load()
}
type SortItem = { key: string; order: 'asc' | 'desc' }
function onSort(s: SortItem[]) {
  sortBy.value = s
  page.value = 1
  load()
}

const createOpen = ref(false)
const editOpen   = ref(false)

const items = ref<Note[]>([])
const selectedIds = ref<Array<number | string>>([])

const selected = computed<Note | null>(() => {
  if (selectedIds.value.length !== 1) return null
  const id = Number(selectedIds.value[0])
  return items.value.find(x => x.id === id) ?? null
})

const selectedCount = computed(() => selectedIds.value.length)
const disableEdit   = computed(() => selectedCount.value !== 1)
const disableDelete = computed(() => selectedCount.value === 0)

const headers = [
  { key: 'data-table-select', title: '', sortable: false, width: 48 },
  { title: 'ID', key: 'id', width: 80, headerProps: { class: 'font-semibold' } },
  { title: 'Title', key: 'title', sortable: true, headerProps: { class: 'font-semibold' } },
  { title: 'Content', key: 'content', headerProps: { class: 'font-semibold' } },
  { title: 'Edit', key: 'edit', sortable: false, width: 120, headerProps: { class: 'font-semibold' } },
  { title: 'Delete', key: 'delete', sortable: false, width: 120, headerProps: { class: 'font-semibold' } },
]

const snack = reactive({ show: false, color: 'success', text: '' })
function toastOk(msg = 'OK')  { snack.text = msg; snack.color = 'success'; snack.show = true }
function toastErr(msg = 'FAIL'){ snack.text = msg; snack.color = 'error'  ; snack.show = true }

async function load() {
  loading.value = true
  try {
    const params = {
      page: page.value,
      per_page: perPage.value,
      search: search.value?.trim() || '',
      sort: sortBy.value[0]?.key ?? 'id',
      order: sortBy.value[0]?.order ?? 'desc'
    }

    const { data } = await http.get('/notes', {params})
    items.value = (data?.data ?? data) as Note[]
    total.value   = data.meta?.total ?? 0
    page.value    = data.meta?.current_page ?? page.value
    perPage.value = data.meta?.per_page ?? perPage.value
  } catch (e: any) {
    toastErr(e?.message || 'Failed to load notes')
  } finally {
    loading.value = false
  }
}

function openRowEdit(row: any) {
  const raw: Note | undefined = row?.raw ?? row
  if (!raw) return
  selectedIds.value = [raw.id]
  editOpen.value = true
}

async function handleCreateSubmit(payload: CreateNoteDto) {
  saving.value = true
  try {
    const { data } = await http.post('/notes', payload)
    const created = (data?.data ?? data) as Note
    items.value.unshift(created)
    createOpen.value = false
    toastOk('Created Note')
  } catch (e: any) {
    toastErr(e?.message || 'Failed to create note')
  } finally {
    saving.value = false
  }
}

async function handleEditSubmit(payload: EditNoteDto) {
  saving.value = true
  try {
    const { id, ...body } = payload
    const { data } = await http.put(`/notes/${id}`, body)
    const updated = (data?.data ?? data) as Note
    const idx = items.value.findIndex(x => x.id === updated.id)
    if (idx >= 0) items.value[idx] = updated
    editOpen.value = false
    toastOk('Edit note successfully')
    selectedIds.value = []
  } catch (e: any) {
    toastErr('Failed to edit note')
  } finally {
    saving.value = false
  }
}

function getRowId(row: any): number | null {
  const id = row?.id ?? row?.raw?.id
  return id == null ? null : Number(id)
}

async function onDelete(row?: any) {
  const ids: number[] = row
    ? (() => {
        const rid = getRowId(row)
        return rid == null ? [] : [rid]
      })()
    : selectedIds.value.map(x => Number(x))

  if (!ids.length) return
  if (!confirm(`Delete ${ids.length} note(s)?`)) return

  try {
    await Promise.all(ids.map(id => http.delete(`/notes/${id}`)))
    await load()
    selectedIds.value = []
    toastOk('Notes deleted')
  } catch {
    toastErr('Failed to delete note')
  }
}

function logout() {
  auth.logout()
  router.replace({ name: 'login' })
}

onMounted(load)

watch(search, () => {
  if (searchTimer) window.clearTimeout(searchTimer)
  searchTimer = window.setTimeout(() => {
    page.value = 1
    load()
  }, 400)
})
</script>
