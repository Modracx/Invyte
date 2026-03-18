<template>
  <div class="space-y-3">
    <div class="filter-bar">
      <div class="filter-group">
        <label class="filter-label">Source</label>
        <select v-model="f.source_code" @change="load" class="form-select">
          <option value="">All sources</option>
          <option v-for="s in sources" :key="s.source_code" :value="s.source_code">{{ s.source_code }}</option>
        </select>
      </div>
      <div class="filter-group">
        <label class="filter-label">SKU</label>
        <input v-model="f.sku" @keyup.enter="load" type="text" class="form-input w-32" placeholder="Filter by SKU…" />
      </div>
      <button @click="load" class="btn-primary self-end">Search</button>
    </div>

    <div class="card overflow-hidden">
      <div class="px-4 py-2.5 border-b border-slate-200 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-700">Active Low Stock Alerts</h3>
        <span v-if="!loading" class="badge badge-red">{{ data.total }} unresolved</span>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead>
            <tr class="border-b border-slate-200">
              <th class="table-header">SKU</th>
              <th class="table-header">Source</th>
              <th class="table-header text-right">Threshold</th>
              <th class="table-header text-right">Current Qty</th>
              <th class="table-header">Status</th>
              <th class="table-header">Created</th>
              <th class="table-header"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading"><td colspan="7" class="table-cell text-center text-slate-400 py-8">Loading…</td></tr>
            <template v-else>
              <tr v-for="a in data.data" :key="a.id" class="table-row">
                <td class="table-cell font-mono text-xs font-medium">{{ a.sku }}</td>
                <td class="table-cell text-xs text-slate-500">{{ a.source_code }}</td>
                <td class="table-cell text-right text-xs text-slate-500">{{ a.threshold }}</td>
                <td class="table-cell text-right"><StockBadge :qty="+a.current_qty" :threshold="+a.threshold" /></td>
                <td class="table-cell">
                  <span class="badge" :class="+a.current_qty === 0 ? 'badge-red' : 'badge-yellow'">
                    {{ +a.current_qty === 0 ? 'Out of stock' : 'Low stock' }}
                  </span>
                </td>
                <td class="table-cell text-xs text-slate-400">{{ new Date(a.created_at).toLocaleDateString() }}</td>
                <td class="table-cell">
                  <button @click="resolve(a.id)" class="text-emerald-600 hover:text-emerald-800 text-xs font-medium">Resolve</button>
                </td>
              </tr>
              <tr v-if="!data.data?.length">
                <td colspan="7" class="table-cell text-center text-slate-400 py-8">No active alerts</td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
      <Pagination :current-page="data.current_page||1" :last-page="data.last_page||1" :total="data.total||0" :per-page="perPage"
        @change="load" @per-page="p => { perPage = p; load() }" />
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

import { ref, onMounted } from 'vue'
import StockBadge from '@/components/UI/StockBadge.vue'
import Pagination from '@/components/UI/Pagination.vue'
import api from '@/api/index.js'

const data = ref({ data: [], total: 0, current_page: 1, last_page: 1 })
const sources = ref([])
const loading = ref(true)
const perPage = ref(50)
const f = ref({ source_code: '', sku: '' })

onMounted(async () => {
  try { const { data: s } = await api.get('/sources'); sources.value = s } catch {}
  load()
})

async function load(page = 1) {
  loading.value = true
  try {
    const params = { page, per_page: perPage.value, ...Object.fromEntries(Object.entries(f.value).filter(([, v]) => v)) }
    const { data: res } = await api.get('/alerts', { params })
    data.value = res
  } finally { loading.value = false }
}

async function resolve(id) {
  try { await api.put(`/alerts/${id}/resolve`); await load() } catch (e) { alert(e.response?.data?.message || 'Failed') }
}
</script>
