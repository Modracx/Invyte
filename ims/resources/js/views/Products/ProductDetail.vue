<template>
  <div class="max-w-5xl space-y-4">
    <div v-if="loading" class="flex items-center justify-center py-20 text-slate-400">
      <svg class="w-8 h-8 animate-spin mr-3" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
      </svg>
      <span class="text-sm">Loading product…</span>
    </div>

    <template v-else-if="detail">
      <!-- Product header -->
      <div class="card overflow-hidden">
        <div class="h-1.5 bg-gradient-to-r from-blue-500 to-indigo-500"></div>
        <div class="p-5 flex items-start justify-between gap-4">
          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2 flex-wrap">
              <h1 class="text-lg font-bold text-slate-900 leading-tight">
                {{ detail.product?.name || detail.product?.sku }}
              </h1>
              <span class="badge badge-gray">{{ detail.product?.type_id }}</span>
            </div>
            <div class="flex items-center gap-4 mt-2 flex-wrap">
              <div class="flex items-center gap-1.5 text-xs text-slate-500">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                SKU: <code class="font-mono font-semibold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded">{{ detail.product?.sku }}</code>
              </div>
              <div v-if="detail.product?.price" class="flex items-center gap-1.5 text-xs text-slate-500">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Price: <span class="font-semibold text-slate-700">${{ Number(detail.product.price).toFixed(2) }}</span>
              </div>
            </div>
          </div>
          <RouterLink to="/ims/products" class="btn-secondary flex-shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back
          </RouterLink>
        </div>
      </div>

      <!-- Stock + Location table -->
      <div class="card overflow-hidden">
        <div class="card-header">
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <span class="font-semibold text-slate-700 text-sm">Stock by Warehouse</span>
            <span class="badge badge-blue">{{ detail.source_items?.length || 0 }} sources</span>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-slate-200">
                <th class="table-header">Warehouse</th>
                <th class="table-header text-right">Quantity</th>
                <th class="table-header">Status</th>
                <th class="table-header">Assigned Locations</th>
                <th class="table-header text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <template v-for="item in detail.source_items" :key="item.source_item_id">
                <tr :class="['table-row', editing === item.source_code ? 'bg-blue-50/50' : '']">
                  <td class="table-cell">
                    <code class="font-mono text-xs font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded">{{ item.source_code }}</code>
                  </td>
                  <td class="table-cell text-right">
                    <template v-if="editing === item.source_code">
                      <input v-model.number="editQty" type="number" min="0" class="form-input w-24 text-right" @keyup.enter="saveQty(item)" />
                    </template>
                    <span v-else class="font-bold text-slate-900">{{ Number(item.quantity).toLocaleString() }}</span>
                  </td>
                  <td class="table-cell"><StockBadge :qty="+item.quantity" /></td>

                  <!-- Location badges -->
                  <td class="table-cell">
                    <div class="flex flex-wrap gap-1">
                      <span v-for="loc in locationsFor(item.source_code)" :key="loc.id"
                        class="inline-flex items-center gap-1 text-xs text-purple-700 bg-purple-50 border border-purple-200 px-2 py-0.5 rounded-full">
                        <svg class="w-2.5 h-2.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ loc.path }}
                        <span v-if="loc.qty > 0" class="font-semibold text-purple-500">({{ loc.qty }})</span>
                      </span>
                      <span v-if="!locationsFor(item.source_code).length" class="text-slate-300 text-xs">Not assigned</span>
                    </div>
                  </td>

                  <!-- Actions -->
                  <td class="table-cell">
                    <div class="flex items-center justify-end gap-1 flex-wrap">
                      <template v-if="editing === item.source_code">
                        <!-- Location select (only if locations are assigned to this source) -->
                        <select v-if="locationsFor(item.source_code).length"
                          v-model="editLocationId"
                          class="form-select text-xs w-40"
                          title="Apply delta to this location">
                          <option :value="null">No location</option>
                          <option v-for="loc in locationsFor(item.source_code)" :key="loc.location_id" :value="loc.location_id">
                            {{ loc.path || loc.name }} ({{ loc.qty }})
                          </option>
                        </select>
                        <input v-model="editReason" type="text" placeholder="Reason…"
                          class="form-input w-32 text-xs" @keyup.enter="saveQty(item)" />
                        <button @click="saveQty(item)" :disabled="saving" class="btn-success text-xs">Save</button>
                        <button @click="editing = null" class="btn-secondary text-xs">Cancel</button>
                      </template>
                      <template v-else>
                        <button @click="startEdit(item)" class="icon-btn-blue" title="Edit stock quantity">
                          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                          </svg>
                        </button>
                        <button @click="openLocationModal(item)" class="icon-btn-purple" title="Assign locations">
                          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                          </svg>
                        </button>
                      </template>
                    </div>
                  </td>
                </tr>
              </template>
              <tr v-if="!detail.source_items?.length">
                <td colspan="5" class="table-cell text-center py-10 text-slate-400">No warehouse stock found</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Toast -->
      <Transition enter-from-class="opacity-0 translate-y-2" leave-to-class="opacity-0 translate-y-2"
        enter-active-class="transition duration-200" leave-active-class="transition duration-200">
        <div v-if="msg.text" class="fixed bottom-6 right-6 z-50 flex items-center gap-2.5 px-4 py-3 rounded-lg shadow-lg text-sm font-medium"
          :class="msg.type === 'ok' ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white'">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path v-if="msg.type === 'ok'" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            <path v-else stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ msg.text }}
        </div>
      </Transition>
    </template>

    <!-- ── Location Modal ──────────────────────────────────────── -->
    <div v-if="locModal.open" class="modal-overlay" @click.self="locModal.open = false">
      <div class="modal-box w-full max-w-lg max-h-[90vh] flex flex-col">

        <!-- Header -->
        <div class="modal-header flex-shrink-0">
          <div>
            <h3 class="font-semibold text-slate-900">Assign Locations</h3>
            <p class="text-xs text-slate-500 mt-0.5">
              Warehouse: <code class="font-mono text-slate-700 bg-slate-100 px-1 rounded">{{ locModal.sourceCode }}</code>
            </p>
          </div>
          <button @click="locModal.open = false" class="icon-btn-gray">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- No locations -->
        <div v-if="!flatLocations.length" class="p-8 text-center text-slate-400">
          <svg class="w-10 h-10 mx-auto mb-2 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          <p class="text-sm font-medium text-slate-600">No locations set up</p>
          <RouterLink :to="`/ims/sources/${locModal.sourceCode}/locations`" class="btn-primary inline-flex mt-3 text-xs">
            Set up locations →
          </RouterLink>
        </div>

        <template v-else>
          <!-- Qty summary bar -->
          <div class="flex-shrink-0 px-4 py-2.5 bg-slate-50 border-b border-slate-200">
            <div class="flex items-center justify-between text-xs">
              <span class="text-slate-500">
                Available stock:
                <span class="font-bold text-slate-800 ml-1">{{ modalAvailable }}</span>
              </span>
              <span :class="['font-semibold', qtyOverLimit ? 'text-red-600' : 'text-emerald-600']">
                Assigned: {{ totalAssigned }} / {{ modalAvailable }}
                <span v-if="qtyOverLimit" class="ml-1 text-red-500">⚠ exceeds available</span>
              </span>
            </div>
            <div class="mt-1.5 h-1.5 bg-slate-200 rounded-full overflow-hidden">
              <div class="h-full rounded-full transition-all"
                :class="qtyOverLimit ? 'bg-red-400' : 'bg-emerald-400'"
                :style="{ width: Math.min(100, modalAvailable > 0 ? (totalAssigned / modalAvailable) * 100 : 0) + '%' }">
              </div>
            </div>
          </div>

          <!-- Search + select-all bar -->
          <div class="flex-shrink-0 px-4 pt-3 pb-2 border-b border-slate-100 space-y-2">
            <div class="relative">
              <svg class="absolute left-2.5 top-2 w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
              <input v-model="locSearch" type="text" class="form-input pl-8 text-xs" placeholder="Search by name or code…" />
            </div>
            <div class="flex items-center justify-between text-xs">
              <label class="flex items-center gap-2 cursor-pointer text-slate-600 hover:text-slate-800 select-none">
                <input type="checkbox" :checked="allVisible > 0 && allVisible === selectedIds.size"
                  :indeterminate="selectedIds.size > 0 && selectedIds.size < allVisible"
                  @change="toggleAll" class="rounded text-purple-600" />
                <span>Select all visible ({{ filteredLocations.length }})</span>
              </label>
              <button @click="clearAll" class="text-slate-400 hover:text-slate-600 text-xs">Clear all</button>
            </div>
          </div>

          <!-- Location tree -->
          <div class="overflow-y-auto flex-1 p-3 space-y-1.5">
            <template v-if="filteredLocations.length">
              <template v-for="row in rowNodes" :key="row.id">
                <!-- Row -->
                <div v-show="isVisible(row)"
                  :class="['rounded border transition-colors', selectedIds.has(row.id) ? 'bg-blue-50 border-blue-300' : 'bg-blue-50/50 border-blue-100']">
                  <div class="flex items-center gap-2 px-2 py-1.5">
                    <label class="flex items-center gap-2 flex-1 min-w-0 cursor-pointer select-none">
                      <input type="checkbox" :checked="selectedIds.has(row.id)" @change="toggleId(row.id)"
                        class="rounded text-blue-600 flex-shrink-0" />
                      <svg class="w-3.5 h-3.5 text-blue-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/>
                      </svg>
                      <span class="text-xs font-semibold text-blue-700 truncate">{{ row.name }}</span>
                      <code class="text-[10px] font-mono text-blue-500 bg-blue-100 px-1 rounded flex-shrink-0">{{ row.code }}</code>
                      <span class="text-[10px] text-blue-400 flex-shrink-0">row</span>
                    </label>
                    <div v-if="selectedIds.has(row.id)" class="flex items-center gap-1 flex-shrink-0" @click.stop>
                      <span class="text-[10px] text-slate-400">qty:</span>
                      <input v-model.number="locationQtys[row.id]" type="number" min="0"
                        class="w-20 text-xs border rounded px-1.5 py-0.5 text-right focus:outline-none focus:ring-1"
                        :class="qtyOverLimit ? 'border-red-300 focus:ring-red-400' : 'border-slate-300 focus:ring-blue-400'"
                        placeholder="0" />
                    </div>
                  </div>
                </div>

                <!-- Shelves under this row -->
                <template v-for="shelf in shelvesOf(row.id)" :key="shelf.id">
                  <div class="ml-4 pl-2 border-l-2 border-blue-100 space-y-0.5">
                    <!-- Shelf -->
                    <div v-show="isVisible(shelf)"
                      :class="['rounded border transition-colors', selectedIds.has(shelf.id) ? 'bg-amber-50 border-amber-300' : 'bg-amber-50/50 border-amber-100']">
                      <div class="flex items-center gap-2 px-2 py-1.5">
                        <label class="flex items-center gap-2 flex-1 min-w-0 cursor-pointer select-none">
                          <input type="checkbox" :checked="selectedIds.has(shelf.id)" @change="toggleId(shelf.id)"
                            class="rounded text-amber-600 flex-shrink-0" />
                          <svg class="w-3 h-3 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                          </svg>
                          <span class="text-xs font-medium text-amber-700 truncate">{{ shelf.name }}</span>
                          <code class="text-[10px] font-mono text-amber-500 bg-amber-100 px-1 rounded flex-shrink-0">{{ shelf.code }}</code>
                          <span class="text-[10px] text-amber-400 flex-shrink-0">shelf</span>
                        </label>
                        <div v-if="selectedIds.has(shelf.id)" class="flex items-center gap-1 flex-shrink-0" @click.stop>
                          <span class="text-[10px] text-slate-400">qty:</span>
                          <input v-model.number="locationQtys[shelf.id]" type="number" min="0"
                            class="w-20 text-xs border rounded px-1.5 py-0.5 text-right focus:outline-none focus:ring-1"
                            :class="qtyOverLimit ? 'border-red-300 focus:ring-red-400' : 'border-slate-300 focus:ring-amber-400'"
                            placeholder="0" />
                        </div>
                      </div>
                    </div>

                    <!-- Columns -->
                    <div class="ml-4 border-l-2 border-amber-100 pl-2 space-y-0.5 pb-1">
                      <template v-for="col in columnsOf(shelf.id)" :key="col.id">
                        <div v-if="isVisible(col)"
                          :class="['rounded border transition-colors', selectedIds.has(col.id) ? 'bg-purple-50 border-purple-300' : 'border-transparent hover:bg-slate-50']">
                          <div class="flex items-center gap-2 px-2 py-1.5">
                            <label class="flex items-center gap-2 flex-1 min-w-0 cursor-pointer select-none">
                              <input type="checkbox" :value="col.id" :checked="selectedIds.has(col.id)"
                                @change="toggleId(col.id)" class="rounded text-purple-600 flex-shrink-0" />
                              <svg class="w-3.5 h-3.5 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                              </svg>
                              <span class="text-xs font-medium text-slate-700 truncate">{{ col.name }}</span>
                              <code class="text-[10px] font-mono text-emerald-600 bg-emerald-50 border border-emerald-200 px-1 rounded flex-shrink-0">{{ col.code }}</code>
                            </label>
                            <div v-if="selectedIds.has(col.id)" class="flex items-center gap-1 flex-shrink-0" @click.stop>
                              <span class="text-[10px] text-slate-400">qty:</span>
                              <input v-model.number="locationQtys[col.id]" type="number" min="0"
                                class="w-20 text-xs border rounded px-1.5 py-0.5 text-right focus:outline-none focus:ring-1"
                                :class="qtyOverLimit ? 'border-red-300 focus:ring-red-400' : 'border-slate-300 focus:ring-purple-400'"
                                placeholder="0" />
                            </div>
                          </div>
                        </div>
                      </template>
                    </div>
                  </div>
                </template>

              </template>
            </template>
            <div v-else class="py-8 text-center text-slate-400 text-sm">No locations match "{{ locSearch }}"</div>
          </div>

          <div class="modal-footer flex-shrink-0">
            <button @click="locModal.open = false" class="btn-secondary">Cancel</button>
            <button @click="saveLocations" :disabled="locSaving || qtyOverLimit" class="btn-primary">
              <svg v-if="locSaving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
              </svg>
              {{ locSaving ? 'Saving…' : `Save (${selectedIds.size} selected)` }}
            </button>
          </div>
        </template>

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

