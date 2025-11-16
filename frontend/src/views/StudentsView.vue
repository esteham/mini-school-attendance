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
const success = ref('')

// Modal and form states
const showModal = ref(false)
const editingStudent = ref(null)
const formData = ref({
  name: '',
  class: '',
  section: '',
  photo: null,
})
const formErrors = ref({})
const submitting = ref(false)
const photoInput = ref(null)

const loadStudents = async () => {
  loading.value = true
  error.value = null
  success.value = ''

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

// Modal functions
const openAddModal = () => {
  editingStudent.value = null
  formData.value = { name: '', class: '', section: '', photo: null }
  formErrors.value = {}
  showModal.value = true
}

const openEditModal = (student) => {
  editingStudent.value = student
  formData.value = { ...student, photo: null }
  formErrors.value = {}
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingStudent.value = null
  formData.value = { name: '', class: '', section: '', photo: null }
  formErrors.value = {}
  if (photoInput.value) {
    photoInput.value.value = ''
  }
}

const handlePhotoChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    formData.value.photo = file
  } else {
    formData.value.photo = null
  }
}

const submitForm = async () => {
  submitting.value = true
  formErrors.value = {}
  error.value = ''
  success.value = ''

  try {
    const formDataToSend = new FormData()
    formDataToSend.append('name', formData.value.name)
    formDataToSend.append('class', formData.value.class)
    formDataToSend.append('section', formData.value.section)
    if (formData.value.photo) {
      formDataToSend.append('photo', formData.value.photo)
    }

    if (editingStudent.value) {
      // Update
      await api.post(`/students/${editingStudent.value.id}`, formDataToSend, {
        params: { _method: 'PUT' },
      })
    } else {
      // Create
      await api.post('/students', formDataToSend)
    }
    await loadStudents()
    success.value = editingStudent.value
      ? 'Student updated successfully!'
      : 'Student added successfully!'
    setTimeout(() => {
      success.value = ''
    }, 3000)
    alert(success.value)
    closeModal()
  } catch (e) {
    console.error(e)
    if (e.response && e.response.data.errors) {
      formErrors.value = e.response.data.errors
    } else {
      error.value = 'Failed to save student. Please try again.'
    }
  } finally {
    submitting.value = false
  }
}

const deleteStudent = async (student) => {
  if (!confirm(`Are you sure you want to delete ${student.name}?`)) return

  try {
    await api.delete(`/students/${student.id}`)
    await loadStudents()
    success.value = 'Student deleted successfully!'
    setTimeout(() => {
      success.value = ''
    }, 3000)
    alert(success.value)
  } catch (e) {
    console.error(e)
    error.value = 'Failed to delete student. Please try again.'
  }
}

// Reload whenever filters or page change
watch([search, filterClass, page], () => {
  loadStudents()
})

// Ensure section is always in uppercase
watch(
  () => formData.value.section,
  (newVal) => {
    if (newVal) {
      formData.value.section = newVal.toUpperCase()
    }
  },
)

// Ensure name starts with uppercase letter
watch(
  () => formData.value.name,
  (newVal) => {
    if (newVal) {
      formData.value.name = newVal.charAt(0).toUpperCase() + newVal.slice(1)
    }
  },
)

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
      <div class="flex items-center gap-3">
        <button
          @click="openAddModal"
          class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-sm font-medium hover:bg-white/20 transition"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 6v6m0 0v6m0-6h6m-6 0H6"
            ></path>
          </svg>
          Add Student
        </button>
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
    </div>

    <!-- Success state -->
    <div
      v-if="success"
      class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700"
    >
      {{ success }}
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
              <th
                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
              >
                Actions
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
                  <img
                    v-if="s.photo"
                    :src="s.photo"
                    alt="Student photo"
                    class="h-8 w-8 rounded-full object-cover"
                  />
                  <div
                    v-else
                    class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-50 text-xs font-semibold text-blue-600"
                  >
                    ?
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
              <td class="px-4 py-2">
                <div class="flex items-center gap-2">
                  <button
                    @click="openEditModal(s)"
                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 transition"
                  >
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                      ></path>
                    </svg>
                    Edit
                  </button>
                  <button
                    @click="deleteStudent(s)"
                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-red-600 bg-red-50 rounded-md hover:bg-red-100 transition"
                  >
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                      ></path>
                    </svg>
                    Delete
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="!students.length && !loading">
              <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
                No students found for the current filters.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-2xl shadow-xl max-w-md w-full mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">
            {{ editingStudent ? 'Edit Student' : 'Add New Student' }}
          </h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              ></path>
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitForm" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input
              v-model="formData.name"
              type="text"
              required
              class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
              :class="{ 'border-red-300': formErrors.name }"
            />
            <p v-if="formErrors.name" class="text-red-600 text-xs mt-1">{{ formErrors.name[0] }}</p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
              <input
                v-model="formData.class"
                type="number"
                required
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                :class="{ 'border-red-300': formErrors.class }"
              />
              <p v-if="formErrors.class" class="text-red-600 text-xs mt-1">
                {{ formErrors.class[0] }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Section</label>
              <input
                v-model="formData.section"
                type="text"
                required
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                :class="{ 'border-red-300': formErrors.section }"
              />
              <p v-if="formErrors.section" class="text-red-600 text-xs mt-1">
                {{ formErrors.section[0] }}
              </p>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Student ID <span class="text-red-500">*</span> (Auto Generated)
            </label>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Photo {{ editingStudent ? '' : '*' }}</label
            >
            <div v-if="editingStudent && editingStudent.photo" class="mb-2">
              <img
                :src="editingStudent.photo"
                alt="Current student photo"
                class="h-16 w-16 rounded-full object-cover border border-gray-200"
              />
            </div>
            <input
              ref="photoInput"
              type="file"
              :required="!editingStudent"
              accept="image/*"
              @change="handlePhotoChange"
              class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
            />
            <p v-if="editingStudent && editingStudent.photo" class="text-xs text-gray-500 mt-1">
              Current photo will be replaced if you select a new one.
            </p>
          </div>

          <div class="flex items-center gap-3 pt-4">
            <button
              type="button"
              @click="closeModal"
              class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="flex-1 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
            >
              {{ submitting ? 'Saving...' : editingStudent ? 'Update' : 'Create' }}
            </button>
          </div>
        </form>
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
