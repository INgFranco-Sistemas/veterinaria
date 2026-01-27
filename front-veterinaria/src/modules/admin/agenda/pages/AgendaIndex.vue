<script setup>
import { ref, onMounted, computed } from "vue"
import { useAuthStore } from "@/stores/auth"
import { vetsApi } from "@/services/vets"
import { schedulesApi } from "@/services/schedules"
import { slotsApi } from "@/services/slots"

const auth = useAuthStore()

/** Permisos */
const canSchedulesView = computed(() => auth.can("schedules.view"))
const canSchedulesCreate = computed(() => auth.can("schedules.create") || auth.can("schedules.update"))
const canSlotsGenerate = computed(() => auth.can("slots.generate"))
const canSlotsView = computed(() => auth.can("slots.view"))

/** Vets */
const vets = ref([])
const vetsLoading = ref(false)
const selectedVetId = ref("")

/** Schedule form */
const scheduleLoading = ref(false)
const scheduleMsg = ref("")
const scheduleErr = ref("")

// 1=Lun .. 7=Dom
const weekdays = [
  { id: 1, label: "Lunes" },
  { id: 2, label: "Martes" },
  { id: 3, label: "Miércoles" },
  { id: 4, label: "Jueves" },
  { id: 5, label: "Viernes" },
  { id: 6, label: "Sábado" },
  { id: 7, label: "Domingo" },
]

// estado local editable: por día
const scheduleByDay = ref(
  weekdays.reduce((acc, d) => {
    acc[d.id] = { weekday: d.id, start_time: "09:00", end_time: "13:00", slot_minutes: 30, active: false }
    return acc
  }, {})
)

/** Slots */
const slotsMsg = ref("")
const slotsErr = ref("")
const slotsLoading = ref(false)

const gen = ref({
  service_type: "appointment",
  start_date: "",
  end_date: "",
  only_active_days: true,
})

const view = ref({
  date: "",
  service_type: "appointment",
})

const slots = ref([])
const slotsMeta = ref(null)

/** Helpers */
const requireVet = () => {
  if (!selectedVetId.value) {
    alert("Selecciona un veterinario primero.")
    return false
  }
  return true
}

/** Load vets */
const fetchVets = async () => {
  vetsLoading.value = true
  try {
    const { data } = await vetsApi.list({ per_page: 100 })
    vets.value = data.data
  } finally {
    vetsLoading.value = false
  }
}

/** Load schedules */
const fetchSchedules = async () => {
  if (!requireVet()) return
  scheduleLoading.value = true
  scheduleMsg.value = ""
  scheduleErr.value = ""
  try {
    const { data } = await schedulesApi.list(selectedVetId.value)
    // data: array schedules existentes
    // reset a defaults:
    scheduleByDay.value = weekdays.reduce((acc, d) => {
      acc[d.id] = { weekday: d.id, start_time: "09:00", end_time: "13:00", slot_minutes: 30, active: false }
      return acc
    }, {})

    for (const sch of data) {
      scheduleByDay.value[sch.weekday] = {
        weekday: sch.weekday,
        start_time: (sch.start_time || "09:00").slice(0, 5),
        end_time: (sch.end_time || "13:00").slice(0, 5),
        slot_minutes: sch.slot_minutes ?? 30,
        active: !!sch.active,
      }
    }

    scheduleMsg.value = "Horarios cargados."
  } catch (err) {
    scheduleErr.value = err?.response?.data?.message || "Error cargando horarios"
  } finally {
    scheduleLoading.value = false
  }
}

/** Save one day */
const saveDay = async (weekday) => {
  if (!requireVet()) return
  scheduleLoading.value = true
  scheduleMsg.value = ""
  scheduleErr.value = ""
  try {
    const payload = scheduleByDay.value[weekday]
    await schedulesApi.upsert(selectedVetId.value, payload)
    scheduleMsg.value = `Horario guardado (${weekdays.find(w => w.id === weekday)?.label}).`
    await fetchSchedules()
  } catch (err) {
    const data = err?.response?.data
    if (data?.errors) {
      const firstKey = Object.keys(data.errors)[0]
      scheduleErr.value = data.errors[firstKey]?.[0] || "Error de validación"
    } else {
      scheduleErr.value = data?.message || "Error guardando horario"
    }
  } finally {
    scheduleLoading.value = false
  }
}

