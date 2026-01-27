import api from "@/app/axios"

export const slotsApi = {
  list(params) {
    return api.get("/slots", { params })
  },
  generate(vetId, payload) {
    return api.post(`/veterinarians/${vetId}/slots/generate`, payload)
  },
  deleteRange(vetId, payload) {
    return api.delete(`/veterinarians/${vetId}/slots/delete-range`, { data: payload })
  },
}