import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import StockBadge from '@/components/UI/StockBadge.vue'
import api from '@/api/index.js'

const route = useRoute()
const detail = ref(null)
const loading = ref(true)
const editing = ref(null)
const editQty = ref(0)
const editReason = ref('')
const editLocationId = ref(null)
const saving = ref(false)
const msg = ref({ text: '', type: '' })

// { source_code: [{id, location_id, qty, path}] }
const assignmentsBySource = ref({})
// { source_code: [{id, name, code, type, parent_id}] }
const locationsBySource = ref({})
// { source_code: total_qty }
const stockBySource = ref({})

// Modal state
const locModal = ref({ open: false, sourceCode: '' })
const locSearch = ref('')
const selectedIds = ref(new Set())
// Reactive object so property mutations are tracked: { location_id: qty }
const locationQtys = reactive({})
const locSaving = ref(false)

onMounted(async () => {
  try {
    const { data } = await api.get(`/products/${route.params.id}`)
    detail.value = data
    await loadLocations()
  } finally { loading.value = false }
})

async function loadLocations() {
  try {
    const { data } = await api.get(`/products/${route.params.id}/locations`)
    assignmentsBySource.value = data.assignments || {}
    locationsBySource.value   = data.locations   || {}
    stockBySource.value       = data.stock        || {}
  } catch (_) {}
}

