<template>
  <div class="space-y-4 pb-24">
    <!-- Back / Loading -->
    <div v-if="pageLoading" class="text-slate-400 text-sm py-12 text-center">Loading order…</div>
    <template v-else-if="detail">

      <!-- Header -->
      <div class="flex items-start justify-between gap-4">
        <div>
          <RouterLink to="/ims/orders" class="text-xs text-slate-400 hover:text-slate-600 flex items-center gap-1 mb-1">
            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Orders
          </RouterLink>
          <h1 class="text-xl font-bold text-slate-800">#{{ detail.order.increment_id }}</h1>
          <p class="text-sm text-slate-500 mt-0.5">
            {{ detail.order.customer_firstname }} {{ detail.order.customer_lastname }}
            <span class="text-slate-400 ml-1">{{ detail.order.customer_email }}</span>
          </p>
        </div>
        <div class="text-right flex-shrink-0">
          <div class="text-xs text-slate-400">{{ fmtDate(detail.order.created_at) }}</div>
          <div class="text-lg font-semibold text-slate-700 mt-0.5">${{ Number(detail.order.grand_total).toFixed(2) }}</div>
          <span class="badge badge-blue mt-1 inline-block">{{ detail.order.status }}</span>
        </div>
      </div>

      <!-- Confirmed banner -->
      <div v-if="detail.fulfillment?.status === 'confirmed'"
        class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 flex items-center gap-3">
        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
          <p class="text-sm font-medium text-green-800">This order has been fulfilled</p>
          <p class="text-xs text-green-600">
            Confirmed {{ fmtDate(detail.fulfillment.confirmed_at) }}
            <template v-if="detail.fulfillment.confirmedBy"> by {{ detail.fulfillment.confirmedBy.name }}</template>
          </p>
        </div>
      </div>

      <!-- Notes -->
      <div class="card">
        <div class="card-header">Notes</div>
        <div class="card-body">
          <textarea
            v-model="notes"
            rows="2"
            class="form-input w-full resize-none"
            placeholder="Optional notes for this fulfillment…"
            :disabled="detail.fulfillment?.status === 'confirmed'"
          ></textarea>
        </div>
      </div>

      <!-- Items -->
      <div v-for="item in detail.items" :key="item.item_id" class="card">
        <div class="card-header flex items-center justify-between">
          <div class="flex items-center gap-3 min-w-0">
            <div class="min-w-0">
              <p class="text-sm font-medium text-slate-700 truncate">{{ item.name }}</p>
              <span class="inline-block font-mono text-xs bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded mt-0.5">{{ item.sku }}</span>
            </div>
          </div>
          <div class="flex-shrink-0 text-right ml-4">
            <div class="text-xs text-slate-500 space-x-3">
              <span>Ordered: <strong>{{ item.qty_ordered }}</strong></span>
              <span>Shipped: <strong>{{ item.qty_shipped ?? 0 }}</strong></span>
              <span
                class="font-semibold"
                :class="item.qty_to_fulfill > 0 ? 'text-orange-600' : 'text-slate-400'"
              >
                To fulfill: <strong>{{ item.qty_to_fulfill }}</strong>
              </span>
            </div>
            <div class="text-xs text-slate-400 mt-0.5">${{ Number(item.price).toFixed(2) }} each</div>
          </div>
        </div>
        <div class="card-body space-y-3">

          <!-- Availability toggle -->
          <div>
            <button
              @click="toggleAvail(item.item_id)"
              class="text-xs text-blue-600 hover:text-blue-800 flex items-center gap-1"
            >
              <svg class="w-3 h-3 transition-transform" :class="showAvail[item.item_id] ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
              </svg>
              {{ showAvail[item.item_id] ? 'Hide' : 'Show' }} available stock
            </button>

            <div v-if="showAvail[item.item_id]" class="mt-2 overflow-x-auto">
              <table class="min-w-full text-xs">
                <thead>
                  <tr class="border-b border-slate-200">
                    <th class="table-header">Warehouse</th>
                    <th class="table-header text-right">Total Available</th>
                    <th class="table-header">Assigned Locations</th>
                    <th class="table-header text-right">Qty at Location</th>
                  </tr>
                </thead>
                <tbody>
                  <template v-for="src in availabilityForSku(item.sku)" :key="src.source_code">
                    <tr v-if="!src.assigned_locations?.length" class="border-b border-slate-100">
                      <td class="table-cell font-mono">{{ src.source_code }}</td>
                      <td class="table-cell text-right">
                        <span class="font-semibold" :class="src.qty_available <= 0 ? 'text-red-600' : src.qty_available < 10 ? 'text-yellow-600' : 'text-green-600'">
                          {{ src.qty_available }}
                        </span>
                      </td>
                      <td class="table-cell text-slate-400 italic" colspan="2">No locations assigned</td>
                    </tr>
                    <tr v-else v-for="(loc, li) in src.assigned_locations" :key="loc.location_id"
                      :class="['border-b border-slate-100', li === 0 ? '' : 'bg-slate-50/50']">
                      <td class="table-cell font-mono">{{ li === 0 ? src.source_code : '' }}</td>
                      <td class="table-cell text-right">
                        <span v-if="li === 0" class="font-semibold"
                          :class="src.qty_available <= 0 ? 'text-red-600' : src.qty_available < 10 ? 'text-yellow-600' : 'text-green-600'">
                          {{ src.qty_available }}
                        </span>
                      </td>
                      <td class="table-cell">
                        <span class="inline-flex items-center gap-1 text-purple-700">
                          <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                          </svg>
                          {{ loc.path || loc.name || '—' }}
                        </span>
                      </td>
                      <td class="table-cell text-right font-semibold text-slate-700">{{ loc.qty }}</td>
                    </tr>
                  </template>
                  <tr v-if="!availabilityForSku(item.sku).length">
                    <td colspan="4" class="table-cell text-slate-400">No stock data available</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Allocations -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <p class="text-xs font-medium text-slate-600">Allocations for this item</p>
              <div class="text-xs" :class="allocationStatusClass(item.item_id, item.qty_to_fulfill)">
                Allocated: {{ totalAllocated(item.item_id) }} / {{ item.qty_to_fulfill }} needed
              </div>
            </div>

            <div
              v-for="(alloc, idx) in (allocations[item.item_id] || [])"
              :key="idx"
              class="flex items-center gap-2 mb-2"
            >
              <!-- Source dropdown -->
              <div class="flex-1 min-w-0">
                <label class="filter-label">Warehouse</label>
                <select
                  v-model="alloc.source_code"
                  @change="onSourceChange(item.item_id, idx, item.sku)"
                  class="form-select w-full"
                  :disabled="detail.fulfillment?.status === 'confirmed'"
                >
                  <option value="">— Select warehouse —</option>
                  <option
                    v-for="src in availabilityForSku(item.sku)"
                    :key="src.source_code"
                    :value="src.source_code"
                  >
                    {{ src.source_code }} ({{ src.qty_available }} avail.)
                  </option>
                </select>
              </div>

              <!-- Location dropdown (only assigned locations for this sku+source) -->
              <div class="flex-1 min-w-0">
                <label class="filter-label">Location</label>
                <select
                  v-model="alloc.location_id"
                  class="form-select w-full"
                  :disabled="detail.fulfillment?.status === 'confirmed' || !alloc.source_code"
                >
                  <option :value="null">— No specific location —</option>
                  <option
                    v-for="loc in locationsForAlloc(item.sku, alloc.source_code)"
                    :key="loc.id"
                    :value="loc.id"
                  >
                    {{ loc.path || loc.name }}{{ loc.qty > 0 ? ` (qty: ${loc.qty})` : '' }}
                  </option>
                </select>
              </div>

              <!-- Qty input -->
              <div class="w-24 flex-shrink-0">
                <label class="filter-label">Qty</label>
                <input
                  v-model.number="alloc.qty"
                  type="number"
                  min="0.0001"
                  step="1"
                  :max="alloc.source_code ? qtyAvailableForSource(item.sku, alloc.source_code) : undefined"
                  class="form-input w-full"
                  :disabled="detail.fulfillment?.status === 'confirmed'"
                />
              </div>

              <!-- Remove -->
              <div class="flex-shrink-0 mt-4">
                <button
                  v-if="detail.fulfillment?.status !== 'confirmed'"
                  @click="removeAllocation(item.item_id, idx)"
                  class="icon-btn-red"
                  title="Remove"
                >
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </button>
              </div>
            </div>

            <button
              v-if="detail.fulfillment?.status !== 'confirmed'"
              @click="addAllocation(item.item_id, item.sku)"
              class="btn-ghost text-xs mt-1"
            >
              + Add Allocation
            </button>
          </div>

        </div>
      </div>

    </template>

    <!-- Sticky action bar -->
    <div
      v-if="detail && detail.fulfillment?.status !== 'confirmed'"
      class="fixed bottom-0 left-56 right-0 bg-white border-t border-slate-200 px-6 py-3 flex items-center justify-between z-10"
    >
      <div class="text-xs text-slate-500">
        <span :class="summaryClass">{{ fullyAllocatedCount }} of {{ detail.items.length }} items fully allocated</span>
        <span v-if="saving" class="ml-3 text-blue-500">Saving…</span>
        <span v-if="toast" class="ml-3" :class="toast.type === 'ok' ? 'text-green-600' : 'text-red-600'">{{ toast.msg }}</span>
      </div>
      <div class="flex items-center gap-2">
        <button @click="saveDraft" :disabled="saving" class="btn-secondary text-sm">
          Save Draft
        </button>
        <button
          @click="confirmFulfillment"
          :disabled="saving || !canConfirm"
          class="btn-primary text-sm"
          :title="!canConfirm ? 'All items with pending qty must have allocations' : ''"
        >
          Confirm Fulfillment
        </button>
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
import { useRoute, RouterLink } from 'vue-router'
import api from '@/api/index.js'

