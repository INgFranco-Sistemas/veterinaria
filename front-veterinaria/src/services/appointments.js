import api from "@/app/axios"

export const appointmentsApi = {
  list(params) {
    return api.get("/appointments", { params })
  },
  get(id) {
    return api.get(`/appointments/${id}`)
  },
  create(payload) {
    return api.post("/appointments", payload)
  },
  update(id, payload) {
    return api.put(`/appointments/${id}`, payload)
  },
  cancel(id, payload) {
    return api.post(`/appointments/${id}/cancel`, payload)
  },
}
