<template>
  <div class="space-y-3">
    <div class="filter-bar">
      <div class="filter-group flex-1 min-w-40">
        <label class="filter-label">Search</label>
        <input v-model="filters.search" @input="debounce" type="text" class="form-input" placeholder="SKU or product name…" />
      </div>
      <div class="filter-group">
        <label class="filter-label">Source</label>
        <select v-model="filters.source" @change="load(1)" class="form-select">
          <option value="">All sources</option>
          <option v-for="s in sources" :key="s.source_code" :value="s.source_code">{{ s.source_code }}</option>
        </select>
      </div>
      <div class="filter-group">
        <label class="filter-label">Stock status</label>
        <select v-model="filters.low_stock" @change="load(1)" class="form-select">
          <option value="">All</option>
          <option value="1">Low stock only</option>
        </select>
      </div>
      <div class="filter-group">
        <label class="filter-label">Type</label>
        <select v-model="filters.type_id" @change="load(1)" class="form-select">
          <option value="">All types</option>
          <option value="simple">Simple</option>
          <option value="configurable">Configurable</option>
          <option value="bundle">Bundle</option>
          <option value="grouped">Grouped</option>
          <option value="virtual">Virtual</option>
        </select>
      </div>
      <button @click="load(1)" class="btn-primary self-end">Search</button>
    </div>

    <div class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead>
            <tr class="border-b border-slate-200">
              <th class="table-header">SKU</th>
              <th class="table-header">Product Name</th>
              <th class="table-header">Type</th>
              <th class="table-header text-right">Price</th>
              <th class="table-header text-right">Total Qty</th>
              <th class="table-header">Status</th>
              <th class="table-header"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="7" class="table-cell text-center text-slate-400 py-8">Loading…</td>
            </tr>
            <template v-else>
              <tr v-for="p in data.data" :key="p.entity_id" class="table-row">
                <td class="table-cell font-mono text-xs text-slate-600">{{ p.sku }}</td>
                <td class="table-cell font-medium max-w-xs truncate">{{ p.name || '—' }}</td>
                <td class="table-cell"><span class="badge badge-gray">{{ p.type_id }}</span></td>
                <td class="table-cell text-right text-slate-600">${{ p.price ? Number(p.price).toFixed(2) : '—' }}</td>
                <td class="table-cell text-right font-medium">{{ p.stock_item?.qty ?? 0 }}</td>
                <td class="table-cell"><StockBadge :qty="+(p.stock_item?.qty ?? 0)" /></td>
                <td class="table-cell">
                  <RouterLink :to="`/ims/products/${p.entity_id}`" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Edit stock</RouterLink>
                </td>
              </tr>
              <tr v-if="!data.data?.length">
                <td colspan="7" class="table-cell text-center text-slate-400 py-8">No products found</td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
      <Pagination :current-page="data.current_page" :last-page="data.last_page" :total="data.total" :per-page="perPage"
        @change="load" @per-page="p => { perPage = p; load(1) }" />
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
import { RouterLink } from 'vue-router'
import StockBadge from '@/components/UI/StockBadge.vue'
import Pagination from '@/components/UI/Pagination.vue'
import api from '@/api/index.js'

const data = ref({ data: [], total: 0, current_page: 1, last_page: 1 })
const sources = ref([])
const loading = ref(true)
const perPage = ref(20)
const filters = ref({ search: '', source: '', low_stock: '', type_id: '' })
let timer = null

onMounted(async () => {
  try { const { data: s } = await api.get('/sources'); sources.value = s } catch {}
  load(1)
})

async function load(page = 1) {
  loading.value = true
  try {
    const params = { page, per_page: perPage.value }
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.low_stock) params.low_stock = filters.value.low_stock
    const { data: res } = await api.get('/products', { params })
    data.value = res
  } finally { loading.value = false }
}

function debounce() { clearTimeout(timer); timer = setTimeout(() => load(1), 350) }
</script>