const route = useRoute()

const detail = ref(null)
const pageLoading = ref(true)
const saving = ref(false)
const notes = ref('')
const toast = ref(null)
const showAvail = ref({})

// { [order_item_id]: [{ source_code, location_id, qty }] }
const allocations = ref({})

onMounted(async () => {
  try {
    const res = await api.get(`/orders/${route.params.id}`)
    detail.value = res.data
    notes.value = detail.value.fulfillment?.notes || ''
    if (detail.value.fulfillment?.allocations) {
      for (const alloc of detail.value.fulfillment.allocations) {
        if (!allocations.value[alloc.order_item_id]) allocations.value[alloc.order_item_id] = []
        allocations.value[alloc.order_item_id].push({
          source_code: alloc.source_code,
          location_id: alloc.location_id ?? null,
          qty: alloc.qty_allocated,
        })
      }
    }
  } finally {
    pageLoading.value = false
  }
})

function fmtDate(s) {
  if (!s) return '—'
  return new Date(s).toLocaleDateString()
}

function toggleAvail(itemId) {
  showAvail.value[itemId] = !showAvail.value[itemId]
}

function availabilityForSku(sku) {
  return detail.value?.availability?.[sku] || []
}

// Returns only the assigned locations for a specific sku+source (for the dropdown)
function locationsForAlloc(sku, sourceCode) {
  if (!sku || !sourceCode) return []
  const src = availabilityForSku(sku).find(s => s.source_code === sourceCode)
  return src?.assigned_locations || []
}

