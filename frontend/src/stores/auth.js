import { defineStore } from 'pinia'
import { authApi } from '../api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token'),
    initialized: false,
  }),
  
  getters: {
    isAuthenticated: (state) => !!state.token && !!state.user,
    isAdmin: (state) => state.user?.is_admin === true,
    isEmailVerified: (state) => !!state.user?.email_verified_at,
  },
  
  actions: {
    async initAuth() {
      if (this.token) {
        try {
          const response = await authApi.getUser()
          this.user = response.data
        } catch (error) {
          this.logout()
        }
      }
      this.initialized = true
    },
    
    async register(data) {
      const response = await authApi.register(data)
      this.token = response.data.token
      this.user = response.data.user
      localStorage.setItem('token', this.token)
      return response.data
    },
    
    async login(data) {
      const response = await authApi.login(data)
      this.token = response.data.token
      this.user = response.data.user
      localStorage.setItem('token', this.token)
      return response.data
    },
    
    async logout() {
      try {
        if (this.token) {
          await authApi.logout()
        }
      } catch (error) {
        // Ignore logout errors
      } finally {
        this.token = null
        this.user = null
        localStorage.removeItem('token')
      }
    },
    
    async fetchUser() {
      const response = await authApi.getUser()
      this.user = response.data
      return this.user
    },
  },
})

