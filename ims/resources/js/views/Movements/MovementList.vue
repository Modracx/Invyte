<template>
  <div class="space-y-3">
    <div class="filter-bar">
      <div class="filter-group">
        <label class="filter-label">SKU</label>
        <input v-model="f.sku" @keyup.enter="load(1)" type="text" class="form-input w-32" placeholder="e.g. 24-MB01" />
      </div>
      <div class="filter-group">
        <label class="filter-label">Source</label>
        <select v-model="f.source_code" class="form-select">
          <option value="">All</option>
          <option v-for="s in sources" :key="s.source_code" :value="s.source_code">{{ s.source_code }}</option>
        </select>
      </div>
      <div class="filter-group">
        <label class="filter-label">Type</label>
        <select v-model="f.type" class="form-select">
          <option value="">All</option>
          <option value="adjustment">Adjustment</option>
          <option value="receive">Receive</option>
          <option value="sync">Sync</option>
          <option value="transfer">Transfer</option>
        </select>
      </div>
      <div class="filter-group">
        <label class="filter-label">From</label>
        <input v-model="f.from" type="date" class="form-input" />
      </div>
      <div class="filter-group">
        <label class="filter-label">To</label>
        <input v-model="f.to" type="date" class="form-input" />
      </div>
      <button @click="load(1)" class="btn-primary self-end">Search</button>
      <button @click="reset" class="btn-secondary self-end">Reset</button>
    </div>

    <div class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead>
            <tr class="border-b border-slate-200">
              <th class="table-header whitespace-nowrap">Date / Time</th>
              <th class="table-header">SKU</th>
              <th class="table-header">Source</th>
              <th class="table-header">Type</th>
              <th class="table-header text-right">Before</th>
              <th class="table-header text-right">Change</th>
              <th class="table-header text-right">After</th>
              <th class="table-header">Reason</th>
              <th class="table-header">User</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading"><td colspan="9" class="table-cell text-center text-slate-400 py-8">Loading…</td></tr>
            <template v-else>
              <tr v-for="m in data.data" :key="m.id" class="table-row">
                <td class="table-cell text-xs text-slate-400 whitespace-nowrap">{{ fmtDate(m.created_at) }}</td>
                <td class="table-cell font-mono text-xs">{{ m.sku }}</td>
                <td class="table-cell text-xs text-slate-500">{{ m.source_code }}</td>
                <td class="table-cell"><TypeBadge :type="m.type" /></td>
                <td class="table-cell text-right text-xs text-slate-500">{{ m.qty_before }}</td>
                <td class="table-cell text-right text-xs font-semibold" :class="m.qty_change >= 0 ? 'text-emerald-600' : 'text-red-600'">
                  {{ m.qty_change >= 0 ? '+' : '' }}{{ m.qty_change }}
                </td>
                <td class="table-cell text-right text-xs font-medium">{{ m.qty_after }}</td>
                <td class="table-cell text-xs text-slate-500 max-w-40 truncate" :title="m.reason">{{ m.reason || '—' }}</td>
                <td class="table-cell text-xs text-slate-500">{{ m.user?.name || '—' }}</td>
              </tr>
              <tr v-if="!data.data?.length">
                <td colspan="9" class="table-cell text-center text-slate-400 py-8">No movements found</td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
      <Pagination :current-page="data.current_page||1" :last-page="data.last_page||1" :total="data.total||0" :per-page="perPage"
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

import { ref, onMounted, h } from 'vue'
import Pagination from '@/components/UI/Pagination.vue'
import api from '@/api/index.js'

const data = ref({ data: [], total: 0, current_page: 1, last_page: 1 })
const sources = ref([])
const loading = ref(true)
const perPage = ref(20)
const f = ref({ sku: '', source_code: '', type: '', from: '', to: '' })

const TypeBadge = {
  props: ['type'],
  render() {
    const cls = { receive: 'badge-green', adjustment: 'badge-blue', sync: 'badge-gray', transfer: 'badge-yellow' }
    return h('span', { class: ['badge', cls[this.type] || 'badge-gray'].join(' ') }, this.type)
  }
}

onMounted(async () => {
  try { const { data: s } = await api.get('/sources'); sources.value = s } catch {}
  load(1)
})

async function load(page = 1) {
  loading.value = true
  try {
    const params = { page, per_page: perPage.value, ...Object.fromEntries(Object.entries(f.value).filter(([, v]) => v)) }
    const { data: res } = await api.get('/movements', { params })
    data.value = res
  } finally { loading.value = false }
}

function reset() { f.value = { sku: '', source_code: '', type: '', from: '', to: '' }; load(1) }
function fmtDate(s) { if (!s) return '—'; const d = new Date(s); return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }
</script>
