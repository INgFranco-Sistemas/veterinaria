<script setup>
import { ref, onMounted, computed } from "vue"
import { useRoute, useRouter } from "vue-router"
import { useAuthStore } from "@/stores/auth"
import { clientsApi } from "@/services/clients"
import { petsApi } from "@/services/pets"

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const clientId = computed(() => route.params.id)

const loading = ref(false)
const errorMsg = ref("")
const client = ref(null)

const petsLoading = ref(false)
const pets = ref([])

const canPetsCreate = computed(() => auth.can("pets.create"))
const canPetsUpdate = computed(() => auth.can("pets.update"))
const canPetsDelete = computed(() => auth.can("pets.delete"))

const petFormVisible = ref(false)
const editingPetId = ref(null)

const petForm = ref({
  name: "",
  species: "Perro",
  breed: "",
  sex: "",
  birth_date: "",
  weight_kg: "",
  notes: "",
  active: true,
})

const resetPetForm = () => {
  editingPetId.value = null
  petForm.value = {
    name: "",
    species: "Perro",
    breed: "",
    sex: "",
    birth_date: "",
    weight_kg: "",
    notes: "",
    active: true,
  }
}

const openCreatePet = () => {
  resetPetForm()
  petFormVisible.value = true
}

const openEditPet = (p) => {
  editingPetId.value = p.id
  petForm.value = {
    name: p.name ?? "",
    species: p.species ?? "Perro",
    breed: p.breed ?? "",
    sex: p.sex ?? "",
    birth_date: p.birth_date ?? "",
    weight_kg: p.weight_kg ?? "",
    notes: p.notes ?? "",
    active: !!p.active,
  }
  petFormVisible.value = true
}

const closePetForm = () => {
  petFormVisible.value = false
  resetPetForm()
}

const fetchClient = async () => {
  loading.value = true
  errorMsg.value = ""
  try {
    const { data } = await clientsApi.get(clientId.value)
    client.value = data
  } catch (err) {
    errorMsg.value = err?.response?.data?.message || "Error cargando cliente"
  } finally {
    loading.value = false
  }
}

const fetchPets = async () => {
  petsLoading.value = true
  try {
    const { data } = await petsApi.list({ client_id: clientId.value, per_page: 100 })
    pets.value = data.data
  } catch (err) {
    // opcional
  } finally {
    petsLoading.value = false
  }
}

const savePet = async () => {
  try {
    const payload = {
      client_id: Number(clientId.value),
      ...petForm.value,
      // normalizar weight
      weight_kg: petForm.value.weight_kg === "" ? null : Number(petForm.value.weight_kg),
    }

    if (editingPetId.value) {
      await petsApi.update(editingPetId.value, payload)
    } else {
      await petsApi.create(payload)
    }

    await fetchPets()
    closePetForm()
  } catch (err) {
    alert(err?.response?.data?.message || "Error guardando mascota")
  }
}

const removePet = async (id) => {
  if (!confirm("¿Eliminar mascota?")) return
  try {
    await petsApi.remove(id)
    await fetchPets()
  } catch (err) {
    alert(err?.response?.data?.message || "No se pudo eliminar")
  }
}

const back = () => router.push({ name: "admin.clients.index" })

