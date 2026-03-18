<template>
  <div class="max-w-4xl space-y-4">
    <div v-if="loading" class="text-center py-12 text-slate-400 text-sm">Loading…</div>

    <template v-else>
      <!-- NEW PO FORM -->
      <div v-if="isNew" class="card p-4 space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-semibold text-slate-700">New Purchase Order</h3>
          <RouterLink to="/ims/purchase-orders" class="btn-secondary text-xs">← Back</RouterLink>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
          <div class="filter-group">
            <label class="filter-label">Supplier</label>
            <select v-model="form.supplier_id" class="form-select w-full">
              <option value="">— None —</option>
              <option v-for="s in suppliers" :key="s.supplier_id" :value="s.supplier_id">{{ s.name }}</option>
            </select>
          </div>
          <div class="filter-group">
            <label class="filter-label">Source / Warehouse *</label>
            <select v-model="form.source_code" class="form-select w-full">
              <option value="">— Select —</option>
              <option v-for="s in sources" :key="s.source_code" :value="s.source_code">{{ s.source_code }}</option>
            </select>
          </div>
          <div class="filter-group">
            <label class="filter-label">Expected Date</label>
            <input v-model="form.expected_date" type="date" class="form-input" />
          </div>
          <div class="filter-group col-span-2 md:col-span-4">
            <label class="filter-label">Notes</label>
            <input v-model="form.notes" type="text" class="form-input" placeholder="Optional notes…" />
          </div>
        </div>

        <div>
          <div class="flex items-center justify-between mb-2">
            <label class="text-xs font-semibold text-slate-600">Line Items</label>
            <button @click="addItem" class="btn-secondary text-xs">+ Add Item</button>
          </div>
          <table class="min-w-full border border-slate-200 rounded-lg overflow-hidden text-xs">
            <thead>
              <tr class="border-b border-slate-200">
                <th class="table-header w-1/2">SKU</th>
                <th class="table-header w-24 text-right">Qty Ordered</th>
                <th class="table-header w-24 text-right">Unit Cost ($)</th>
                <th class="table-header w-10"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, i) in form.items" :key="i" class="border-b border-slate-100">
                <td class="px-2 py-1.5"><input v-model="item.sku" type="text" class="form-input font-mono" placeholder="Product SKU" /></td>
                <td class="px-2 py-1.5"><input v-model.number="item.qty_ordered" type="number" min="1" class="form-input text-right" /></td>
                <td class="px-2 py-1.5"><input v-model.number="item.unit_cost" type="number" min="0" step="0.01" class="form-input text-right" /></td>
                <td class="px-2 py-1.5 text-center"><button @click="form.items.splice(i,1)" class="text-red-400 hover:text-red-600">✕</button></td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="formError" class="text-xs text-red-600">{{ formError }}</div>
        <div class="flex gap-2">
          <button @click="create" :disabled="saving" class="btn-primary">{{ saving ? 'Creating…' : 'Create Purchase Order' }}</button>
        </div>
      </div>

      <!-- EXISTING PO VIEW -->
      <template v-else-if="po">
        <div class="card p-4">
          <div class="flex items-start justify-between">
            <div>
              <div class="flex items-center gap-2">
                <h3 class="font-semibold text-slate-900">PO-{{ po.id }}</h3>
                <StatusBadge :status="po.status" />
              </div>
              <div class="flex gap-4 mt-1.5 text-xs text-slate-500">
                <span>Supplier: <strong>{{ po.supplier?.name || '—' }}</strong></span>
                <span>Source: <code class="font-mono bg-slate-100 px-1 rounded">{{ po.source_code }}</code></span>
                <span v-if="po.expected_date">Expected: <strong>{{ po.expected_date }}</strong></span>
                <span>Created by: <strong>{{ po.created_by?.name || '—' }}</strong></span>
              </div>
              <p v-if="po.notes" class="text-xs text-slate-400 mt-1">{{ po.notes }}</p>
            </div>
            <RouterLink to="/ims/purchase-orders" class="btn-secondary text-xs">← Back</RouterLink>
          </div>
        </div>

        <div class="card overflow-hidden">
          <div class="px-4 py-2.5 border-b border-slate-200 flex items-center justify-between">
            <h4 class="text-sm font-semibold text-slate-700">Line Items</h4>
            <button v-if="canReceive" @click="receiveAll" :disabled="receiving" class="btn-success text-xs">
              {{ receiving ? 'Processing…' : 'Receive Stock' }}
            </button>
          </div>
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-slate-200">
                <th class="table-header">SKU</th>
                <th class="table-header">Product</th>
                <th class="table-header text-right">Ordered</th>
                <th class="table-header text-right">Received</th>
                <th class="table-header text-right">Remaining</th>
                <th v-if="canReceive" class="table-header text-right w-28">Receive Now</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in po.items" :key="item.id" class="table-row">
                <td class="table-cell font-mono text-xs">{{ item.sku }}</td>
                <td class="table-cell text-xs text-slate-500 max-w-xs truncate">{{ item.product_name }}</td>
                <td class="table-cell text-right text-xs">{{ item.qty_ordered }}</td>
                <td class="table-cell text-right text-xs">
                  <span :class="item.qty_received >= item.qty_ordered ? 'text-emerald-600 font-medium' : ''">{{ item.qty_received }}</span>
                </td>
                <td class="table-cell text-right text-xs" :class="item.qty_ordered - item.qty_received > 0 ? 'text-amber-600' : 'text-slate-400'">
                  {{ item.qty_ordered - item.qty_received }}
                </td>
                <td v-if="canReceive" class="table-cell text-right">
                  <input v-model.number="receiveQtys[item.id]" type="number" min="0" :max="item.qty_ordered - item.qty_received"
                    class="form-input w-20 text-right" />
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="msg.text" class="px-3 py-2 rounded-md text-sm" :class="msg.type === 'ok' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'">{{ msg.text }}</div>
      </template>
    </template>
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

