<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '../api/axios'

const students = ref([])
const links = ref([])
const meta = ref(null)

const search = ref('')
const filterClass = ref('')
const page = ref(1)

const loading = ref(false)
const error = ref(null)

const loadStudents = async () => {
  loading.value = true
  error.value = null

  try {
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
  } catch (e) {
    console.error(e)
    error.value = 'Failed to load students. Please try again.'
  } finally {
    loading.value = false
  }
}

const getPageFromUrl = (url) => {
  if (!url) return null
  try {
    const urlObj = new URL(url, window.location.origin)
    return parseInt(urlObj.searchParams.get('page')) || null
  } catch {
    const match = url.match(/[?&]page=(\d+)/)
    return match ? parseInt(match[1]) : null
  }
}

// Reload whenever filters or page change
watch([search, filterClass, page], () => {
  loadStudents()
})

onMounted(() => {
  loadStudents()
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div
      class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-gradient-to-r from-blue-400 via-indigo-300 to-blue-600 rounded-2xl px-6 py-4 shadow-sm text-white"
    >
      <div>
        <h2 class="text-2xl font-semibold tracking-tight">Students</h2>
        <p class="text-sm text-blue-100">Manage and browse all registered students</p>
      </div>
      <div
        v-if="meta"
        class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-xs sm:text-sm"
      >
        <span class="text-blue-100">Total Students</span>
        <span class="font-semibold">
          {{ meta.total }}
        </span>
      </div>
    </div>

    <!-- Error state -->
    <div
      v-if="error"
      class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
    >
      {{ error }}
    </div>

    <!-- Filters -->
    <div
      class="bg-white rounded-2xl shadow-sm border border-gray-100 px-4 py-3 sm:px-6 sm:py-4 mb-2 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between"
    >
      <div class="flex flex-1 flex-wrap gap-3">
        <!-- Search -->
        <div class="relative flex-1 min-w-[150px]">
          <span
            class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400"
          >
            🔍
          </span>
          <input
            v-model="search"
            type="text"
            placeholder="Search by name or student ID"
            class="w-full rounded-xl border border-gray-200 bg-gray-50 pl-10 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
          />
        </div>

        <!-- Class filter -->
        <div class="relative w-full sm:w-55">
          <span
            class="pointer-events-none absolute inset-y-0 left-2 flex items-center text-gray-400 text-xs uppercase"
          >
            Class
          </span>
          <input
            v-model="filterClass"
            type="text"
            placeholder="e.g. 10, 9, 8"
            class="w-full rounded-xl border border-gray-200 bg-gray-50 pl-12 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
          />
        </div>
      </div>

      <!-- Right side info -->
      <div class="flex items-center gap-2 text-xs text-gray-500 mt-1 sm:mt-0">
        <span
          class="inline-flex h-2 w-2 rounded-full"
          :class="loading ? 'bg-amber-400' : 'bg-emerald-400'"
        />
        <span>{{ loading ? 'Refreshing list…' : 'Up to date' }}</span>
      </div>
    </div>

    <!-- Table / content -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <!-- Loading skeleton -->
      <div v-if="loading && !students.length" class="p-4 sm:p-6 space-y-3 animate-pulse">
        <div class="h-4 w-40 bg-gray-200 rounded" />
        <div class="h-10 w-full bg-gray-100 rounded" />
        <div class="h-10 w-full bg-gray-100 rounded" />
        <div class="h-10 w-full bg-gray-100 rounded" />
      </div>

      <!-- Table -->
      <div v-else class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-100 border border-gray-300">
            <tr>
              <th
                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
              >
                #
              </th>
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
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(s, idx) in students"
              :key="s.id"
              class="border border-gray-300 hover:bg-gray-50/90 transition"
            >
              <td class="px-4 py-3 text-gray-500">
                {{ (meta?.from || 1) + idx }}
              </td>
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
              <td class="px-3 py-2 text-gray-700">
                {{ s.student_id }}
              </td>
              <td class="px-4 py-2 text-gray-700">
                {{ s.class }}
              </td>
              <td class="px-8 py-2 text-gray-700">
                {{ s.section }}
              </td>
            </tr>

            <tr v-if="!students.length && !loading">
              <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">
                No students found for the current filters.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="meta" class="flex flex-col sm:flex-row items-center justify-between gap-3 text-sm">
      <div class="text-gray-500">
        Showing
        <span class="font-medium text-gray-900">{{ meta.from || 0 }}</span>
        –
        <span class="font-medium text-gray-900">{{ meta.to || 0 }}</span>
        of
        <span class="font-medium text-gray-900">{{ meta.total }}</span>
        students
      </div>

      <div class="flex items-center gap-2">
        <button
          class="px-3 py-1.5 rounded-xl border text-sm font-medium transition disabled:opacity-40 disabled:cursor-not-allowed hover:bg-gray-50"
          :disabled="!meta.links.find((l) => l.label.includes('Previous'))?.url"
          @click="
            page = getPageFromUrl(meta.links.find((l) => l.label.includes('Previous'))?.url) || page
          "
        >
          ‹ Prev
        </button>
        <span class="text-gray-500">
          Page
          <span class="font-semibold text-gray-900">{{ meta.current_page }}</span>
          of
          <span class="font-semibold text-gray-900">{{ meta.last_page }}</span>
        </span>
        <button
          class="px-3 py-1.5 rounded-xl border text-sm font-medium transition disabled:opacity-40 disabled:cursor-not-allowed hover:bg-gray-50"
          :disabled="!meta.links.find((l) => l.label.includes('Next'))?.url"
          @click="
            page = getPageFromUrl(meta.links.find((l) => l.label.includes('Next'))?.url) || page
          "
        >
          Next ›
        </button>
      </div>
    </div>
  </div>
</template>