// All assigned location objects for a given source
function locationsFor(sourceCode) {
  return assignmentsBySource.value[sourceCode] || []
}

// ── Stock edit ─────────────────────────────────────────────────

function startEdit(item) {
  editing.value = item.source_code
  editQty.value = item.quantity
  editReason.value = ''
  // Default to first assigned location (if any)
  const locs = locationsFor(item.source_code)
  editLocationId.value = locs.length ? locs[0].location_id : null
}

async function saveQty(item) {
  saving.value = true; msg.value = { text: '', type: '' }
  try {
    await api.put(`/products/${route.params.id}/stock`, {
      source_code:  item.source_code,
      quantity:     editQty.value,
      reason:       editReason.value,
      location_id:  editLocationId.value || undefined,
    })
    item.quantity = editQty.value
    editing.value = null
    await loadLocations()   // refresh location qtys
    showToast('Stock updated successfully', 'ok')
  } catch (e) {
    showToast(e.response?.data?.message || 'Update failed', 'err')
  } finally { saving.value = false }
}

// ── Location modal ─────────────────────────────────────────────

// Flat list for current modal source
const flatLocations = computed(() => locationsBySource.value[locModal.value.sourceCode] || [])

// Available stock for current modal source
const modalAvailable = computed(() => stockBySource.value[locModal.value.sourceCode] ?? 0)

