<template>
  <div class="space-y-3">
    <div class="filter-bar">
      <div class="filter-group">
        <label class="filter-label">Source</label>
        <select v-model="sourceFilter" class="form-select">
          <option value="">All sources</option>
          <option v-for="s in data" :key="s.source_code" :value="s.source_code">{{ s.source_code }}</option>
        </select>
      </div>
      <button @click="load" class="btn-secondary self-end">Refresh</button>
    </div>

    <div class="grid grid-cols-2 gap-3" v-if="!loading">
      <div class="card p-4">
        <p class="text-xs text-slate-500">Total Units</p>
        <p class="text-2xl font-bold text-slate-900 mt-1">{{ Number(totalQty).toLocaleString() }}</p>
      </div>
      <div class="card p-4">
        <p class="text-xs text-slate-500">Estimated Total Value</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1">${{ Number(totalValue).toLocaleString('en', { minimumFractionDigits: 2 }) }}</p>
      </div>
    </div>

    <div class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead>
            <tr class="border-b border-slate-200">
              <th class="table-header">Source / Warehouse</th>
              <th class="table-header text-right">Total Units</th>
              <th class="table-header text-right">Estimated Value</th>
              <th class="table-header text-right">% of Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading"><td colspan="4" class="table-cell text-center text-slate-400 py-8">Loading…</td></tr>
            <template v-else>
              <tr v-for="row in filtered" :key="row.source_code" class="table-row">
                <td class="table-cell font-mono text-xs font-semibold">{{ row.source_code }}</td>
                <td class="table-cell text-right text-sm font-medium">{{ Number(row.total_qty).toLocaleString() }}</td>
                <td class="table-cell text-right text-sm font-semibold text-emerald-600">${{ Number(row.total_value).toFixed(2) }}</td>
                <td class="table-cell text-right text-xs text-slate-500">
                  <div class="flex items-center justify-end gap-2">
                    <div class="w-16 bg-slate-100 rounded-full h-1.5">
                      <div class="bg-blue-500 h-1.5 rounded-full" :style="`width:${pct(row.total_value)}%`"></div>
                    </div>
                    {{ pct(row.total_value) }}%
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
          <tfoot v-if="!loading && filtered.length">
            <tr class="border-t border-slate-300 bg-slate-50 font-semibold">
              <td class="table-cell text-xs">Total</td>
              <td class="table-cell text-right text-sm">{{ Number(totalQty).toLocaleString() }}</td>
              <td class="table-cell text-right text-sm text-emerald-700">${{ Number(totalValue).toFixed(2) }}</td>
              <td></td>
            </tr>
          </tfoot>
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
import api from '@/api/index.js'

const data = ref([])
const loading = ref(true)
const sourceFilter = ref('')

const filtered = computed(() => sourceFilter.value ? data.value.filter(r => r.source_code === sourceFilter.value) : data.value)
const totalQty = computed(() => filtered.value.reduce((s, r) => s + Number(r.total_qty), 0))
const totalValue = computed(() => filtered.value.reduce((s, r) => s + Number(r.total_value), 0))

function pct(val) {
  if (!totalValue.value) return 0
  return Math.round((Number(val) / totalValue.value) * 100)
}

onMounted(load)

async function load() {
  loading.value = true
  try { const { data: res } = await api.get('/reports/stock-value'); data.value = res }
  finally { loading.value = false }
}
</script>
