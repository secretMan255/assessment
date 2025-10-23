import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes: RouteRecordRaw[] = [
  { path: '/', redirect: { name: 'login' } },
  {
    path: '/login',
    name: 'login',
    meta: { public: true },
    component: () => import('@/views/login/Login.vue'),
  },
  {
    path: '/notes',
    name: 'notes',
    component: () => import('@/views/note/Notes.vue'),
  },
  { path: '/:pathMatch(.*)*', redirect: { name: 'login' } },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

router.beforeEach((to) => {
  const auth = useAuthStore()
  if (!to.meta.public && !auth.isAuthed) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }
})

export default router
