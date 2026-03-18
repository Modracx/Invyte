<template>
  <header class="bg-white border-b border-slate-200 px-4 py-2 flex items-center justify-between flex-shrink-0">
    <h2 class="text-sm font-semibold text-slate-700">{{ pageTitle }}</h2>
    <div class="flex items-center gap-3">
      <span class="text-xs text-slate-500">{{ auth.user?.name }}</span>
      <span class="badge" :class="auth.isAdmin ? 'badge-blue' : 'badge-green'">{{ auth.user?.role }}</span>
      <button @click="handleLogout" class="text-xs text-slate-400 hover:text-slate-700 transition-colors">Sign out</button>
    </div>
  </header>
</template>

<script setup>
/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */

import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth.js'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

const titles = {
  dashboard: 'Dashboard', products: 'Products', 'product-detail': 'Product Detail',
  sources: 'Warehouses', 'source-locations': 'Location Layout', movements: 'Stock Movements', 'purchase-orders': 'Purchase Orders',
  'po-detail': 'Purchase Order', suppliers: 'Suppliers', alerts: 'Low Stock Alerts',
  orders: 'Orders', 'order-fulfill': 'Fulfill Order', fulfillments: 'Fulfillments',
  'report-low-stock': 'Low Stock Report', 'report-stock-value': 'Stock Value Report', 'report-movements': 'Movement Report',
}

const pageTitle = computed(() => titles[route.name] || 'IMS')

async function handleLogout() {
  await auth.logout()
  router.push('/ims/login')
}
</script>
