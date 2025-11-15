<script setup>
import { ref, computed } from 'vue'
import api from '../api/axios'

const selectedClass = ref('')
const selectedSection = ref('')
const date = ref(new Date().toISOString().slice(0, 10))
const students = ref([])
const statuses = ref({})
const loading = ref(false)

const loadStudents = async () => {
  if (!selectedClass.value) return

  const params = {
    class: selectedClass.value,
    per_page: 100,
  }

  if (selectedSection.value) {
    params.section = selectedSection.value
  }

  const res = await api.get('/students', {
    params,
  })

  students.value = res.data.data
  statuses.value = {}
  students.value.forEach((s) => {
    statuses.value[s.id] = 'present'
  })
}

const markAll = (status) => {
  Object.keys(statuses.value).forEach((id) => {
    statuses.value[id] = status
  })
}

const submit = async () => {
  loading.value = true
  try {
    const records = Object.entries(statuses.value).map(([id, status]) => ({
      student_id: Number(id),
      status,
    }))

    await api.post('/attendance/bulk', {
      date: date.value,
      records,
    })

    alert('Attendance saved!')
  } finally {
    loading.value = false
  }
}

const presentCount = computed(
  () => Object.values(statuses.value).filter((s) => s === 'present').length,
)
const totalCount = computed(() => Object.values(statuses.value).length)
const percent = computed(() =>
  totalCount.value ? ((presentCount.value * 100) / totalCount.value).toFixed(2) : 0,
)
</script>

<template>
  <div class="space-y-4">
    <h2 class="text-2xl font-bold">Attendance</h2>

    <div class="flex flex-wrap gap-2 items-center">
      <input
        v-model="selectedClass"
        type="text"
        placeholder="Class (e.g. Ten)"
        class="border rounded px-3 py-2"
      />
      <input
        v-model="selectedSection"
        type="text"
        placeholder="Section (optional)"
        class="border rounded px-3 py-2"
      />
      <input v-model="date" type="date" class="border rounded px-3 py-2" />
      <button class="px-4 py-2 bg-blue-600 text-white rounded" @click="loadStudents">
        Load Students
      </button>
    </div>

    <div v-if="students.length" class="space-y-3">
      <div class="flex gap-2 items-center">
        <button class="px-3 py-1 border rounded" @click="markAll('present')">
          Mark All Present
        </button>
        <button class="px-3 py-1 border rounded" @click="markAll('absent')">Mark All Absent</button>
        <button class="px-3 py-1 border rounded" @click="markAll('late')">Mark All Late</button>

        <div class="ml-auto text-right">
          <p>Present: {{ presentCount }} / {{ totalCount }} ({{ percent }}%)</p>
        </div>
      </div>

      <table class="w-full bg-white rounded shadow overflow-hidden">
        <thead class="bg-gray-200 text-left">
          <tr>
            <th class="px-4 py-2">Name</th>
            <th class="px-4 py-2">Student ID</th>
            <th class="px-4 py-2">Class</th>
            <th class="px-4 py-2">Section</th>
            <th class="px-4 py-2">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="s in students" :key="s.id" class="border-t">
            <td class="px-4 py-2">{{ s.name }}</td>
            <td class="px-4 py-2">{{ s.student_id }}</td>
            <td class="px-4 py-2">{{ s.class }}</td>
            <td class="px-4 py-2">{{ s.section }}</td>
            <td class="px-4 py-2">
              <select v-model="statuses[s.id]" class="border rounded px-2 py-1">
                <option value="present">Present</option>
                <option value="absent">Absent</option>
                <option value="late">Late</option>
              </select>
            </td>
          </tr>
        </tbody>
      </table>

      <button class="px-4 py-2 bg-green-600 text-white rounded" :disabled="loading" @click="submit">
        {{ loading ? 'Saving...' : 'Save Attendance' }}
      </button>
    </div>

    <p v-else class="text-gray-500">Select class & click "Load Students".</p>
  </div>
</template>
