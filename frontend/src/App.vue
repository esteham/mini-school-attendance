<script setup>
import { RouterLink, RouterView, useRoute } from 'vue-router'

const route = useRoute()

const isActive = (path) => route.path === path

const menu = [
  { label: 'Dashboard', to: '/' },
  { label: 'Students', to: '/students' },
  { label: 'Attendance', to: '/attendance' },
]
</script>

<template>
  <div class="min-h-screen flex bg-gray-100">
    <!-- Sidebar -->
    <aside class="w-70 bg-white border-r shadow-sm p-6 flex flex-col space-y-6">
      <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Mini School Attendance</h1>

      <nav class="flex flex-col space-y-1">
        <RouterLink
          v-for="item in menu"
          :key="item.to"
          :to="item.to"
          class="flex items-center gap-3 px-4 py-2 rounded-xl transition-all"
          :class="
            isActive(item.to) ? 'bg-blue-600 text-white shadow' : 'text-gray-600 hover:bg-gray-100'
          "
        >
          <component :is="item.icon" class="w-5 h-5" />
          <span class="font-medium">{{ item.label }}</span>
        </RouterLink>
      </nav>
    </aside>

    <!-- Main content -->
    <main class="flex-1 p-8">
      <RouterView />
    </main>
  </div>
</template>
