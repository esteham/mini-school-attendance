<script setup>
import { ref, onMounted } from 'vue'
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

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

const todayStats = ref(null)
const monthlyData = ref(null)

const chartData = ref({
  labels: [],
  datasets: [],
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
}

const loadToday = async () => {
  const res = await api.get('/attendance/daily-stats')
  todayStats.value = res.data
}

const loadMonthly = async () => {
  const now = new Date()
  const res = await api.get('/attendance/monthly-report', {
    params: {
      year: now.getFullYear(),
      month: now.getMonth() + 1,
    },
  })

  monthlyData.value = res.data

  chartData.value = {
    labels: res.data.records.map((r) => r.student.name),
    datasets: [
      {
        label: 'Present (%)',
        data: res.data.records.map((r) => r.percent),
      },
    ],
  }
}

onMounted(async () => {
  await loadToday()
  await loadMonthly()
})
</script>

<template>
  <div class="space-y-6">
    <h2 class="text-2xl font-bold mb-4">Dashboard</h2>

    <section v-if="todayStats" class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="bg-white p-4 rounded shadow">
        <p class="text-gray-500">Date</p>
        <p class="text-xl font-semibold">{{ todayStats.date }}</p>
      </div>
      <div class="bg-white p-4 rounded shadow">
        <p class="text-gray-500">Total</p>
        <p class="text-xl font-semibold">{{ todayStats.total }}</p>
      </div>
      <div class="bg-white p-4 rounded shadow">
        <p class="text-gray-500">Present</p>
        <p class="text-xl font-semibold">{{ todayStats.present }}</p>
      </div>
      <div class="bg-white p-4 rounded shadow">
        <p class="text-gray-500">Attendance %</p>
        <p class="text-xl font-semibold">{{ todayStats.percent }}%</p>
      </div>
    </section>

    <section class="bg-white p-4 rounded shadow h-80">
      <h3 class="text-lg font-semibold mb-2">Monthly Attendance</h3>
      <Bar v-if="chartData.datasets.length" :data="chartData" :options="chartOptions" />
      <p v-else>Loading chart...</p>
    </section>
  </div>
</template>
