<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '../api/axios'

const students = ref([])
const links = ref([])
const meta = ref(null)

const search = ref('')
const filterClass = ref('')
const page = ref(1)

const loadStudents = async () => {
  const res = await api.get('/students', {
    params: {
      search: search.value || undefined,
      class: filterClass.value || undefined,
      page: page.value,
    },
  })

  students.value = res.data.data
  links.value = res.data.links
  meta.value = res.data.meta
}

watch([search, filterClass, page], () => {
  loadStudents()
})

onMounted(() => {
  loadStudents()
})
</script>

<template>
  <div class="space-y-4">
    <h2 class="text-2xl font-bold">Students</h2>

    <div class="flex flex-wrap gap-2 items-center">
      <input
        v-model="search"
        type="text"
        placeholder="Search by name or ID"
        class="border rounded px-3 py-2"
      />
      <input
        v-model="filterClass"
        type="text"
        placeholder="Filter by class"
        class="border rounded px-3 py-2"
      />
    </div>

    <table class="w-full bg-white rounded  shadow overflow-hidden">
      <thead class="bg-gray-200 text-left">
        <tr>
          <th class="px-4 py-2">ID</th>
          <th class="px-4 py-2">Name</th>
          <th class="px-4 py-2">Student ID</th>
          <th class="px-4 py-2">Class</th>
          <th class="px-4 py-2">Section</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="s in students" :key="s.id" class="border-t">
          <td class="px-4 py-2">{{ s.id }}</td>
          <td class="px-4 py-2">{{ s.name }}</td>
          <td class="px-4 py-2">{{ s.student_id }}</td>
          <td class="px-4 py-2">{{ s.class }}</td>
          <td class="px-4 py-2">{{ s.section }}</td>
        </tr>
        <tr v-if="!students.length">
          <td colspan="5" class="px-4 py-4 text-center text-gray-500">No students found</td>
        </tr>
      </tbody>
    </table>

    <div v-if="meta" class="flex gap-2">
      <button
        class="px-3 py-1 border rounded"
        :disabled="!meta.links.find((l) => l.label === '&laquo; Previous')?.url"
        @click="page = Math.max(1, page - 1)"
      >
        Prev
      </button>
      <span>Page {{ meta.current_page }} of {{ meta.last_page }}</span>
      <button
        class="px-3 py-1 border rounded"
        :disabled="!meta.links.find((l) => l.label === 'Next &raquo;')?.url"
        @click="page = Math.min(meta.last_page, page + 1)"
      >
        Next
      </button>
    </div>
  </div>
</template>
