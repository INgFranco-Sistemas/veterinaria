<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="w-full max-w-md bg-white p-6 rounded-xl shadow">
      <h1 class="text-2xl font-bold mb-6 text-center">Iniciar sesión</h1>

      <form @submit.prevent="onSubmit" class="space-y-4">
        <div>
          <label class="block text-sm mb-1">Email</label>
          <input
            v-model="form.email"
            type="email"
            class="w-full border rounded p-2"
            placeholder="admin@vet.com"
          />
        </div>

        <div>
          <label class="block text-sm mb-1">Password</label>
          <input
            v-model="form.password"
            type="password"
            class="w-full border rounded p-2"
            placeholder="********"
          />
        </div>

        <button
          type="submit"
          class="w-full bg-blue-600 text-white rounded p-2"
          :disabled="loading"
        >
          {{ loading ? "Ingresando..." : "Entrar" }}
        </button>
      </form>

      <p v-if="error" class="text-red-600 mt-4 text-sm text-center">
        {{ error }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from "vue"
import { useRouter } from "vue-router"
import axios from "axios"

const router = useRouter()

const loading = ref(false)
const error = ref("")

const form = reactive({
  email: "admin@vet.com",
  password: "12345678",
})

const onSubmit = async () => {
  console.log("CLICK/SUBMIT OK ✅", { ...form })

  error.value = ""
  loading.value = true

  try {
    const url = "http://127.0.0.1:8000/api/auth/login"
    console.log("POST →", url)

    const res = await axios.post(url, form, {
      headers: { "Content-Type": "application/json" },
    })

    console.log("RESPUESTA ✅", res.data)

    // ✅ Guardar token y rol
    if (res.data?.access_token) {
      localStorage.setItem("token", res.data.access_token)
      localStorage.setItem("role", res.data.user?.role || "")
    }

    console.log("GUARDADO token?", !!localStorage.getItem("token"))
    console.log("GUARDADO role:", localStorage.getItem("role"))

    // ✅ Redirigir a dashboard (ruta ABSOLUTA)
    // Opción A (por path):
    router.push("/admin/dashboard")

    // Opción B (por name, más seguro):
    // router.push({ name: "admin.dashboard" })
  } catch (e) {
    console.error("ERROR ❌", e)
    error.value =
      e?.response?.data?.message ||
      `Error ${e?.response?.status || ""}` ||
      "Error al iniciar sesión"
  } finally {
    loading.value = false
  }
}
</script>
