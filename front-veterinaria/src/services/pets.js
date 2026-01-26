import api from "@/app/axios"

export const petsApi = {
  list(params) {
    return api.get("/pets", { params })
  },
  get(id) {
    return api.get(`/pets/${id}`)
  },
  create(payload) {
    return api.post("/pets", payload)
  },
  update(id, payload) {
    return api.put(`/pets/${id}`, payload)
  },
  remove(id) {
    return api.delete(`/pets/${id}`)
  },
}