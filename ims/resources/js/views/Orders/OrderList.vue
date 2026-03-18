<template>
  <div class="space-y-3">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-base font-semibold text-slate-800">Orders</h1>
        <p class="text-xs text-slate-500 mt-0.5">Fulfill Magento orders from warehouse stock</p>
      </div>
    </div>

    <div class="filter-bar">
      <div class="filter-group">
        <label class="filter-label">Status</label>
        <select v-model="f.status" @change="load(1)" class="form-select">
          <option value="processing">Processing</option>
          <option value="pending">Pending</option>
          <option value="complete">Complete</option>
          <option value="all">All statuses</option>
        </select>
      </div>
      <div class="filter-group">
        <label class="filter-label">Search</label>
        <input
          v-model="f.search"
          type="text"
          class="form-input"
          placeholder="Order # or customer email"
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
              <th class="table-header">Order #</th>
              <th class="table-header">Customer</th>
              <th class="table-header">Date</th>
              <th class="table-header">Total</th>
              <th class="table-header">Items</th>
              <th class="table-header">Fulfillment</th>
              <th class="table-header"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="7" class="table-cell text-center text-slate-400 py-8">Loading…</td>
            </tr>
            <template v-else>
              <tr v-for="order in data.data" :key="order.entity_id" class="table-row">
                <td class="table-cell font-mono text-xs font-semibold text-slate-700">
                  #{{ order.increment_id }}
                </td>
                <td class="table-cell">
                  <div class="text-xs font-medium text-slate-700">
                    {{ order.customer_firstname }} {{ order.customer_lastname }}
                  </div>
                  <div class="text-xs text-slate-400">{{ order.customer_email }}</div>
                </td>
                <td class="table-cell text-xs text-slate-500">{{ fmtDate(order.created_at) }}</td>
                <td class="table-cell text-xs font-medium text-slate-700">
                  ${{ Number(order.grand_total).toFixed(2) }}
                </td>
                <td class="table-cell text-xs text-slate-500">
                  {{ order.total_qty_ordered != null ? Math.round(order.total_qty_ordered) : '—' }}
                </td>
                <td class="table-cell">
                  <span
                    class="badge"
                    :class="fulfillmentBadgeClass(order.fulfillment_status)"
                  >
                    {{ fulfillmentLabel(order.fulfillment_status) }}
                  </span>
                </td>
                <td class="table-cell">
                  <!-- Fulfill / View: only for actionable statuses -->
                  <RouterLink v-if="canFulfill(order.status)"
                    :to="`/ims/orders/${order.entity_id}/fulfill`"
                    class="btn-primary text-xs"
                  >
                    {{ order.fulfillment_status === 'confirmed' ? 'View' : 'Fulfill' }}
                  </RouterLink>
                  <!-- View-only for confirmed fulfillments on non-actionable orders -->
                  <RouterLink v-else-if="order.fulfillment_status === 'confirmed'"
                    :to="`/ims/orders/${order.entity_id}/fulfill`"
                    class="btn-secondary text-xs"
                  >
                    View
                  </RouterLink>
                  <span v-else class="text-xs text-slate-300">—</span>
                </td>
              </tr>
              <tr v-if="!data.data?.length">
                <td colspan="7" class="table-cell text-center text-slate-400 py-8">No orders found</td>
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
    const { data: res } = await api.get('/orders', { params })
    data.value = res
  } finally {
    loading.value = false
  }
}

function fmtDate(s) {
  if (!s) return '—'
  return new Date(s).toLocaleDateString()
}

function fulfillmentBadgeClass(status) {
  if (status === 'confirmed') return 'badge-green'
  if (status === 'draft') return 'badge-yellow'
  return 'badge-gray'
}

function fulfillmentLabel(status) {
  if (status === 'confirmed') return 'Fulfilled'
  if (status === 'draft') return 'Draft'
  return 'Not started'
}

function canFulfill(orderStatus) {
  return orderStatus === 'processing' || orderStatus === 'pending'
}
</script>
