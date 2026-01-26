import api from "@/app/axios"

export const clientsApi = {
  list(params) {
    return api.get("/clients", { params })
  },
  get(id) {
    return api.get(`/clients/${id}`)
  },
  create(payload) {
    return api.post("/clients", payload)
  },
  update(id, payload) {
    return api.put(`/clients/${id}`, payload)
  },
  remove(id) {
    return api.delete(`/clients/${id}`)
  },
}