function qtyAvailableForSource(sku, sourceCode) {
  return availabilityForSku(sku).find(s => s.source_code === sourceCode)?.qty_available ?? 0
}


function totalAllocated(itemId) {
  return (allocations.value[itemId] || []).reduce((s, a) => s + (parseFloat(a.qty) || 0), 0)
}

function allocationStatusClass(itemId, qtyNeeded) {
  const total = totalAllocated(itemId)
  if (qtyNeeded <= 0) return 'text-slate-400'
  if (total >= qtyNeeded) return 'text-green-600 font-medium'
  if (total > 0) return 'text-yellow-600 font-medium'
  return 'text-red-600 font-medium'
}

function addAllocation(itemId, sku) {
  if (!allocations.value[itemId]) allocations.value[itemId] = []
  const avail = availabilityForSku(sku)
  const firstSource = avail.find(s => s.qty_available > 0)
  const firstLoc = firstSource?.assigned_locations?.[0] ?? null
  allocations.value[itemId].push({
    source_code: firstSource?.source_code || '',
    location_id: firstLoc?.location_id ?? null,
    qty: 0,
  })
}

function removeAllocation(itemId, idx) {
  allocations.value[itemId].splice(idx, 1)
}

function onSourceChange(itemId, idx, sku) {
  const alloc = allocations.value[itemId]?.[idx]
  if (!alloc) return
  // Pre-fill with first assigned location for this sku+source
  const srcAvail = availabilityForSku(sku).find(s => s.source_code === alloc.source_code)
  const firstLoc = srcAvail?.assigned_locations?.[0] ?? null
  alloc.location_id = firstLoc?.location_id ?? null
}