import { ref, computed, onMounted, h } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { useRouter } from 'vue-router'
import api from '@/api/index.js'

const route = useRoute()
const router = useRouter()
const isNew = computed(() => route.params.id === 'new' || !route.params.id)
const po = ref(null)
const loading = ref(!isNew.value)
const saving = ref(false)
const receiving = ref(false)
const suppliers = ref([])
const sources = ref([])
const receiveQtys = ref({})
const formError = ref('')
const msg = ref({ text: '', type: '' })

const form = ref({ supplier_id: '', source_code: '', expected_date: '', notes: '', items: [{ sku: '', qty_ordered: 1, unit_cost: 0 }] })
const canReceive = computed(() => po.value && ['draft','pending','partial'].includes(po.value.status))

const statusMap = { draft: 'badge-gray', pending: 'badge-yellow', partial: 'badge-blue', received: 'badge-green', cancelled: 'badge-red' }
const StatusBadge = {
  props: ['status'],
  render() { return h('span', { class: ['badge', statusMap[this.status] || 'badge-gray'].join(' ') }, this.status) }
}

onMounted(async () => {
  try { const { data: s } = await api.get('/suppliers', { params: { per_page: 100 } }); suppliers.value = s.data || s } catch {}
  try { const { data: s } = await api.get('/sources'); sources.value = s } catch {}
  if (!isNew.value) {
    try {
      const { data } = await api.get(`/purchase-orders/${route.params.id}`)
      po.value = data
      data.items?.forEach(i => receiveQtys.value[i.id] = 0)
    } finally { loading.value = false }
  }
})

function addItem() { form.value.items.push({ sku: '', qty_ordered: 1, unit_cost: 0 }) }

async function create() {
  if (!form.value.source_code) { formError.value = 'Source is required'; return }
  if (!form.value.items[0]?.sku) { formError.value = 'At least one item with SKU is required'; return }
  saving.value = true; formError.value = ''
  try {
    const { data } = await api.post('/purchase-orders', form.value)
    router.push(`/ims/purchase-orders/${data.id}`)
  } catch (e) { formError.value = e.response?.data?.message || 'Failed to create PO' }
  finally { saving.value = false }
}

async function receiveAll() {
  const items = po.value.items.map(i => ({ id: i.id, qty_received: receiveQtys.value[i.id] || 0 })).filter(i => i.qty_received > 0)
  if (!items.length) { msg.value = { text: 'Enter quantities to receive', type: 'err' }; return }
  receiving.value = true; msg.value = { text: '', type: '' }
  try {
    await api.put(`/purchase-orders/${po.value.id}/receive`, { items })
    const { data } = await api.get(`/purchase-orders/${po.value.id}`)
    po.value = data
    msg.value = { text: 'Stock received successfully', type: 'ok' }
    setTimeout(() => msg.value.text = '', 4000)
  } catch (e) { msg.value = { text: e.response?.data?.message || 'Failed', type: 'err' } }
  finally { receiving.value = false }
}
</script>
