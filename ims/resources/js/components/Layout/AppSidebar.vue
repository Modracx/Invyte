<template>
  <aside class="w-56 bg-slate-900 text-slate-300 flex flex-col min-h-screen flex-shrink-0">
    <div class="px-4 py-3 border-b border-slate-800">
      <div class="flex items-center gap-2">
        <div class="w-7 h-7 bg-blue-600 rounded-md flex items-center justify-center flex-shrink-0">
          <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
          </svg>
        </div>
        <div>
          <p class="text-sm font-semibold text-white leading-none">IMS</p>
          <p class="text-xs text-slate-500 leading-none mt-0.5">Inventory</p>
        </div>
      </div>
    </div>

    <nav class="flex-1 px-2 py-3 space-y-0.5 overflow-y-auto">
      <NavItem v-for="item in visibleItems" :key="item.to" :item="item" />

      <div class="pt-3 mt-2 border-t border-slate-800">
        <p class="text-xs font-semibold text-slate-500 px-2 mb-1 uppercase tracking-wider">Reports</p>
        <NavItem v-for="item in reportItems" :key="item.to" :item="item" />
      </div>
    </nav>

    <div class="px-3 py-2 border-t border-slate-800">
      <p class="text-xs text-slate-500 truncate">{{ auth.user?.email }}</p>
    </div>
  </aside>
</template>

<script setup>
/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */

import { computed, h } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { useAuthStore } from '@/store/auth.js'

const auth = useAuthStore()
const route = useRoute()

// SVG path definitions
const icons = {
  dashboard: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
  products: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
  warehouse: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
  movements: 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
  orders: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
  fulfillments: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
  suppliers: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0',
  alerts: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
  lowstock: 'M13 17h8m0 0V9m0 8l-8-8-4 4-6-6',
  value: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
  chart: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
}

const NavItem = {
  props: ['item'],
  setup(props) {
    const route = useRoute()
    const isActive = computed(() => route.path === props.item.to || route.path.startsWith(props.item.to + '/'))
    return () => h(RouterLink, {
      to: props.item.to,
      class: ['flex items-center gap-2.5 px-2.5 py-1.5 rounded-md text-xs transition-colors',
        isActive.value ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'].join(' ')
    }, () => [
      h('svg', { class: 'w-4 h-4 flex-shrink-0', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor', strokeWidth: 2 }, [
        h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: icons[props.item.icon] || icons.products })
      ]),
      h('span', { class: 'truncate' }, props.item.label)
    ])
  }
}

const allItems = [
  { to: '/ims/dashboard', label: 'Dashboard', icon: 'dashboard', admin: false },
  { to: '/ims/products', label: 'Products', icon: 'products', admin: false },
  { to: '/ims/sources', label: 'Warehouses', icon: 'warehouse', admin: true },
  { to: '/ims/movements', label: 'Movements', icon: 'movements', admin: false },
  { to: '/ims/purchase-orders', label: 'Purchase Orders', icon: 'orders', admin: false },
  { to: '/ims/suppliers', label: 'Suppliers', icon: 'suppliers', admin: true },
  { to: '/ims/orders', label: 'Orders', icon: 'orders', admin: false },
  { to: '/ims/fulfillments', label: 'Fulfillments', icon: 'fulfillments', admin: false },
  { to: '/ims/alerts', label: 'Low Stock Alerts', icon: 'alerts', admin: false },
]

const reportItems = [
  { to: '/ims/reports/low-stock', label: 'Low Stock', icon: 'lowstock' },
  { to: '/ims/reports/stock-value', label: 'Stock Value', icon: 'value' },
  { to: '/ims/reports/movements', label: 'Movements', icon: 'chart' },
]

const visibleItems = computed(() => allItems.filter(i => !i.admin || auth.isAdmin))
</script>