function buildPayload() {
  const allAllocations = []
  for (const item of (detail.value?.items || [])) {
    for (const alloc of (allocations.value[item.item_id] || [])) {
      if (alloc.source_code && parseFloat(alloc.qty) > 0) {
        allAllocations.push({
          order_item_id: item.item_id,
          sku: item.sku,
          source_code: alloc.source_code,
          location_id: alloc.location_id || null,
          qty_allocated: parseFloat(alloc.qty),
        })
      }
    }
  }
  return {
    order_id: detail.value.order.entity_id,
    increment_id: detail.value.order.increment_id,
    notes: notes.value,
    allocations: allAllocations,
  }
}

// Summary computed
const fullyAllocatedCount = computed(() => {
  if (!detail.value) return 0
  return detail.value.items.filter(item => {
    if (item.qty_to_fulfill <= 0) return true
    return totalAllocated(item.item_id) >= item.qty_to_fulfill
  }).length
})

const summaryClass = computed(() => {
  if (!detail.value) return 'text-slate-500'
  return fullyAllocatedCount.value === detail.value.items.length ? 'text-green-600 font-medium' : 'text-yellow-600 font-medium'
})

const canConfirm = computed(() => {
  if (!detail.value) return false
  // Every item that needs fulfillment must have at least some allocation
  return detail.value.items.every(item => {
    if (item.qty_to_fulfill <= 0) return true
    return totalAllocated(item.item_id) >= item.qty_to_fulfill
  })
})

function showToast(msg, type = 'ok') {
  toast.value = { msg, type }
  setTimeout(() => { toast.value = null }, 3000)
}

async function saveDraft() {
  saving.value = true
  try {
    const payload = buildPayload()
    if (payload.allocations.length === 0) {
      showToast('Add at least one allocation before saving', 'err')
      return
    }
    if (detail.value.fulfillment?.id && detail.value.fulfillment.status === 'draft') {
      const res = await api.put(`/fulfillments/${detail.value.fulfillment.id}`, payload)
      detail.value.fulfillment = res.data
    } else {
      const res = await api.post('/fulfillments', payload)
      detail.value.fulfillment = res.data
    }
    showToast('Draft saved', 'ok')
  } catch (err) {
    const msg = err.response?.data?.message || 'Save failed'
    showToast(msg, 'err')
    throw err
  } finally {
    saving.value = false
  }
}

async function confirmFulfillment() {
  saving.value = true
  try {
    await saveDraft()
    await api.post(`/fulfillments/${detail.value.fulfillment.id}/confirm`)
    // Reload full detail
    const res = await api.get(`/orders/${route.params.id}`)
    detail.value = res.data
    notes.value = detail.value.fulfillment?.notes || ''
    showToast('Fulfillment confirmed — stock adjusted', 'ok')
  } catch (err) {
    const msg = err.response?.data?.message || 'Confirmation failed'
    showToast(msg, 'err')
  } finally {
    saving.value = false
  }
}
</script>
