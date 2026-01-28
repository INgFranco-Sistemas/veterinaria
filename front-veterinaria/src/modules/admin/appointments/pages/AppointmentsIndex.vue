<script setup>
import { ref, onMounted, computed } from "vue"
import { useRouter } from "vue-router"
import { useAuthStore } from "@/stores/auth"
import { appointmentsApi } from "@/services/appointments"

const router = useRouter()
const auth = useAuthStore()

const canCreate = computed(() => auth.can("appointments.create"))
const canCancel = computed(() => auth.can("appointments.cancel"))

const loading = ref(false)
const errorMsg = ref("")
const items = ref([])
const meta = ref(null)

const search = ref("")
const status = ref("")
const date = ref("")
const page = ref(1)
const perPage = ref(10)

const fetchList = async () => {
  loading.value = true
  errorMsg.value = ""
  try {
    const { data } = await appointmentsApi.list({
      search: search.value || undefined,
      status: status.value || undefined,
      date: date.value || undefined,
      page: page.value,
      per_page: perPage.value,
    })
    items.value = data.data
    meta.value = data
  } catch (err) {
    errorMsg.value = err?.response?.data?.message || "Error cargando citas"
  } finally {
    loading.value = false
  }
}

const onSearch = () => {
  page.value = 1
  fetchList()
}

const goCreate = () => router.push({ name: "admin.appointments.create" })

const cancelAppt = async (id) => {
  if (!confirm("¿Cancelar cita?")) return
  try {
    await appointmentsApi.cancel(id, { notes: "Cancelada desde admin" })
    await fetchList()
  } catch (err) {
    alert(err?.response?.data?.message || "No se pudo cancelar")
  }
}

onMounted(fetchList)
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold">Citas</h1>
        <p class="text-gray-600">Reservas del sistema</p>
      </div>

      <button v-if="canCreate" class="px-4 py-2 rounded bg-gray-900 text-white" @click="goCreate">
        + Nueva cita
      </button>
    </div>

    <div class="bg-white rounded shadow p-4">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <input v-model="search" class="border rounded p-2" placeholder="Buscar (cliente, mascota, vet)" @keyup.enter="onSearch" />
        <select v-model="status" class="border rounded p-2" @change="onSearch">
          <option value="">Todos</option>
          <option value="reserved">Reservado</option>
          <option value="paid">Pagado</option>
          <option value="attended">Atendido</option>
          <option value="cancelled">Cancelado</option>
          <option value="no_show">No asistió</option>
        </select>
        <input v-model="date" type="date" class="border rounded p-2" @change="onSearch" />
        <select v-model.number="perPage" class="border rounded p-2" @change="onSearch">
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
        </select>
        <button class="border rounded p-2 hover:bg-gray-50" @click="onSearch">Filtrar</button>
      </div>
    </div>

    <div v-if="errorMsg" class="p-3 rounded bg-red-50 text-red-700">{{ errorMsg }}</div>

    <div class="bg-white rounded shadow overflow-hidden">
      <div v-if="loading" class="p-4">Cargando...</div>

      <table v-else class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="text-left p-3">Fecha/Hora</th>
            <th class="text-left p-3">Cliente</th>
            <th class="text-left p-3">Mascota</th>
            <th class="text-left p-3">Veterinario</th>
            <th class="text-left p-3">Estado</th>
            <th class="text-right p-3">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="a in items" :key="a.id" class="border-t">
            <td class="p-3">{{ new Date(a.starts_at).toLocaleString() }}</td>
            <td class="p-3">{{ a.client?.full_name }}</td>
            <td class="p-3">{{ a.pet?.name }}</td>
            <td class="p-3">{{ a.veterinarian?.full_name }}</td>
            <td class="p-3">
              <span class="px-2 py-1 rounded text-xs bg-gray-100 text-gray-700">{{ a.status }}</span>
            </td>
            <td class="p-3 text-right">
              <button
                v-if="canCancel && a.status !== 'cancelled'"
                class="px-3 py-1 rounded border hover:bg-red-50"
                @click="cancelAppt(a.id)"
              >
                Cancelar
              </button>
            </td>
          </tr>

          <tr v-if="items.length === 0">
            <td colspan="6" class="p-4 text-center text-gray-500">No hay registros</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
