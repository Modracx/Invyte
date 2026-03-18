/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/api/index.js'

export const useAuthStore = defineStore('auth', () => {
    const token = ref(localStorage.getItem('ims_token') || null)
    const user = ref(JSON.parse(localStorage.getItem('ims_user') || 'null'))

    const isAuthenticated = computed(() => !!token.value)
    const isAdmin = computed(() => user.value?.role === 'admin')

    async function login(email, password) {
        const { data } = await api.post('/auth/login', { email, password })
        token.value = data.token
        user.value = data.user
        localStorage.setItem('ims_token', data.token)
        localStorage.setItem('ims_user', JSON.stringify(data.user))
        return data
    }

    async function logout() {
        try { await api.post('/auth/logout') } catch {}
        token.value = null
        user.value = null
        localStorage.removeItem('ims_token')
        localStorage.removeItem('ims_user')
    }

    function canAccessSource(sourceCode) {
        if (!user.value) return false
        if (isAdmin.value) return true
        return user.value.assigned_sources?.includes(sourceCode) ?? false
    }

    return { token, user, isAuthenticated, isAdmin, login, logout, canAccessSource }
})
