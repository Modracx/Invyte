<template>
  <div class="space-y-3">
    <div class="filter-bar">
      <div class="filter-group">
        <label class="filter-label">Threshold</label>
        <input v-model.number="threshold" type="number" min="0" class="form-input w-20" />
      </div>
      <div class="filter-group">
        <label class="filter-label">Source</label>
        <select v-model="sourceFilter" class="form-select">
          <option value="">All sources</option>
          <option v-for="s in sources" :key="s.source_code" :value="s.source_code">{{ s.source_code }}</option>
        </select>
      </div>
      <button @click="load" class="btn-primary self-end">Load</button>
      <span v-if="!loading" class="self-end text-xs text-slate-500">{{ filtered.length }} items</span>
    </div>

    <div class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead>
            <tr class="border-b border-slate-200">
              <th class="table-header">SKU</th>
              <th class="table-header">Product Name</th>
              <th class="table-header">Source</th>
              <th class="table-header text-right">Quantity</th>
              <th class="table-header text-right">Threshold</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading"><td colspan="5" class="table-cell text-center text-slate-400 py-8">Loading…</td></tr>
            <template v-else>
              <tr v-for="item in filtered" :key="`${item.source_code}-${item.sku}`" class="table-row">
                <td class="table-cell font-mono text-xs">{{ item.sku }}</td>
                <td class="table-cell text-xs text-slate-600 max-w-xs truncate">{{ item.product_name }}</td>
                <td class="table-cell text-xs text-slate-500">{{ item.source_code }}</td>
                <td class="table-cell text-right"><StockBadge :qty="+item.quantity" :threshold="threshold" /></td>
                <td class="table-cell text-right text-xs text-slate-400">≤ {{ threshold }}</td>
              </tr>
              <tr v-if="!filtered.length">
                <td colspan="5" class="table-cell text-center text-slate-400 py-8">No items at or below threshold</td>
              </tr>
            </template>
          </tbody>
        </table>
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

import { ref, computed, onMounted } from 'vue'
import StockBadge from '@/components/UI/StockBadge.vue'
import api from '@/api/index.js'

const items = ref([])
const sources = ref([])
const loading = ref(true)
const threshold = ref(10)
const sourceFilter = ref('')

const filtered = computed(() =>
  sourceFilter.value ? items.value.filter(i => i.source_code === sourceFilter.value) : items.value
)

onMounted(async () => {
  try { const { data: s } = await api.get('/sources'); sources.value = s } catch {}
  load()
})

async function load() {
  loading.value = true
  try { const { data } = await api.get('/reports/low-stock', { params: { threshold: threshold.value } }); items.value = data }
  finally { loading.value = false }
}
</script>