// Total qty assigned across selected locations — reactive because locationQtys is reactive()
const totalAssigned = computed(() => {
  let sum = 0
  for (const id of selectedIds.value) {
    sum += Number(locationQtys[id]) || 0
  }
  return Math.round(sum * 10000) / 10000
})

const qtyOverLimit = computed(() => totalAssigned.value > modalAvailable.value + 0.0001)

// Helpers to build tree structure from flat list
const rowNodes  = computed(() => flatLocations.value.filter(l => l.type === 'row'))
const shelvesOf = (rowId)   => flatLocations.value.filter(l => l.type === 'shelf'  && l.parent_id === rowId)
const columnsOf = (shelfId) => flatLocations.value.filter(l => l.type === 'column' && l.parent_id === shelfId)

// Filtered by search query (matches name or code, case-insensitive)
const filteredLocations = computed(() => {
  const q = locSearch.value.trim().toLowerCase()
  if (!q) return flatLocations.value
  return flatLocations.value.filter(l =>
    l.name.toLowerCase().includes(q) || l.code.toLowerCase().includes(q)
  )
})

// A node is "visible" if it or any ancestor/descendant matches the search
function isVisible(node) {
  const q = locSearch.value.trim().toLowerCase()
  if (!q) return true
  if (node.name.toLowerCase().includes(q) || node.code.toLowerCase().includes(q)) return true
  const children = flatLocations.value.filter(l => l.parent_id === node.id)
  return children.some(c => isVisible(c))
}

