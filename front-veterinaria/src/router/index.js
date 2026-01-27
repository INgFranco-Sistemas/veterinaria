import { createRouter, createWebHistory } from "vue-router"
import { useAuthStore } from "@/stores/auth"

// Layouts
import PublicLayout from "@/layouts/PublicLayout.vue"
import AdminLayout from "@/layouts/AdminLayout.vue"

// Public
import Home from "@/modules/public/pages/Home.vue"
import Login from "@/modules/auth/pages/Login.vue"

// Admin
import Dashboard from "@/modules/admin/dashboard/pages/Dashboard.vue"

// Vets
import VetsIndex from "@/modules/admin/vets/pages/VetsIndex.vue"
import VetsForm from "@/modules/admin/vets/pages/VetsForm.vue"

// Clients
import ClientsIndex from "@/modules/admin/clients/pages/ClientsIndex.vue"
import ClientsForm from "@/modules/admin/clients/pages/ClientsForm.vue"
import ClientShow from "@/modules/admin/clients/pages/ClientShow.vue"

import AgendaIndex from "@/modules/admin/agenda/pages/AgendaIndex.vue"

const routes = [
  {
    path: "/",
    component: PublicLayout,
    children: [
      { path: "", name: "home", component: Home },
      { path: "login", name: "login", component: Login },
    ],
  },

  {
    path: "/admin",
    component: AdminLayout,
    meta: { requiresAuth: true, roles: ["admin"] },
    children: [
      { path: "dashboard", name: "admin.dashboard", component: Dashboard },

      { path: "veterinarios", name: "admin.vets.index", component: VetsIndex, meta: { permission: "vets.view" } },
      { path: "veterinarios/nuevo", name: "admin.vets.create", component: VetsForm, meta: { permission: "vets.create" } },
      { path: "veterinarios/:id/editar", name: "admin.vets.edit", component: VetsForm, meta: { permission: "vets.update" } },

      { path: "clientes", name: "admin.clients.index", component: ClientsIndex, meta: { permission: "clients.view" } },
      { path: "clientes/nuevo", name: "admin.clients.create", component: ClientsForm, meta: { permission: "clients.create" } },
      { path: "clientes/:id/editar", name: "admin.clients.edit", component: ClientsForm, meta: { permission: "clients.update" } },
      { path: "clientes/:id", name: "admin.clients.show", component: ClientShow, meta: { permission: "clients.view" } },
      { path: "agenda", name: "admin.agenda.index", component: AgendaIndex, meta: { requiresAuth: true, roles: ["admin"], permission: "schedules.view" },},
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

/**
 * ✅ Guard global:
 * - Si hay token y aún no cargó /me -> fetchMe() (roles/permissions)
 * - Protege rutas con meta.requiresAuth
 * - Bloquea por roles y permissions cuando aplique
 */
router.beforeEach(async (to) => {
  const auth = useAuthStore()

  // Si tiene token, asegúrate de tener roles/permisos antes de entrar a admin
  if (auth.token && !auth.loadedMe) {
    try {
      await auth.fetchMe()
    } catch (e) {
      auth.clearAuth()
      return { name: "login" }
    }
  }

  // Rutas protegidas
  if (to.meta?.requiresAuth && !auth.token) {
    return { name: "login" }
  }
})

export default router
