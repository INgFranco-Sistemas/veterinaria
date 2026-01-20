<script setup>
import { ref, onMounted, computed } from "vue"
import { useRoute, useRouter } from "vue-router"
import { vetsApi } from "@/services/vets"

const route = useRoute()
const router = useRouter()

const id = computed(() => route.params.id)
const isEdit = computed(() => !!id.value)

const loading = ref(false)
const errorMsg = ref("")

const form = ref({
  full_name: "",
  email: "",
  phone: "",
  document_type: "",
  document_number: "",
  specialty: "",
  attention_area: "",
  active: true,
})

const fetchOne = async () => {
  loading.value = true
  errorMsg.value = ""
  try {
    const { data } = await vetsApi.get(id.value)
    form.value = {
      full_name: data.full_name ?? "",
      email: data.email ?? "",
      phone: data.phone ?? "",
      document_type: data.document_type ?? "",
      document_number: data.document_number ?? "",
      specialty: data.specialty ?? "",
      attention_area: data.attention_area ?? "",
      active: !!data.active,
    }
  } catch (err) {
    errorMsg.value = err?.response?.data?.message || "Error cargando veterinario"
  } finally {
    loading.value = false
  }
}

const save = async () => {
  loading.value = true
  errorMsg.value = ""
  try {
    if (isEdit.value) {
      await vetsApi.update(id.value, form.value)
    } else {
      await vetsApi.create(form.value)
    }
    router.push({ name: "admin.vets.index" })
  } catch (err) {
    const data = err?.response?.data
    if (data?.errors) {
      // muestra primer error
      const firstKey = Object.keys(data.errors)[0]
      errorMsg.value = data.errors[firstKey]?.[0] || "Error de validación"
    } else {
      errorMsg.value = data?.message || "Error guardando"
    }
  } finally {
    loading.value = false
  }
}

const cancel = () => router.push({ name: "admin.vets.index" })

onMounted(() => {
  if (isEdit.value) fetchOne()
})
</script>

<template>
  <div class="space-y-4">
    <div>
      <h1 class="text-2xl font-bold">
        {{ isEdit ? "Editar veterinario" : "Nuevo veterinario" }}
      </h1>
      <p class="text-gray-600">Completa la información</p>
    </div>

    <div v-if="errorMsg" class="p-3 rounded bg-red-50 text-red-700">
      {{ errorMsg }}
    </div>

    <div class="bg-white rounded shadow p-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm text-gray-700">Nombre completo *</label>
          <input v-model="form.full_name" class="w-full border rounded p-2" />
        </div>

        <div>
          <label class="text-sm text-gray-700">Email</label>
          <input v-model="form.email" class="w-full border rounded p-2" />
        </div>

        <div>
          <label class="text-sm text-gray-700">Teléfono</label>
          <input v-model="form.phone" class="w-full border rounded p-2" />
        </div>

        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="text-sm text-gray-700">Tipo doc</label>
            <input v-model="form.document_type" class="w-full border rounded p-2" placeholder="DNI" />
          </div>
          <div>
            <label class="text-sm text-gray-700">N° doc</label>
            <input v-model="form.document_number" class="w-full border rounded p-2" />
          </div>
        </div>

        <div>
          <label class="text-sm text-gray-700">Especialidad</label>
          <input v-model="form.specialty" class="w-full border rounded p-2" />
        </div>

        <div>
          <label class="text-sm text-gray-700">Área de atención</label>
          <input v-model="form.attention_area" class="w-full border rounded p-2" />
        </div>

        <div class="flex items-center gap-2">
          <input id="active" type="checkbox" v-model="form.active" />
          <label for="active" class="text-sm text-gray-700">Activo</label>
        </div>
      </div>

      <div class="mt-6 flex gap-2">
        <button
          class="px-4 py-2 rounded bg-gray-900 text-white disabled:opacity-60"
          :disabled="loading"
          @click="save"
        >
          {{ loading ? "Guardando..." : "Guardar" }}
        </button>
        <button class="px-4 py-2 rounded border" @click="cancel">
          Cancelar
        </button>
      </div>
    </div>
  </div>
</template>
