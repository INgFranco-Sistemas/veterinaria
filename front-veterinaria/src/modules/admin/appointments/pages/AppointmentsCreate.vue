<script setup>
import { ref, onMounted, computed, watch } from "vue"
import { useRouter } from "vue-router"
import { clientsApi } from "@/services/clients"
import { petsApi } from "@/services/pets"
import { vetsApi } from "@/services/vets"
import { slotsApi } from "@/services/slots"
import { appointmentsApi } from "@/services/appointments"

const router = useRouter()

const loading = ref(false)
const errorMsg = ref("")
const successMsg = ref("")

const clients = ref([])
const pets = ref([])
const vets = ref([])
const slots = ref([])

const form = ref({
  client_id: "",
  pet_id: "",
  veterinarian_id: "",
  date: "",
  slot_id: "",
  reason: "",
})

const fetchClients = async () => {
  const { data } = await clientsApi.list({ per_page: 100 })
  clients.value = data.data
}

const fetchVets = async () => {
  const { data } = await vetsApi.list({ per_page: 100 })
  vets.value = data.data
}

const fetchPets = async () => {
  if (!form.value.client_id) {
    pets.value = []
    form.value.pet_id = ""
    return
  }
  const { data } = await petsApi.list({ client_id: form.value.client_id, per_page: 200 })
  pets.value = data.data
  // si la mascota seleccionada ya no pertenece, reset
  if (!pets.value.find(p => String(p.id) === String(form.value.pet_id))) {
    form.value.pet_id = ""
  }
}

const fetchSlots = async () => {
  slots.value = []
  form.value.slot_id = ""

  if (!form.value.veterinarian_id || !form.value.date) return

  const { data } = await slotsApi.list({
    veterinarian_id: form.value.veterinarian_id,
    service_type: "appointment",
    date: form.value.date,
    status: "available",
    per_page: 300,
  })

  slots.value = data.data
}

watch(() => form.value.client_id, fetchPets)
watch(() => [form.value.veterinarian_id, form.value.date], fetchSlots)

const save = async () => {
  loading.value = true
  errorMsg.value = ""
  successMsg.value = ""
  try {
    if (!form.value.client_id || !form.value.pet_id || !form.value.veterinarian_id || !form.value.date || !form.value.slot_id) {
      throw new Error("Completa todos los campos requeridos.")
    }

    await appointmentsApi.create({
      client_id: Number(form.value.client_id),
      pet_id: Number(form.value.pet_id),
      veterinarian_id: Number(form.value.veterinarian_id),
      slot_id: Number(form.value.slot_id),
      reason: form.value.reason || null,
    })

    successMsg.value = "Cita creada correctamente."
    router.push({ name: "admin.appointments.index" })
  } catch (err) {
    errorMsg.value = err?.response?.data?.message || err.message || "Error creando cita"
  } finally {
    loading.value = false
  }
}

const cancel = () => router.push({ name: "admin.appointments.index" })

onMounted(async () => {
  await Promise.all([fetchClients(), fetchVets()])
})
</script>

<template>
  <div class="space-y-4">
    <div>
      <h1 class="text-2xl font-bold">Nueva cita</h1>
      <p class="text-gray-600">Selecciona cliente, mascota, veterinario y horario</p>
    </div>

    <div v-if="errorMsg" class="p-3 rounded bg-red-50 text-red-700">{{ errorMsg }}</div>
    <div v-if="successMsg" class="p-3 rounded bg-green-50 text-green-700">{{ successMsg }}</div>

    <div class="bg-white rounded shadow p-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm text-gray-700">Cliente *</label>
          <select v-model="form.client_id" class="w-full border rounded p-2">
            <option value="">-- Seleccionar --</option>
            <option v-for="c in clients" :key="c.id" :value="String(c.id)">{{ c.full_name }}</option>
          </select>
        </div>

        <div>
          <label class="text-sm text-gray-700">Mascota *</label>
          <select v-model="form.pet_id" class="w-full border rounded p-2" :disabled="!form.client_id">
            <option value="">-- Seleccionar --</option>
            <option v-for="p in pets" :key="p.id" :value="String(p.id)">{{ p.name }} ({{ p.species }})</option>
          </select>
        </div>

        <div>
          <label class="text-sm text-gray-700">Veterinario *</label>
          <select v-model="form.veterinarian_id" class="w-full border rounded p-2">
            <option value="">-- Seleccionar --</option>
            <option v-for="v in vets" :key="v.id" :value="String(v.id)">{{ v.full_name }}</option>
          </select>
        </div>

        <div>
          <label class="text-sm text-gray-700">Fecha *</label>
          <input v-model="form.date" type="date" class="w-full border rounded p-2" />
        </div>

        <div class="md:col-span-2">
          <label class="text-sm text-gray-700">Horario disponible *</label>
          <select v-model="form.slot_id" class="w-full border rounded p-2" :disabled="slots.length === 0">
            <option value="">
              {{ slots.length ? "-- Seleccionar --" : "No hay slots disponibles" }}
            </option>
            <option v-for="s in slots" :key="s.id" :value="String(s.id)">
              {{ new Date(s.starts_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
              -
              {{ new Date(s.ends_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
            </option>
          </select>
        </div>

        <div class="md:col-span-2">
          <label class="text-sm text-gray-700">Motivo</label>
          <textarea v-model="form.reason" class="w-full border rounded p-2" rows="3" />
        </div>
      </div>

      <div class="mt-6 flex gap-2">
        <button class="px-4 py-2 rounded bg-gray-900 text-white disabled:opacity-60" :disabled="loading" @click="save">
          {{ loading ? "Guardando..." : "Crear cita" }}
        </button>
        <button class="px-4 py-2 rounded border" @click="cancel">Cancelar</button>
      </div>
    </div>
  </div>
</template>
