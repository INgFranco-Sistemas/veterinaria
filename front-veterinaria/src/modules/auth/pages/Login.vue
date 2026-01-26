<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="w-full max-w-md bg-white p-6 rounded-xl shadow">
      <h1 class="text-2xl font-bold mb-6 text-center">Iniciar Sesión</h1>

      <form @submit.prevent="onSubmit" class="space-y-4">
        <div>
          <label class="block text-sm mb-1">Email</label>
          <input
            v-model="form.email"
            type="email"
            class="w-full border rounded p-2"
            placeholder="admin@vet.com"
            autocomplete="username"
          />
        </div>

        <div>
          <label class="block text-sm mb-1">Password</label>
          <input
            v-model="form.password"
            type="password"
            class="w-full border rounded p-2"
            placeholder="********"
            autocomplete="current-password"
          />
        </div>

        <button
          type="submit"
          class="w-full bg-blue-600 text-white rounded p-2 disabled:opacity-60"
          :disabled="loading"
        >
          {{ loading ? "Ingresando..." : "Entrar" }}
        </button>

        <p v-if="error" class="text-red-600 mt-2 text-sm text-center">
          {{ error }}
        </p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from "vue"
import { useRouter } from "vue-router"
import { useAuthStore } from "@/stores/auth"

const router = useRouter()
const auth = useAuthStore()

const loading = ref(false)
const error = ref("")

const form = reactive({
  email: "admin@vet.com",
  password: "12345678",
})

const onSubmit = async () => {
  loading.value = true
  error.value = ""

  try {
    await auth.login(form)
    await auth.fetchMe() // ✅ aquí se cargan roles/permisos antes de entrar al admin
    await router.push({ name: "admin.vets.index" })
  } catch (e) {
    error.value =
      e?.response?.data?.message ||
      "Error al iniciar sesión"
  } finally {
    loading.value = false
  }
}
</script>
