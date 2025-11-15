import { createRouter, createWebHistory } from 'vue-router'
import DashboardView from '../views/DashboardView.vue'
import StudentsView from '../views/StudentsView.vue'

const routes = [
  { path: '/', name: 'dashboard', component: DashboardView },
  { path: '/students', name: 'students', component: StudentsView },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

export default router
