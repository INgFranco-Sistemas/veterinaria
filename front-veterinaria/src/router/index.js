import { createRouter, createWebHistory } from "vue-router"

import PublicLayout from "@/layouts/PublicLayout.vue"
import AdminLayout from "@/layouts/AdminLayout.vue"

import Home from "@/modules/public/pages/Home.vue"
import Login from "@/modules/auth/pages/Login.vue"
import Dashboard from "@/modules/admin/dashboard/pages/Dashboard.vue"

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
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// ✅ Guard global: protege /admin
router.beforeEach((to) => {
  const token = localStorage.getItem("token")
  const role = localStorage.getItem("role")

  // Debug (puedes borrarlo luego)
  console.log("[GUARD]", {
    to: to.fullPath,
    token: !!token,
    role,
  })

  // Solo proteger rutas admin
  if (to.path.startsWith("/admin")) {
    if (!token) return "/login"
    if (role !== "admin") return "/"
  }
})

export default router
