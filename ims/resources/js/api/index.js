/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */

import axios from 'axios'

const api = axios.create({
    baseURL: '/ims/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    }
})

// Request interceptor - add JWT token
api.interceptors.request.use(config => {
    const token = localStorage.getItem('ims_token')
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }
    return config
})

// Response interceptor - handle 401
api.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            localStorage.removeItem('ims_token')
            localStorage.removeItem('ims_user')
            window.location.href = '/ims/login'
        }
        return Promise.reject(error)
    }
)

export default api