const allVisible = computed(() => filteredLocations.value.length)

function toggleId(id) {
  const next = new Set(selectedIds.value)
  if (next.has(id)) {
    next.delete(id)
    delete locationQtys[id]
  } else {
    next.add(id)
    if (locationQtys[id] === undefined) locationQtys[id] = 0
  }
  selectedIds.value = next
}

function toggleAll() {
  const visibleIds = filteredLocations.value.map(l => l.id)
  const allChecked = visibleIds.every(id => selectedIds.value.has(id))
  const next = new Set(selectedIds.value)
  if (allChecked) {
    visibleIds.forEach(id => { next.delete(id); delete locationQtys[id] })
  } else {
    visibleIds.forEach(id => { next.add(id); if (locationQtys[id] === undefined) locationQtys[id] = 0 })
  }
  selectedIds.value = next
}

function clearAll() {
  selectedIds.value = new Set()
  Object.keys(locationQtys).forEach(k => delete locationQtys[k])
}

async function openLocationModal(item) {
  // Ensure locations loaded for this source
  if (!locationsBySource.value[item.source_code]) {
    try {
      const { data } = await api.get(`/sources/${item.source_code}/locations/flat`)
      locationsBySource.value = { ...locationsBySource.value, [item.source_code]: data }
    } catch (_) {
      locationsBySource.value = { ...locationsBySource.value, [item.source_code]: [] }
    }
  }

  // Seed selected IDs and qtys from current assignments
  const current = assignmentsBySource.value[item.source_code] || []
  const ids = new Set(current.map(a => a.location_id))

  // Reset and re-populate locationQtys
  Object.keys(locationQtys).forEach(k => delete locationQtys[k])
  for (const a of current) {
    locationQtys[a.location_id] = a.qty ?? 0
  }

  selectedIds.value  = ids
  locSearch.value    = ''
  locModal.value     = { open: true, sourceCode: item.source_code }
}

async function saveLocations() {
  locSaving.value = true
  try {
    const locations = [...selectedIds.value].map(id => ({
      location_id: id,
      qty: Number(locationQtys[id]) || 0,
    }))
    await api.put(`/products/${route.params.id}/location`, {
      source_code: locModal.value.sourceCode,
      locations,
    })
    await loadLocations()
    locModal.value.open = false
    showToast(`${locations.length} location(s) assigned`, 'ok')
  } catch (e) {
    showToast(e.response?.data?.message || 'Failed', 'err')
  } finally { locSaving.value = false }
}

function showToast(text, type) {
  msg.value = { text, type }
  setTimeout(() => msg.value.text = '', 3500)
}
</script>
