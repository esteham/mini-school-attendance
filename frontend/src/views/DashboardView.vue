<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '../api/axios'
import { Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
} from 'chart.js'
import ChartDataLabels from 'chartjs-plugin-datalabels'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ChartDataLabels)

const todayStats = ref(null)
const monthlyData = ref(null)
const selectedClass = ref('')
const selectedSection = ref('')

const chartData = ref({
  labels: [],
  datasets: [],
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true,
      // position: 'top',
      labels: {
        font: {
          size: 10,
        },
      },
    },
    datalabels: {
      anchor: 'start',
      align: 'bottom',
      formatter: (value) => `${value}%`,
      font: {
        color: 'white',
        weight: 'bold',
        size: 10,
      },
      clamp: true,
    },
    tooltip: {
      callbacks: {
        label: (ctx) => ` ${ctx.parsed.y}%`,
      },
    },
  },
  scales: {
    x: {
      ticks: {
        maxRotation: 40,
        minRotation: 0,
        font: {
          size: 11,
        },
      },
      grid: {
        display: false,
      },
    },
    y: {
      beginAtZero: true,
      max: 100,
      ticks: {
        stepSize: 10,
        font: {
          size: 11,
        },
        callback: (value) => `${value}%`,
      },
      grid: {
        drawBorder: false,
      },
    },
  },
}

const loading = ref(true)
const error = ref(null)

const loadToday = async () => {
  const res = await api.get('/attendance/daily-stats')
  todayStats.value = res.data
}

const loadMonthly = async () => {
  const now = new Date()
  const params = {
    year: now.getFullYear(),
    month: now.getMonth() + 1,
  }
  if (selectedClass.value) {
    params.class = selectedClass.value
  }
  if (selectedSection.value) {
    params.section = selectedSection.value
  }
  const res = await api.get('/attendance/monthly-report', {
    params,
  })

  monthlyData.value = res.data

  chartData.value = {
    labels: res.data.records.map((r) => r.student.name),
    datasets: [
      {
        label: 'Present (%)',
        data: res.data.records.map((r) => r.percent),
        backgroundColor: 'rgba(59,130,246,0.8)',
        borderRadius: 6,
        borderSkipped: false,
      },
    ],
  }
}

watch([selectedClass, selectedSection], () => {
  loadMonthly()
})

onMounted(async () => {
  loading.value = true
  error.value = null
  try {
    await Promise.all([loadToday(), loadMonthly()])
  } catch (e) {
    console.error(e)
    error.value = 'Failed to load dashboard data. Please try again.'
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div
      class="flex items-center justify-between bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 rounded-2xl px-6 py-4 shadow-sm text-white"
    >
      <div>
        <h2 class="text-2xl font-semibold tracking-tight">Attendance Dashboard</h2>
        <p class="text-sm text-blue-100">
          Overview of today’s attendance and this month’s performance
        </p>
      </div>
      <div
        v-if="todayStats"
        class="hidden sm:flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-sm"
      >
        <span class="text-blue-100">Today</span>
        <span class="font-medium">
          {{ todayStats.date }}
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

    <!-- Loading skeleton -->
    <div v-if="loading && !error" class="space-y-4 animate-pulse">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div
          v-for="i in 4"
          :key="i"
          class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100"
        >
          <div class="h-3 w-20 bg-gray-200 rounded mb-3" />
          <div class="h-6 w-16 bg-gray-200 rounded" />
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100 h-80">
        <div class="h-4 w-40 bg-gray-200 rounded mb-4" />
        <div class="h-full w-full bg-gray-100 rounded" />
      </div>
    </div>

    <!-- Main content -->
    <div v-else class="space-y-4">
      <!-- Stat cards -->
      <section v-if="todayStats" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Date -->
        <div
          class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100 flex flex-col justify-between"
        >
          <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Date</p>
          <p class="mt-2 text-xl font-semibold text-gray-900">
            {{ todayStats.date }}
          </p>
          <p class="mt-1 text-xs text-gray-400">Current attendance session</p>
        </div>

        <!-- Total -->
        <div
          class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100 flex flex-col justify-between"
        >
          <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total Students</p>
          <p class="mt-2 text-2xl font-semibold text-gray-900">
            {{ todayStats.total }}
          </p>
          <p class="mt-1 text-xs text-gray-400">Enrolled in selected class/section</p>
        </div>

        <!-- Present -->
        <div
          class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100 flex flex-col justify-between"
        >
          <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Present</p>
          <p class="mt-2 text-2xl font-semibold text-emerald-600">
            {{ todayStats.present }}
          </p>
          <p class="mt-1 text-xs text-gray-400">Marked present today</p>
        </div>

        <!-- Attendance % -->
        <div
          class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100 flex flex-col justify-between"
        >
          <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Attendance Rate</p>
          <div class="mt-2 flex items-baseline gap-2">
            <span
              class="text-3xl font-bold"
              :class="
                todayStats.percent >= 90
                  ? 'text-emerald-600'
                  : todayStats.percent >= 75
                    ? 'text-amber-500'
                    : 'text-red-500'
              "
            >
              {{ todayStats.percent }}%
            </span>
            <span class="text-xs text-gray-400"> of total students </span>
          </div>
          <div
            class="mt-3 inline-flex items-center rounded-full px-3 py-1 text-xs font-medium"
            :class="
              todayStats.percent >= 90
                ? 'bg-emerald-50 text-emerald-700'
                : todayStats.percent >= 75
                  ? 'bg-amber-50 text-amber-700'
                  : 'bg-red-50 text-red-700'
            "
          >
            <span v-if="todayStats.percent >= 90">Excellent attendance</span>
            <span v-else-if="todayStats.percent >= 75">Good attendance</span>
            <span v-else>Needs attention</span>
          </div>
        </div>
      </section>

      <!-- Monthly chart -->
      <section class="bg-white rounded-2xl shadow-sm p-4 md:p-6 border border-gray-100 h-80">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h3 class="text-base md:text-lg font-semibold text-gray-900">Monthly Attendance</h3>
            <p class="text-xs text-gray-500">
              Student-wise attendance percentage for the current month
              <span v-if="selectedClass || selectedSection">
                ({{ selectedClass || 'All Classes'
                }}{{ selectedSection ? ` - ${selectedSection}` : '' }})
              </span>
            </p>
          </div>
          <div class="flex items-center gap-2">
            <input
              v-model="selectedClass"
              type="text"
              placeholder="Class (e.g. Ten)"
              class="w-26 px-3 py-2 text-xs border border-gray-600 rounded"
            />
            <input
              v-model="selectedSection"
              type="text"
              placeholder="Section (optional)"
              class="w-23 px-3 py-2 text-xs border border-gray-600 rounded"
            />
          </div>
        </div>

        <div class="h-[calc(100%-1rem)]">
          <Bar v-if="chartData.datasets.length" :data="chartData" :options="chartOptions" />
          <p v-else class="text-sm text-gray-400 flex items-center justify-center h-full">
            No monthly attendance data available.
          </p>
        </div>
      </section>
    </div>
  </div>
</template>
