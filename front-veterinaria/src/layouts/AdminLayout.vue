<template>
  <div class="flex min-h-screen bg-gray-100">
    <aside class="w-64 bg-gray-900 text-white p-4">
      <h2 class="text-lg font-bold mb-4">Admin</h2>
      <nav class="space-y-2">
        <router-link class="block px-2 py-1 rounded hover:bg-gray-800" to="/admin/dashboard">
          Dashboard
        </router-link>

        <router-link class="block px-2 py-1 rounded hover:bg-gray-800" to="/admin/veterinarios">
          Veterinarios
        </router-link>
      </nav>
    </aside>

    <main class="flex-1 p-6">
      <router-view />
    </main>
  </div>
</template>
