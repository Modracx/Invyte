<template>
  <div class="min-h-screen flex items-center justify-center bg-slate-900">
    <div class="w-full max-w-sm">
      <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-600 rounded-xl mb-3">
          <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
          </svg>
        </div>
        <h1 class="text-xl font-bold text-white">IMS</h1>
        <p class="text-sm text-slate-400">Inventory Management System</p>
      </div>

      <div class="card p-6">
        <form @submit.prevent="handleLogin" class="space-y-4">
          <div>
            <label class="filter-label block mb-1">Email address</label>
            <input v-model="form.email" type="email" required autofocus class="form-input" placeholder="you@example.com" />
          </div>
          <div>
            <label class="filter-label block mb-1">Password</label>
            <input v-model="form.password" type="password" required class="form-input" placeholder="••••••••" />
          </div>
          <div v-if="error" class="bg-red-50 border border-red-200 text-red-600 text-xs px-3 py-2 rounded-md">{{ error }}</div>
          <button type="submit" :disabled="loading" class="btn-primary w-full justify-center py-2">
            {{ loading ? 'Signing in…' : 'Sign In' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */

import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth.js'

const router = useRouter()
const auth = useAuthStore()
const form = ref({ email: '', password: '' })
const error = ref('')
const loading = ref(false)

async function handleLogin() {
  error.value = ''
  loading.value = true
  try {
    await auth.login(form.value.email, form.value.password)
    router.push('/ims/dashboard')
  } catch (e) {
    error.value = e.response?.data?.message || 'Invalid credentials'
  } finally {
    loading.value = false
  }
}
</script>
