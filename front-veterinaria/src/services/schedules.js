import api from "@/app/axios"

export const schedulesApi = {
  list(vetId) {
    return api.get(`/veterinarians/${vetId}/schedules`)
  },
  upsert(vetId, payload) {
    // store hace updateOrCreate por weekday
    return api.post(`/veterinarians/${vetId}/schedules`, payload)
  },
  update(vetId, scheduleId, payload) {
    return api.put(`/veterinarians/${vetId}/schedules/${scheduleId}`, payload)
  },
  remove(vetId, scheduleId) {
    return api.delete(`/veterinarians/${vetId}/schedules/${scheduleId}`)
  },
}
