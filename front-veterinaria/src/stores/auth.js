import { defineStore } from "pinia"
import api from "@/app/axios"

export const useAuthStore = defineStore("auth", {
  state: () => ({
    user: null,
    token: localStorage.getItem("token") || null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
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
    },

    async login(credentials) {
      const { data } = await api.post("/api/auth/login", credentials)
      this.setToken(data.token)
      this.user = data.user
      return data
    },

    async fetchMe() {
      const { data } = await api.get("/api/auth/me")
      this.user = data.user
      return data.user
    },

    async refreshToken() {
      const { data } = await api.post("/api/auth/refresh")
      this.setToken(data.token)
      this.user = data.user
      return data
    },

    async logout() {
      try {
        await api.post("/api/auth/logout")
      } finally {
        this.clearAuth()
      }
    },
  },
})
