<template>
  <div class="space-y-3">
    <div>
      <h1 class="text-base font-semibold text-slate-800">Fulfillments</h1>
      <p class="text-xs text-slate-500 mt-0.5">History of all order fulfillments</p>
    </div>

    <div class="filter-bar">
      <div class="filter-group">
        <label class="filter-label">Status</label>
        <select v-model="f.status" @change="load(1)" class="form-select">
          <option value="all">All statuses</option>
          <option value="draft">Draft</option>
          <option value="confirmed">Confirmed</option>
        </select>
      </div>
      <div class="filter-group">
        <label class="filter-label">Search</label>
        <input
          v-model="f.search"
          type="text"
          class="form-input"
          placeholder="Order # or increment ID"
          @keyup.enter="load(1)"
        />
      </div>
      <button @click="load(1)" class="btn-primary self-end">Search</button>
    </div>

    <div class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead>
            <tr class="border-b border-slate-200">
              <th class="table-header">#</th>
              <th class="table-header">Order #</th>
              <th class="table-header">Created by</th>
              <th class="table-header">Date</th>
              <th class="table-header">Status</th>
              <th class="table-header">Confirmed by</th>
              <th class="table-header"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="7" class="table-cell text-center text-slate-400 py-8">Loading…</td>
            </tr>
            <template v-else>
              <tr v-for="ful in data.data" :key="ful.id" class="table-row">
                <td class="table-cell font-mono text-xs text-slate-500">{{ ful.id }}</td>
                <td class="table-cell font-mono text-xs font-semibold text-slate-700">
                  #{{ ful.increment_id }}
                </td>
                <td class="table-cell text-xs text-slate-500">{{ ful.created_by?.name || '—' }}</td>
                <td class="table-cell text-xs text-slate-400">{{ fmtDate(ful.created_at) }}</td>
                <td class="table-cell">
                  <span class="badge" :class="ful.status === 'confirmed' ? 'badge-green' : 'badge-yellow'">
                    {{ ful.status }}
                  </span>
                </td>
                <td class="table-cell text-xs text-slate-500">
                  <template v-if="ful.status === 'confirmed'">
                    {{ ful.confirmed_by?.name || '—' }}
                    <span class="text-slate-400 ml-1">{{ fmtDate(ful.confirmed_at) }}</span>
                  </template>
                  <template v-else>—</template>
                </td>
                <td class="table-cell">
                  <div class="flex items-center gap-2">
                    <RouterLink
                      :to="`/ims/orders/${ful.order_id}/fulfill`"
                      class="text-xs text-blue-600 hover:text-blue-800 font-medium"
                    >
                      View
                    </RouterLink>
                    <button
                      v-if="ful.status === 'draft'"
                      @click="deleteFulfillment(ful)"
                      class="icon-btn-red"
                      title="Delete draft"
                    >
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!data.data?.length">
                <td colspan="7" class="table-cell text-center text-slate-400 py-8">No fulfillments found</td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
      <Pagination
        :current-page="data.current_page || 1"
        :last-page="data.last_page || 1"
        :total="data.total || 0"
        :per-page="perPage"
        @change="load"
        @per-page="p => { perPage = p; load(1) }"
      />
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
import Pagination from '@/components/UI/Pagination.vue'
import api from '@/api/index.js'

const data = ref({ data: [], total: 0, current_page: 1, last_page: 1 })
const loading = ref(true)
const perPage = ref(20)
const f = ref({ status: 'all', search: '' })

onMounted(() => load(1))

async function load(page = 1) {
  loading.value = true
  try {
    const params = {
      page,
      per_page: perPage.value,
      status: f.value.status !== 'all' ? f.value.status : undefined,
      search: f.value.search || undefined,
    }
    const { data: res } = await api.get('/fulfillments', { params })
    data.value = res
  } finally {
    loading.value = false
  }
}

function fmtDate(s) {
  if (!s) return '—'
  return new Date(s).toLocaleDateString()
}

async function deleteFulfillment(ful) {
  if (!confirm(`Delete draft fulfillment for order #${ful.increment_id}?`)) return
  try {
    await api.delete(`/fulfillments/${ful.id}`)
    load(data.value.current_page)
  } catch (err) {
    alert(err.response?.data?.message || 'Delete failed')
  }
}
</script>