/** Save all days */
const saveAll = async () => {
  if (!requireVet()) return
  scheduleLoading.value = true
  scheduleMsg.value = ""
  scheduleErr.value = ""
  try {
    for (const d of weekdays) {
      await schedulesApi.upsert(selectedVetId.value, scheduleByDay.value[d.id])
    }
    scheduleMsg.value = "Horarios guardados (todos los días)."
    await fetchSchedules()
  } catch (err) {
    scheduleErr.value = err?.response?.data?.message || "Error guardando horarios"
  } finally {
    scheduleLoading.value = false
  }
}

/** Generate slots */
const generateSlots = async () => {
  if (!requireVet()) return
  slotsLoading.value = true
  slotsMsg.value = ""
  slotsErr.value = ""
  try {
    if (!gen.value.start_date || !gen.value.end_date) {
      throw new Error("Debes seleccionar start_date y end_date")
    }
    const { data } = await slotsApi.generate(selectedVetId.value, gen.value)
    slotsMsg.value = `${data.message} Creados: ${data.created} | Omitidos: ${data.skipped}`
  } catch (err) {
    slotsErr.value = err?.response?.data?.message || err.message || "Error generando slots"
  } finally {
    slotsLoading.value = false
  }
}

/** View slots for a date */
const fetchSlots = async () => {
  if (!requireVet()) return
  slotsLoading.value = true
  slotsMsg.value = ""
  slotsErr.value = ""
  try {
    if (!view.value.date) throw new Error("Selecciona una fecha")
    const { data } = await slotsApi.list({
      veterinarian_id: selectedVetId.value,
      service_type: view.value.service_type,
      date: view.value.date,
      per_page: 200,
    })
    slots.value = data.data
    slotsMeta.value = data
  } catch (err) {
    slotsErr.value = err?.response?.data?.message || err.message || "Error cargando slots"
  } finally {
    slotsLoading.value = false
  }
}

