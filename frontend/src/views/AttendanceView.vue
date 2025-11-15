<script setup>
import { ref, computed } from 'vue'
import api from '../api/axios'

const selectedClass = ref('')
const selectedSection = ref('')
const date = ref(new Date().toISOString().slice(0, 10))

const students = ref([])
const statuses = ref({})

const loadingStudents = ref(false)
const saving = ref(false)
const error = ref(null)
const success = ref('')

const loadStudents = async () => {
  if (!selectedClass.value) {
    error.value = 'Please enter a class to load students.'
    return
  }

  loadingStudents.value = true
  error.value = null
  success.value = ''

  try {
    const params = {
      class: selectedClass.value,
      per_page: 100,
    }

    if (selectedSection.value) {
      params.section = selectedSection.value
    }

    const res = await api.get('/students', { params })

    students.value = res.data.data || []
    statuses.value = {}

    students.value.forEach((s) => {
      statuses.value[s.id] = 'present'
    })
  } catch (e) {
    console.error(e)
    error.value = 'Failed to load students. Please try again.'
  } finally {
    loadingStudents.value = false
  }
}

const markAll = (status) => {
  Object.keys(statuses.value).forEach((id) => {
    statuses.value[id] = status
  })
}

const submit = async () => {
  if (!students.value.length) return

  saving.value = true
  error.value = null
  success.value = ''

  try {
    const records = Object.entries(statuses.value).map(([id, status]) => ({
      student_id: Number(id),
      status,
    }))

    await api.post('/attendance/bulk', {
      date: date.value,
      records,
    })

    success.value = 'Attendance saved successfully.'
    setTimeout(() => {
      success.value = ''
    }, 3000)
    alert(success.value)
  } catch (e) {
    console.error(e)
    error.value = 'Failed to save attendance. Please try again.'
  } finally {
    saving.value = false
  }
}

const presentCount = computed(
  () => Object.values(statuses.value).filter((s) => s === 'present').length,
)
const totalCount = computed(() => Object.values(statuses.value).length)
const percent = computed(() =>
  totalCount.value ? ((presentCount.value * 100) / totalCount.value).toFixed(2) : 0,
)

