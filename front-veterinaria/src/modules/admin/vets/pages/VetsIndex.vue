<script setup>
import { ref, onMounted, computed } from "vue"
import { useRouter } from "vue-router"
import { useAuthStore } from "@/stores/auth"
import { vetsApi } from "@/services/vets"

const router = useRouter()
const auth = useAuthStore()

const loading = ref(false)
const errorMsg = ref("")
const items = ref([])
const meta = ref(null)

const search = ref("")
const page = ref(1)
const perPage = ref(10)
const active = ref("") // "", "true", "false"

const canCreate = computed(() => auth.can("vets.create"))
const canUpdate = computed(() => auth.can("vets.update"))
const canDelete = computed(() => auth.can("vets.delete"))

const fetchList = async () => {
  loading.value = true
  errorMsg.value = ""
  try {
    const { data } = await vetsApi.list({
      search: search.value || undefined,
      page: page.value,
      per_page: perPage.value,
      active: active.value || undefined,
    })
    items.value = data.data
    meta.value = data
  } catch (err) {
    errorMsg.value = err?.response?.data?.message || "Error cargando veterinarios"
  } finally {
    loading.value = false
  }
}

const goCreate = () => router.push({ name: "admin.vets.create" })
const goEdit = (id) => router.push({ name: "admin.vets.edit", params: { id } })

const removeVet = async (id) => {
  if (!confirm("¿Eliminar veterinario?")) return
  try {
    await vetsApi.remove(id)
    await fetchList()
  } catch (err) {
    alert(err?.response?.data?.message || "No se pudo eliminar")
  }
}

const totalPages = computed(() => meta.value?.last_page || 1)

const nextPage = () => {
  if (page.value < totalPages.value) {
    page.value++
    fetchList()
  }
}
const prevPage = () => {
  if (page.value > 1) {
    page.value--
    fetchList()
  }
}

const onSearch = () => {
  page.value = 1
  fetchList()
}

onMounted(fetchList)
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold">Veterinarios</h1>
        <p class="text-gray-600">Gestión de veterinarios del sistema</p>
      </div>

      <button
        v-if="canCreate"
        class="px-4 py-2 rounded bg-gray-900 text-white hover:opacity-90"
        @click="goCreate"
      >
        + Nuevo
      </button>
    </div>

    <div class="bg-white rounded shadow p-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <input
          v-model="search"
          class="border rounded p-2"
          placeholder="Buscar (nombre, email, documento)"
          @keyup.enter="onSearch"
        />

        <select v-model="active" class="border rounded p-2" @change="onSearch">
          <option value="">Todos</option>
          <option value="true">Activos</option>
          <option value="false">Inactivos</option>
        </select>

        <select v-model.number="perPage" class="border rounded p-2" @change="onSearch">
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
        </select>

        <button class="border rounded p-2 hover:bg-gray-50" @click="onSearch">
          Buscar
        </button>
      </div>
    </div>

    <div v-if="errorMsg" class="p-3 rounded bg-red-50 text-red-700">
      {{ errorMsg }}
    </div>

    <div class="bg-white rounded shadow overflow-hidden">
      <div v-if="loading" class="p-4">Cargando...</div>

      <table v-else class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="text-left p-3">Nombre</th>
            <th class="text-left p-3">Email</th>
            <th class="text-left p-3">Teléfono</th>
            <th class="text-left p-3">Especialidad</th>
            <th class="text-left p-3">Estado</th>
            <th class="text-center p-3">Acciones</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="v in items" :key="v.id" class="border-t">
            <td class="p-3 font-medium">{{ v.full_name }}</td>
            <td class="p-3">{{ v.email || "-" }}</td>
            <td class="p-3">{{ v.phone || "-" }}</td>
            <td class="p-3">{{ v.specialty || "-" }}</td>
            <td class="p-3">
              <span
                class="px-2 py-1 rounded text-xs"
                :class="v.active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600'"
              >
                {{ v.active ? "Activo" : "Inactivo" }}
              </span>
            </td>
            <td class="p-3 text-center space-x-2">
              <button
                v-if="canUpdate"
                class="px-3 py-1 rounded border hover:bg-gray-50"
                @click="goEdit(v.id)"
              >
                Editar
              </button>
              <button
                v-if="canDelete"
                class="px-3 py-1 rounded border hover:bg-red-50"
                @click="removeVet(v.id)"
              >
                Eliminar
              </button>
            </td>
          </tr>

          <tr v-if="items.length === 0">
            <td colspan="6" class="p-4 text-center text-gray-500">
              No hay registros
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="flex items-center justify-between">
      <div class="text-sm text-gray-600">
        Página {{ meta?.current_page || 1 }} de {{ meta?.last_page || 1 }}
      </div>

      <div class="space-x-2">
        <button class="px-3 py-1 border rounded" :disabled="page === 1" @click="prevPage">
          Anterior
        </button>
        <button class="px-3 py-1 border rounded" :disabled="page >= totalPages" @click="nextPage">
          Siguiente
        </button>
      </div>
    </div>
  </div>
</template>
