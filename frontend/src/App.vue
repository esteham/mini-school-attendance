<template>
  <div class="min-h-screen flex">
    <aside class="w-64 bg-gray-800 text-white p-4 space-y-4">
      <h1 class="text-xl font-bold">Mini School Attendance</h1>
      <nav class="space-y-2">
        <RouterLink to="/" class="block hover:underline">Dashboard</RouterLink>
        <RouterLink to="/students" class="block hover:underline">Students</RouterLink>
        <RouterLink to="/attendance" class="block hover:underline">Attendance</RouterLink>
      </nav>
    </aside>

    <main class="flex-1 p-6 bg-gray-100">
      <RouterView />
    </main>
  </div>
</template>

<script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>
