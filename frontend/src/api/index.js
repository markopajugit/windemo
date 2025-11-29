import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Request interceptor to add auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor to handle auth errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

// Auth API
export const authApi = {
  register: (data) => api.post('/register', data),
  login: (data) => api.post('/login', data),
  logout: () => api.post('/logout'),
  getUser: () => api.get('/user'),
  verifyEmail: (id, hash, params) => api.get(`/email/verify/${id}/${hash}`, { params }),
  resendVerification: () => api.post('/email/verification-notification'),
}

// Lottery API
export const lotteryApi = {
  // Public
  getAll: (params) => api.get('/lotteries', { params }),
  getCategories: () => api.get('/lotteries/categories'),
  getPopular: () => api.get('/lotteries/popular'),
  getUpcoming: () => api.get('/lotteries/upcoming'),
  getEnded: () => api.get('/lotteries/ended'),
  getOne: (id) => api.get(`/lotteries/${id}`),
  
  // User lotteries
  getUserLotteries: () => api.get('/user/lotteries'),
  create: (data) => api.post('/user/lotteries', data, {
    headers: { 'Content-Type': 'multipart/form-data' },
  }),
  update: (id, data) => api.post(`/user/lotteries/${id}`, data, {
    headers: { 'Content-Type': 'multipart/form-data' },
  }),
  delete: (id) => api.delete(`/user/lotteries/${id}`),
}

// Ticket API
export const ticketApi = {
  purchase: (lotteryId, quantity) => api.post(`/lotteries/${lotteryId}/tickets`, { quantity }),
  getUserTickets: () => api.get('/user/tickets'),
}

// Admin API
export const adminApi = {
  getAllLotteries: (params) => api.get('/admin/lotteries', { params }),
  approveLottery: (id) => api.put(`/admin/lotteries/${id}/approve`),
  rejectLottery: (id, reason) => api.put(`/admin/lotteries/${id}/reject`, { reason }),
  updateLottery: (id, data) => api.put(`/admin/lotteries/${id}`, data),
  getStats: () => api.get('/admin/stats'),
}

export default api

