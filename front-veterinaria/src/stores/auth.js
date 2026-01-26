import { defineStore } from "pinia"
import api from "@/app/axios"

export const useAuthStore = defineStore("auth", {
  state: () => ({
    user: null,
    token: localStorage.getItem("token") || null,
    roles: [],
    permissions: [],
    loadingMe: false,
    loadedMe: false,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    hasRole: (state) => (role) => state.roles.includes(role),
    can: (state) => (perm) => state.permissions.includes(perm),
  },

  actions: {
    setToken(token) {
      this.token = token
      localStorage.setItem("token", token)
    },

    clearAuth() {
      this.user = null
      this.token = null
      localStorage.removeItem("token")
      this.roles = []
      this.permissions = []
      this.loadingMe = false
      this.loadedMe = false
    },

    async login(credentials) {
      const { data } = await api.post("/auth/login", credentials)

      const token = data.token || data.access_token // ✅ soporta ambos
      if (!token) throw new Error("No se recibió token en /auth/login")

      this.setToken(token)
      this.user = data.user || null
      return data
    },

    async fetchMe() {
      if (!this.token) return
      if (this.loadedMe || this.loadingMe) return

      this.loadingMe = true
      try {
        const { data } = await api.get("/auth/me")

        this.user = data.user || null
        this.roles = data.roles || []
        this.permissions = data.permissions || []

        this.loadedMe = true
        return data
      } finally {
        this.loadingMe = false
      }
    },

    async refreshToken() {
      const { data } = await api.post("/auth/refresh")
      this.setToken(data.token)
      this.user = data.user || this.user
      return data
    },

    async logout() {
      try {
        await api.post("/auth/logout")
      } finally {
        this.clearAuth()
      }
    },
  },
})
