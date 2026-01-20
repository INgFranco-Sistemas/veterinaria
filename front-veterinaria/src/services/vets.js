import api from "@/app/axios"

export const vetsApi = {
  list(params) {
    return api.get("/veterinarians", { params })
  },
  get(id) {
    return api.get(`/veterinarians/${id}`)
  },
  create(payload) {
    return api.post("/veterinarians", payload)
  },
  update(id, payload) {
    return api.put(`/veterinarians/${id}`, payload)
  },
  remove(id) {
    return api.delete(`/veterinarians/${id}`)
  },
}