onMounted(async () => {
  await fetchClient()
  await fetchPets()
})
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold">Cliente</h1>
        <p class="text-gray-600">Detalle y mascotas</p>
      </div>
      <button class="px-4 py-2 rounded border" @click="back">Volver</button>
    </div>

    <div v-if="errorMsg" class="p-3 rounded bg-red-50 text-red-700">{{ errorMsg }}</div>

    <div v-if="loading" class="p-4 bg-white rounded shadow">Cargando...</div>

    <div v-else class="bg-white rounded shadow p-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div><span class="text-gray-500 text-sm">Nombre:</span> <div class="font-medium">{{ client?.full_name }}</div></div>
        <div><span class="text-gray-500 text-sm">Email:</span> <div class="font-medium">{{ client?.email || "-" }}</div></div>
        <div><span class="text-gray-500 text-sm">Teléfono:</span> <div class="font-medium">{{ client?.phone || "-" }}</div></div>
        <div><span class="text-gray-500 text-sm">Dirección:</span> <div class="font-medium">{{ client?.address || "-" }}</div></div>
      </div>
    </div>

    <div class="flex items-center justify-between">
      <h2 class="text-xl font-bold">Mascotas</h2>
      <button
        v-if="canPetsCreate"
        class="px-4 py-2 rounded bg-gray-900 text-white"
        @click="openCreatePet"
      >
        + Agregar Mascota
      </button>
    </div>

    <div class="bg-white rounded shadow overflow-hidden">
      <div v-if="petsLoading" class="p-4">Cargando mascotas...</div>

      <table v-else class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="text-left p-3">Nombre</th>
            <th class="text-left p-3">Especie</th>
            <th class="text-left p-3">Raza</th>
            <th class="text-left p-3">Peso</th>
            <th class="text-left p-3">Estado</th>
            <th class="text-right p-3">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in pets" :key="p.id" class="border-t">
            <td class="p-3 font-medium">{{ p.name }}</td>
            <td class="p-3">{{ p.species }}</td>
            <td class="p-3">{{ p.breed || "-" }}</td>
            <td class="p-3">{{ p.weight_kg ?? "-" }}</td>
            <td class="p-3">
              <span
                class="px-2 py-1 rounded text-xs"
                :class="p.active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600'"
              >
                {{ p.active ? "Activo" : "Inactivo" }}
              </span>
            </td>
            <td class="p-3 text-right space-x-2">
              <button v-if="canPetsUpdate" class="px-3 py-1 rounded border" @click="openEditPet(p)">
                Editar
              </button>
              <button v-if="canPetsDelete" class="px-3 py-1 rounded border hover:bg-red-50" @click="removePet(p.id)">
                Eliminar
              </button>
            </td>
          </tr>

          <tr v-if="pets.length === 0">
            <td colspan="6" class="p-4 text-center text-gray-500">No hay mascotas registradas</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal simple -->
    <div v-if="petFormVisible" class="fixed inset-0 bg-black/40 flex items-center justify-center p-4">
      <div class="bg-white rounded shadow w-full max-w-xl p-4 space-y-3">
        <h3 class="text-lg font-bold">
          {{ editingPetId ? "Editar mascota" : "Nueva mascota" }}
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <div>
            <label class="text-sm text-gray-700">Nombre *</label>
            <input v-model="petForm.name" class="w-full border rounded p-2" />
          </div>

          <div>
            <label class="text-sm text-gray-700">Especie *</label>
            <input v-model="petForm.species" class="w-full border rounded p-2" />
          </div>

          <div>
            <label class="text-sm text-gray-700">Raza</label>
            <input v-model="petForm.breed" class="w-full border rounded p-2" />
          </div>

          <div>
            <label class="text-sm text-gray-700">Sexo</label>
            <input v-model="petForm.sex" class="w-full border rounded p-2" placeholder="Macho/Hembra" />
          </div>

          <div>
            <label class="text-sm text-gray-700">Fecha nacimiento</label>
            <input v-model="petForm.birth_date" type="date" class="w-full border rounded p-2" />
          </div>

          <div>
            <label class="text-sm text-gray-700">Peso (kg)</label>
            <input v-model="petForm.weight_kg" type="number" step="0.01" class="w-full border rounded p-2" />
          </div>

          <div class="md:col-span-2">
            <label class="text-sm text-gray-700">Notas</label>
            <textarea v-model="petForm.notes" class="w-full border rounded p-2" rows="3" />
          </div>

          <div class="flex items-center gap-2">
            <input id="pet_active" type="checkbox" v-model="petForm.active" />
            <label for="pet_active" class="text-sm text-gray-700">Activo</label>
          </div>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button class="px-4 py-2 rounded border" @click="closePetForm">Cancelar</button>
          <button class="px-4 py-2 rounded bg-gray-900 text-white" @click="savePet">
            Guardar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