onMounted(async () => {
  await fetchVets()
})
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold">Agenda</h1>
      <p class="text-gray-600">Configura horarios del veterinario y genera disponibilidad</p>
    </div>

    <!-- Selector de veterinario -->
    <div class="bg-white rounded shadow p-4 space-y-3">
      <div class="flex flex-col md:flex-row md:items-end gap-3">
        <div class="flex-1">
          <label class="text-sm text-gray-700">Veterinario</label>
          <select v-model="selectedVetId" class="w-full border rounded p-2">
            <option value="">-- Seleccionar --</option>
            <option v-for="v in vets" :key="v.id" :value="String(v.id)">
              {{ v.full_name }} ({{ v.specialty || "General" }})
            </option>
          </select>
        </div>

        <button
          class="px-4 py-2 rounded border hover:bg-gray-50"
          :disabled="vetsLoading || !selectedVetId"
          @click="fetchSchedules"
        >
          Cargar horarios
        </button>
      </div>

      <div v-if="vetsLoading" class="text-sm text-gray-500">Cargando veterinarios...</div>
    </div>

    <!-- Horario semanal -->
    <div class="bg-white rounded shadow p-4 space-y-3">
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold">Horario semanal</h2>

        <button
          v-if="canSchedulesCreate"
          class="px-4 py-2 rounded bg-gray-900 text-white disabled:opacity-60"
          :disabled="scheduleLoading || !selectedVetId"
          @click="saveAll"
        >
          Guardar todo
        </button>
      </div>

      <div v-if="scheduleErr" class="p-3 rounded bg-red-50 text-red-700">{{ scheduleErr }}</div>
      <div v-if="scheduleMsg" class="p-3 rounded bg-green-50 text-green-700">{{ scheduleMsg }}</div>

      <div class="overflow-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left p-3">Día</th>
              <th class="text-left p-3">Activo</th>
              <th class="text-left p-3">Inicio</th>
              <th class="text-left p-3">Fin</th>
              <th class="text-left p-3">Min/slot</th>
              <th class="text-right p-3">Acción</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="d in weekdays" :key="d.id" class="border-t">
              <td class="p-3 font-medium">{{ d.label }}</td>

              <td class="p-3">
                <input type="checkbox" v-model="scheduleByDay[d.id].active" />
              </td>

              <td class="p-3">
                <input type="time" class="border rounded p-2" v-model="scheduleByDay[d.id].start_time" />
              </td>

              <td class="p-3">
                <input type="time" class="border rounded p-2" v-model="scheduleByDay[d.id].end_time" />
              </td>

              <td class="p-3">
                <select class="border rounded p-2" v-model.number="scheduleByDay[d.id].slot_minutes">
                  <option :value="15">15</option>
                  <option :value="20">20</option>
                  <option :value="30">30</option>
                  <option :value="45">45</option>
                  <option :value="60">60</option>
                </select>
              </td>

              <td class="p-3 text-right">
                <button
                  v-if="canSchedulesCreate"
                  class="px-3 py-1 rounded border hover:bg-gray-50 disabled:opacity-60"
                  :disabled="scheduleLoading || !selectedVetId"
                  @click="saveDay(d.id)"
                >
                  Guardar
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="scheduleLoading" class="text-sm text-gray-500">Procesando...</div>
    </div>

    <!-- Generar slots -->
    <div class="bg-white rounded shadow p-4 space-y-3">
      <h2 class="text-xl font-bold">Generar disponibilidad (slots)</h2>

      <div v-if="slotsErr" class="p-3 rounded bg-red-50 text-red-700">{{ slotsErr }}</div>
      <div v-if="slotsMsg" class="p-3 rounded bg-green-50 text-green-700">{{ slotsMsg }}</div>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div>
          <label class="text-sm text-gray-700">Tipo servicio</label>
          <select v-model="gen.service_type" class="w-full border rounded p-2">
            <option value="appointment">Citas</option>
            <option value="vaccine">Vacunas</option>
            <option value="surgery">Cirugías</option>
          </select>
        </div>

        <div>
          <label class="text-sm text-gray-700">Inicio</label>
          <input type="date" v-model="gen.start_date" class="w-full border rounded p-2" />
        </div>

        <div>
          <label class="text-sm text-gray-700">Fin</label>
          <input type="date" v-model="gen.end_date" class="w-full border rounded p-2" />
        </div>

        <div class="flex items-end">
          <button
            v-if="canSlotsGenerate"
            class="w-full px-4 py-2 rounded bg-gray-900 text-white disabled:opacity-60"
            :disabled="slotsLoading || !selectedVetId"
            @click="generateSlots"
          >
            Generar
          </button>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <input id="only_active" type="checkbox" v-model="gen.only_active_days" />
        <label for="only_active" class="text-sm text-gray-700">Solo días activos</label>
      </div>

      <div v-if="slotsLoading" class="text-sm text-gray-500">Procesando...</div>
    </div>

    <!-- Ver slots por día -->
    <div class="bg-white rounded shadow p-4 space-y-3">
      <h2 class="text-xl font-bold">Ver slots por día</h2>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div>
          <label class="text-sm text-gray-700">Tipo servicio</label>
          <select v-model="view.service_type" class="w-full border rounded p-2">
            <option value="appointment">Citas</option>
            <option value="vaccine">Vacunas</option>
            <option value="surgery">Cirugías</option>
          </select>
        </div>

        <div>
          <label class="text-sm text-gray-700">Fecha</label>
          <input type="date" v-model="view.date" class="w-full border rounded p-2" />
        </div>

        <div class="flex items-end">
          <button
            v-if="canSlotsView"
            class="w-full px-4 py-2 rounded border hover:bg-gray-50 disabled:opacity-60"
            :disabled="slotsLoading || !selectedVetId"
            @click="fetchSlots"
          >
            Ver
          </button>
        </div>
      </div>

      <div class="overflow-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left p-3">Inicio</th>
              <th class="text-left p-3">Fin</th>
              <th class="text-left p-3">Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in slots" :key="s.id" class="border-t">
              <td class="p-3">{{ new Date(s.starts_at).toLocaleString() }}</td>
              <td class="p-3">{{ new Date(s.ends_at).toLocaleString() }}</td>
              <td class="p-3">
                <span
                  class="px-2 py-1 rounded text-xs"
                  :class="s.status === 'available' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600'"
                >
                  {{ s.status }}
                </span>
              </td>
            </tr>

            <tr v-if="slots.length === 0">
              <td colspan="3" class="p-4 text-center text-gray-500">No hay slots</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
