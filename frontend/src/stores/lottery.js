import { defineStore } from 'pinia'
import { lotteryApi, ticketApi } from '../api'

export const useLotteryStore = defineStore('lottery', {
  state: () => ({
    lotteries: [],
    popularLotteries: [],
    currentLottery: null,
    userLotteries: [],
    userTickets: [],
    loading: false,
    error: null,
    pagination: {
      currentPage: 1,
      lastPage: 1,
      total: 0,
    },
  }),
  
  actions: {
    async fetchLotteries(params = {}) {
      this.loading = true
      this.error = null
      try {
        const response = await lotteryApi.getAll(params)
        this.lotteries = response.data.data
        this.pagination = {
          currentPage: response.data.current_page,
          lastPage: response.data.last_page,
          total: response.data.total,
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch lotteries'
        throw error
      } finally {
        this.loading = false
      }
    },
    
    async fetchPopular() {
      try {
        const response = await lotteryApi.getPopular()
        this.popularLotteries = response.data
      } catch (error) {
        console.error('Failed to fetch popular lotteries:', error)
      }
    },
    
    async fetchLottery(id) {
      this.loading = true
      this.error = null
      try {
        const response = await lotteryApi.getOne(id)
        this.currentLottery = response.data
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch lottery'
        throw error
      } finally {
        this.loading = false
      }
    },
    
    async fetchUserLotteries() {
      this.loading = true
      try {
        const response = await lotteryApi.getUserLotteries()
        this.userLotteries = response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch your lotteries'
        throw error
      } finally {
        this.loading = false
      }
    },
    
    async createLottery(formData) {
      this.loading = true
      try {
        const response = await lotteryApi.create(formData)
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create lottery'
        throw error
      } finally {
        this.loading = false
      }
    },
    
    async updateLottery(id, formData) {
      this.loading = true
      try {
        const response = await lotteryApi.update(id, formData)
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update lottery'
        throw error
      } finally {
        this.loading = false
      }
    },
    
    async deleteLottery(id) {
      this.loading = true
      try {
        await lotteryApi.delete(id)
        this.userLotteries = this.userLotteries.filter(l => l.id !== id)
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete lottery'
        throw error
      } finally {
        this.loading = false
      }
    },
    
    async purchaseTickets(lotteryId, quantity) {
      this.loading = true
      try {
        const response = await ticketApi.purchase(lotteryId, quantity)
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to purchase tickets'
        throw error
      } finally {
        this.loading = false
      }
    },
    
    async fetchUserTickets() {
      this.loading = true
      try {
        const response = await ticketApi.getUserTickets()
        this.userTickets = response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch your tickets'
        throw error
      } finally {
        this.loading = false
      }
    },
  },
})

