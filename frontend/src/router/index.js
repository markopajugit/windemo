import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('../views/Home.vue'),
  },
  {
    path: '/browse',
    name: 'browse',
    component: () => import('../views/Browse.vue'),
  },
  {
    path: '/lottery/:id',
    name: 'lottery-detail',
    component: () => import('../views/LotteryDetail.vue'),
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/Login.vue'),
    meta: { guest: true },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('../views/Register.vue'),
    meta: { guest: true },
  },
  {
    path: '/verify-email',
    name: 'verify-email',
    component: () => import('../views/VerifyEmail.vue'),
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('../views/Dashboard.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'dashboard-home',
        component: () => import('../views/dashboard/Overview.vue'),
      },
      {
        path: 'my-lotteries',
        name: 'my-lotteries',
        component: () => import('../views/dashboard/MyLotteries.vue'),
      },
      {
        path: 'my-tickets',
        name: 'my-tickets',
        component: () => import('../views/dashboard/MyTickets.vue'),
      },
      {
        path: 'create-lottery',
        name: 'create-lottery',
        component: () => import('../views/dashboard/CreateLottery.vue'),
      },
      {
        path: 'edit-lottery/:id',
        name: 'edit-lottery',
        component: () => import('../views/dashboard/EditLottery.vue'),
      },
    ],
  },
  {
    path: '/admin',
    name: 'admin',
    component: () => import('../views/Admin.vue'),
    meta: { requiresAuth: true, requiresAdmin: true },
    children: [
      {
        path: '',
        name: 'admin-dashboard',
        component: () => import('../views/admin/AdminDashboard.vue'),
      },
      {
        path: 'pending',
        name: 'admin-pending',
        component: () => import('../views/admin/PendingLotteries.vue'),
      },
      {
        path: 'all-lotteries',
        name: 'admin-all-lotteries',
        component: () => import('../views/admin/AllLotteries.vue'),
      },
      {
        path: 'lottery/:id',
        name: 'admin-lottery-edit',
        component: () => import('../views/admin/EditLottery.vue'),
      },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // Initialize auth state if not done
  if (!authStore.initialized) {
    await authStore.initAuth()
  }
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login', query: { redirect: to.fullPath } })
  } else if (to.meta.requiresAdmin && !authStore.isAdmin) {
    next({ name: 'home' })
  } else if (to.meta.guest && authStore.isAuthenticated) {
    next({ name: 'dashboard' })
  } else {
    next()
  }
})

export default router

