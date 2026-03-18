<template>
  <div class="space-y-4">
    <div v-if="loading" class="text-center py-16 text-slate-400 text-sm">Loading dashboard…</div>

    <template v-else>
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <StatCard label="Total Products" :value="stats.total_products" icon="products" />
        <StatCard label="Low Stock Items" :value="stats.low_stock_count" icon="alert" sub="Items near threshold" />
        <StatCard label="Active Warehouses" :value="stats.total_sources" icon="warehouse" />
        <StatCard label="Pending POs" :value="stats.pending_po_count" icon="cart" />
      </div>

      <div class="card">
        <div class="px-4 py-2.5 border-b border-slate-200 flex items-center justify-between">
          <h3 class="text-sm font-semibold text-slate-700">Recent Stock Movements</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-slate-200">
                <th class="table-header">SKU</th>
                <th class="table-header">Source</th>
                <th class="table-header">Type</th>
                <th class="table-header text-right">Change</th>
                <th class="table-header text-right">After</th>
                <th class="table-header">Reason</th>
                <th class="table-header">By</th>
                <th class="table-header">Time</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="m in stats.recent_movements" :key="m.id" class="table-row">
                <td class="table-cell font-mono text-xs">{{ m.sku }}</td>
                <td class="table-cell text-slate-500">{{ m.source_code }}</td>
                <td class="table-cell"><TypeBadge :type="m.type" /></td>
                <td class="table-cell text-right font-medium" :class="m.qty_change >= 0 ? 'text-emerald-600' : 'text-red-600'">
                  {{ m.qty_change >= 0 ? '+' : '' }}{{ m.qty_change }}
                </td>
                <td class="table-cell text-right">{{ m.qty_after }}</td>
                <td class="table-cell text-slate-500 max-w-32 truncate">{{ m.reason || '—' }}</td>
                <td class="table-cell text-slate-500">{{ m.user?.name || '—' }}</td>
                <td class="table-cell text-slate-400 text-xs whitespace-nowrap">{{ fmtDate(m.created_at) }}</td>
              </tr>
              <tr v-if="!stats.recent_movements?.length">
                <td colspan="8" class="table-cell text-center text-slate-400 py-6">No recent movements</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
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

import { ref, onMounted, h } from 'vue'
import StatCard from '@/components/UI/StatCard.vue'
import api from '@/api/index.js'

const loading = ref(true)
const stats = ref({ total_products: 0, low_stock_count: 0, total_sources: 0, pending_po_count: 0, recent_movements: [] })

const TypeBadge = {
  props: ['type'],
  render() {
    const cls = { receive: 'badge-green', adjustment: 'badge-blue', sync: 'badge-gray', transfer: 'badge-yellow' }
    return h('span', { class: ['badge', cls[this.type] || 'badge-gray'].join(' ') }, this.type)
  }
}

onMounted(async () => {
  try { const { data } = await api.get('/dashboard'); stats.value = data } finally { loading.value = false }
})

function fmtDate(s) {
  if (!s) return '—'
  const d = new Date(s)
  return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}
</script>
