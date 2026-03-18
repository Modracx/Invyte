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
        <label class="filter-label">Type</label>
        <select v-model="f.type" @change="load" class="form-select">
          <option value="">All types</option>
          <option value="adjustment">Adjustment</option>
          <option value="receive">Receive</option>
          <option value="sync">Sync</option>
        </select>
      </div>
      <button @click="load" class="btn-secondary self-end">Refresh</button>
    </div>

    <div class="card p-4" v-if="!loading && chartData.length">
      <apexchart type="bar" height="200" :options="chartOpts" :series="chartSeries" />
    </div>

    <div class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead>
            <tr class="border-b border-slate-200">
              <th class="table-header">Date</th>
              <th class="table-header">Source</th>
              <th class="table-header">Type</th>
              <th class="table-header text-right">Movements</th>
              <th class="table-header text-right">Net Change</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading"><td colspan="5" class="table-cell text-center text-slate-400 py-8">Loading…</td></tr>
            <template v-else>
              <tr v-for="(row, i) in filtered" :key="i" class="table-row">
                <td class="table-cell text-xs text-slate-500">{{ row.date }}</td>
                <td class="table-cell font-mono text-xs text-slate-500">{{ row.source_code }}</td>
                <td class="table-cell"><span class="badge" :class="typeClass(row.type)">{{ row.type }}</span></td>
                <td class="table-cell text-right text-xs">{{ row.count }}</td>
                <td class="table-cell text-right text-xs font-semibold" :class="+row.total_qty_change >= 0 ? 'text-emerald-600' : 'text-red-600'">
                  {{ +row.total_qty_change >= 0 ? '+' : '' }}{{ Number(row.total_qty_change).toFixed(2) }}
                </td>
              </tr>
              <tr v-if="!filtered.length">
                <td colspan="5" class="table-cell text-center text-slate-400 py-8">No movement data</td>
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
import api from '@/api/index.js'

const data = ref([])
const sources = ref([])
const loading = ref(true)
const f = ref({ source_code: '', type: '' })

const filtered = computed(() => data.value.filter(r => {
  if (f.value.source_code && r.source_code !== f.value.source_code) return false
  if (f.value.type && r.type !== f.value.type) return false
  return true
}))

const chartData = computed(() => {
  const byDate = {}
  filtered.value.forEach(r => { byDate[r.date] = (byDate[r.date] || 0) + Number(r.total_qty_change) })
  return Object.entries(byDate).sort(([a], [b]) => a.localeCompare(b))
})

const chartSeries = computed(() => [{ name: 'Net Stock Change', data: chartData.value.map(([, v]) => +v.toFixed(2)) }])
const chartOpts = computed(() => ({
  chart: { toolbar: { show: false }, fontFamily: 'inherit' },
  xaxis: { categories: chartData.value.map(([d]) => d), labels: { style: { fontSize: '10px' } } },
  yaxis: { labels: { style: { fontSize: '10px' } } },
  colors: ['#3b82f6'],
  plotOptions: { bar: { borderRadius: 3, columnWidth: '60%' } },
  grid: { borderColor: '#f1f5f9' },
  tooltip: { theme: 'light' },
}))

function typeClass(t) { return { adjustment: 'badge-blue', receive: 'badge-green', sync: 'badge-gray' }[t] || 'badge-gray' }

onMounted(async () => {
  try { const { data: s } = await api.get('/sources'); sources.value = s } catch {}
  load()
})

async function load() {
  loading.value = true
  try { const { data: res } = await api.get('/reports/movements'); data.value = res }
  finally { loading.value = false }
}
</script>