const hasStudents = computed(() => students.value.length > 0)
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div
      class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 rounded-2xl px-6 py-4 shadow-sm text-white mb-3"
    >
      <div>
        <h2 class="text-2xl font-semibold tracking-tight">Attendance</h2>
        <p class="text-sm text-blue-100">Mark daily attendance by class and section</p>
      </div>
      <div
        class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-xs sm:text-sm"
      >
        <span class="text-blue-100">Date</span>
        <span class="font-semibold">
          {{ date }}
        </span>
      </div>
    </div>

    <!-- Alerts -->
    <div
      v-if="error"
      class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
    >
      {{ error }}
    </div>
    <div
      v-if="success"
      class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
    >
      {{ success }}
    </div>

    <!-- Filters -->
    <div
      class="bg-white rounded-2xl shadow-sm border border-gray-100 px-4 py-3 sm:px-6 sm:py-4 flex flex-col gap-3 mb-2"
    >
      <div class="flex flex-col md:flex-row md:items-center gap-3">
        <!-- Class -->
        <div class="relative flex-1 min-w-[180px]">
          <span
            class="pointer-events-none absolute inset-y-0 left-1 flex items-center text-gray-400 text-xs uppercase"
          >
            Class
          </span>
          <input
            v-model="selectedClass"
            type="text"
            placeholder="e.g. 9, 10"
            class="w-full rounded-xl border border-gray-200 bg-gray-50 pl-10 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
          />
        </div>

        <!-- Section -->
        <div class="relative flex-1 min-w-[120px]">
          <span
            class="pointer-events-none absolute inset-y-0 left-1 flex items-center text-gray-400 text-xs uppercase"
          >
            Section
          </span>
          <input
            v-model="selectedSection"
            type="text"
            placeholder="Optional"
            class="w-full rounded-xl border border-gray-200 bg-gray-50 pl-10 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
          />
        </div>

        <!-- Date -->
        <div class="flex items-center gap-2">
          <input
            v-model="date"
            type="date"
            :min="date"
            :max="date"
            class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
          />
        </div>
      </div>

      <div class="flex items-center justify-between gap-3">
        <button
          class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 transition disabled:opacity-60 disabled:cursor-not-allowed"
          :disabled="loadingStudents || !selectedClass"
          @click="loadStudents"
        >
          {{ loadingStudents ? 'Loading...' : 'Load Students' }}
        </button>

        <div class="flex items-center gap-2 text-xs text-gray-500">
          <span
            class="inline-flex h-2 w-2 rounded-full"
            :class="
              loadingStudents ? 'bg-amber-400' : hasStudents ? 'bg-emerald-400' : 'bg-gray-300'
            "
          />
          <span>
            {{
              loadingStudents
                ? 'Fetching students...'
                : hasStudents
                  ? `Loaded ${totalCount} students`
                  : 'No students loaded'
            }}
          </span>
        </div>
      </div>
    </div>

    <!-- Stats + actions -->
    <div v-if="hasStudents" class="space-y-4">
      <!-- Stat cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-2">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
          <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Present</p>
          <p class="mt-2 text-2xl font-semibold text-emerald-600">
            {{ presentCount }}
          </p>
          <p class="mt-1 text-xs text-gray-400">Students marked present</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
          <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total</p>
          <p class="mt-2 text-2xl font-semibold text-gray-900">
            {{ totalCount }}
          </p>
          <p class="mt-1 text-xs text-gray-400">Students in this list</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
          <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Attendance %</p>
          <div class="mt-2 flex items-baseline gap-1">
            <span
              class="text-3xl font-bold"
              :class="
                percent >= 90
                  ? 'text-emerald-600'
                  : percent >= 75
                    ? 'text-amber-500'
                    : 'text-red-500'
              "
            >
              {{ percent }}%
            </span>
          </div>
          <p class="mt-1 text-xs text-gray-400">Present / Total</p>
        </div>
      </div>

      <!-- Bulk actions -->
      <div
        class="bg-white rounded-2xl shadow-sm border border-gray-100 px-4 py-3 sm:px-6 sm:py-4 flex flex-wrap items-center gap-3 mb-2"
      >
        <div class="flex flex-wrap items-center gap-2">
          <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">
            Quick Actions
          </span>
          <button
            class="px-3 py-1.5 text-xs rounded-full border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition"
            @click="markAll('present')"
          >
            Mark All Present
          </button>
          <button
            class="px-3 py-1.5 text-xs rounded-full border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 transition"
            @click="markAll('absent')"
          >
            Mark All Absent
          </button>
          <button
            class="px-3 py-1.5 text-xs rounded-full border border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100 transition"
            @click="markAll('late')"
          >
            Mark All Late
          </button>
        </div>

        <div class="ml-auto text-sm text-gray-500">
          Present:
          <span class="font-semibold text-gray-900">{{ presentCount }}</span>
          /
          <span class="font-semibold text-gray-900">{{ totalCount }}</span>
          (<span class="font-semibold">{{ percent }}%</span>)
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-400">
              <tr>
                <th
                  class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                >
                  Name
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                >
                  Student ID
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                >
                  Class
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                >
                  Section
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                >
                  Status
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="s in students"
                :key="s.id"
                class="border-b border-gray-300 hover:bg-gray-50/80 transition"
              >
                <td class="px-3 py-2">
                  <div class="flex items-center gap-2">
                    <div
                      class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-50 text-xs font-semibold text-blue-600"
                    >
                      {{ s.photo || '?' }}
                    </div>
                    <div>
                      <div class="font-medium text-gray-900">
                        {{ s.name }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-3 py-3 text-gray-700">
                  {{ s.student_id }}
                </td>
                <td class="px-3 py-2 text-gray-700">
                  {{ s.class }}
                </td>
                <td class="px-3 py-2 text-gray-700">
                  {{ s.section }}
                </td>
                <td class="px-3 py-2">
                  <select
                    v-model="statuses[s.id]"
                    class="rounded-full border border-gray-300 bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  >
                    <option value="present">Present</option>
                    <option value="absent">Absent</option>
                    <option value="late">Late</option>
                  </select>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Save button -->
      <div class="flex justify-end">
        <button
          class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 transition disabled:opacity-60 disabled:cursor-not-allowed"
          :disabled="saving || !hasStudents"
          @click="submit"
        >
          {{ saving ? 'Saving...' : 'Save Attendance' }}
        </button>
      </div>
    </div>

    <!-- Empty state -->
    <p v-else class="text-sm text-gray-500">
      Select a class, optionally a section, choose the date and click
      <span class="font-medium text-gray-700">"Load Students"</span>
      to start marking attendance.
    </p>
  </div>
</template>
