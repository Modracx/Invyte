<template>
  <div class="space-y-3">
    <div class="filter-bar">
      <div class="filter-group">
        <label class="filter-label">Status</label>
        <select v-model="f.status" @change="load(1)" class="form-select">
          <option value="">All statuses</option>
          <option value="draft">Draft</option>
          <option value="pending">Pending</option>
          <option value="partial">Partial</option>
          <option value="received">Received</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
      <div class="filter-group">
        <label class="filter-label">Source</label>
        <select v-model="f.source_code" @change="load(1)" class="form-select">
          <option value="">All sources</option>
          <option v-for="s in sources" :key="s.source_code" :value="s.source_code">{{ s.source_code }}</option>
        </select>
      </div>
      <div class="filter-group">
        <label class="filter-label">From date</label>
        <input v-model="f.from" type="date" class="form-input" />
      </div>
      <div class="filter-group">
        <label class="filter-label">To date</label>
        <input v-model="f.to" type="date" class="form-input" />
      </div>
      <button @click="load(1)" class="btn-primary self-end">Search</button>
      <RouterLink to="/ims/purchase-orders/new" class="btn-success self-end">+ New PO</RouterLink>
    </div>

    <div class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead>
            <tr class="border-b border-slate-200">
              <th class="table-header">PO #</th>
              <th class="table-header">Supplier</th>
              <th class="table-header">Source</th>
              <th class="table-header">Status</th>
              <th class="table-header">Items</th>
              <th class="table-header">Expected</th>
              <th class="table-header">Created by</th>
              <th class="table-header">Date</th>
              <th class="table-header"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading"><td colspan="9" class="table-cell text-center text-slate-400 py-8">Loading…</td></tr>
            <template v-else>
              <tr v-for="po in data.data" :key="po.id" class="table-row">
                <td class="table-cell font-mono text-xs font-semibold">PO-{{ po.id }}</td>
                <td class="table-cell text-xs">{{ po.supplier?.name || '—' }}</td>
                <td class="table-cell font-mono text-xs text-slate-500">{{ po.source_code }}</td>
                <td class="table-cell"><StatusBadge :status="po.status" /></td>
                <td class="table-cell text-xs text-slate-500">{{ po.items_count ?? '—' }}</td>
                <td class="table-cell text-xs text-slate-500">{{ po.expected_date || '—' }}</td>
                <td class="table-cell text-xs text-slate-500">{{ po.created_by?.name || '—' }}</td>
                <td class="table-cell text-xs text-slate-400">{{ fmtDate(po.created_at) }}</td>
                <td class="table-cell">
                  <RouterLink :to="`/ims/purchase-orders/${po.id}`" class="text-blue-600 hover:text-blue-800 text-xs font-medium">View</RouterLink>
                </td>
              </tr>
              <tr v-if="!data.data?.length">
                <td colspan="9" class="table-cell text-center text-slate-400 py-8">No purchase orders found</td>
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
import { RouterLink } from 'vue-router'
import Pagination from '@/components/UI/Pagination.vue'
import api from '@/api/index.js'

const data = ref({ data: [], total: 0, current_page: 1, last_page: 1 })
const sources = ref([])
const loading = ref(true)
const perPage = ref(20)
const f = ref({ status: '', source_code: '', from: '', to: '' })

const statusMap = { draft: 'badge-gray', pending: 'badge-yellow', partial: 'badge-blue', received: 'badge-green', cancelled: 'badge-red' }
const StatusBadge = {
  props: ['status'],
  render() { return h('span', { class: ['badge', statusMap[this.status] || 'badge-gray'].join(' ') }, this.status) }
}

onMounted(async () => {
  try { const { data: s } = await api.get('/sources'); sources.value = s } catch {}
  load(1)
})

async function load(page = 1) {
  loading.value = true
  try {
    const params = { page, per_page: perPage.value, ...Object.fromEntries(Object.entries(f.value).filter(([, v]) => v)) }
    const { data: res } = await api.get('/purchase-orders', { params })
    data.value = res
  } finally { loading.value = false }
}

function fmtDate(s) { if (!s) return '—'; return new Date(s).toLocaleDateString() }
</script>
