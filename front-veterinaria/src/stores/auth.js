import { defineStore } from "pinia"
import api from "@/app/axios"

export const useAuthStore = defineStore("auth", {
  state: () => ({
    user: null,
    token: localStorage.getItem("token") || null,
    roles: [],
    permissions: [],
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
    },

    async login(credentials) {
      const { data } = await api.post("/auth/login", credentials) // ✅
      this.setToken(data.token)
      this.user = data.user
      return data
    },

    async fetchMe() {
      const { data } = await api.get("/auth/me") // ✅
      this.user = data.user
      this.roles = data.roles || []
      this.permissions = data.permissions || []
      return data
    },

    async refreshToken() {
      const { data } = await api.post("/auth/refresh") // ✅
      this.setToken(data.token)
      this.user = data.user
      return data
    },

    async logout() {
      try {
        await api.post("/auth/logout") // ✅
      } finally {
        this.clearAuth()
      }
    },
  },
})
