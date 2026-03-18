<template>
  <div class="space-y-4">

    <!-- Page header -->
    <div class="flex items-start justify-between">
      <div class="flex items-center gap-3">
        <RouterLink to="/ims/sources" class="icon-btn-gray">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
        </RouterLink>
        <div>
          <div class="flex items-center gap-2">
            <h1 class="text-base font-semibold text-slate-900">Location Layout</h1>
            <code class="text-xs font-mono bg-slate-100 text-slate-600 px-2 py-0.5 rounded border border-slate-200">{{ code }}</code>
          </div>
          <p class="text-xs text-slate-500 mt-0.5">Define rows, shelves and columns to precisely locate inventory</p>
        </div>
      </div>
      <button @click="openAdd(null)" class="btn-primary">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Add Row
      </button>
    </div>

    <!-- Level legend + stats -->
    <div class="grid grid-cols-3 gap-3">
      <div class="stat-card border-l-4 border-blue-400">
        <div class="stat-icon bg-blue-100">
          <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/>
          </svg>
        </div>
        <div>
          <div class="stat-val">{{ countByType('row') }}</div>
          <div class="stat-lbl">Rows</div>
        </div>
      </div>
      <div class="stat-card border-l-4 border-amber-400">
        <div class="stat-icon bg-amber-100">
          <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
          </svg>
        </div>
        <div>
          <div class="stat-val">{{ countByType('shelf') }}</div>
          <div class="stat-lbl">Shelves</div>
        </div>
      </div>
      <div class="stat-card border-l-4 border-emerald-400">
        <div class="stat-icon bg-emerald-100">
          <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
          </svg>
        </div>
        <div>
          <div class="stat-val">{{ countByType('column') }}</div>
          <div class="stat-lbl">Columns</div>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="card p-12 flex flex-col items-center gap-3 text-slate-400">
      <svg class="w-8 h-8 animate-spin" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
      </svg>
      <span class="text-sm">Loading locations…</span>
    </div>

    <!-- Empty state -->
    <div v-else-if="!tree.length" class="card p-12 flex flex-col items-center gap-3 text-slate-400">
      <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center">
        <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
      </div>
      <div class="text-center">
        <p class="font-medium text-slate-600">No locations defined</p>
        <p class="text-xs text-slate-400 mt-1">Start by adding a Row, then add Shelves and Columns inside it.</p>
      </div>
      <button @click="openAdd(null)" class="btn-primary mt-1">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Add First Row
      </button>
    </div>

    <!-- Location tree -->
    <div v-else class="card overflow-hidden">
      <div class="px-4 py-3 border-b border-slate-200 flex items-center gap-2">
        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
        </svg>
        <span class="text-sm font-semibold text-slate-700">Location Hierarchy</span>
        <span class="text-xs text-slate-400">Row → Shelf → Column</span>
      </div>
      <div class="p-3 space-y-2">
        <LocationNode
          v-for="row in tree" :key="row.id"
          :node="row"
          :depth="0"
          @add-child="openAdd"
          @edit="openEdit"
          @delete="deleteNode"
        />
      </div>
    </div>

    <!-- Add / Edit Modal -->
    <div v-if="modal.open" class="modal-overlay" @click.self="modal.open = false">
      <div class="modal-box max-w-sm">
        <div class="modal-header">
          <div>
            <h3 class="font-semibold text-slate-900">
              {{ modal.isEdit ? 'Edit Location' : (modal.parentId ? `Add ${nextTypeLabel(modal.parentType)}` : 'Add Row') }}
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">
              {{ modal.isEdit ? 'Update name or code' : typeHint(modal.parentType) }}
            </p>
          </div>
          <button @click="modal.open = false" class="icon-btn-gray">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div class="p-5 space-y-4">
          <!-- Type indicator -->
          <div v-if="!modal.isEdit" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium"
            :class="typeColors(modal.parentType).pill">
            <component :is="typeIcon(modal.parentType)" class="w-4 h-4" />
            {{ nextTypeLabel(modal.parentType) }} level
          </div>

          <div>
            <label class="form-label">Name <span class="text-red-500">*</span></label>
            <input v-model="modal.name" class="form-input" :placeholder="namePlaceholder(modal.parentType)" @keyup.enter="save" />
          </div>
          <div>
            <label class="form-label">Code <span class="text-red-500">*</span></label>
            <input v-model="modal.code" class="form-input" :placeholder="codePlaceholder(modal.parentType)" @keyup.enter="save" />
            <p class="text-xs text-slate-400 mt-1">Short identifier used in location paths</p>
          </div>
          <div>
            <label class="form-label">Sort Order</label>
            <input v-model.number="modal.sortOrder" type="number" min="0" class="form-input w-24" />
          </div>

          <div v-if="modal.error" class="flex items-center gap-2 px-3 py-2 bg-red-50 text-red-700 text-xs rounded-md border border-red-200">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ modal.error }}
          </div>
        </div>

        <div class="modal-footer">
          <button @click="modal.open = false" class="btn-secondary">Cancel</button>
          <button @click="save" :disabled="modal.saving" class="btn-primary">
            <svg v-if="modal.saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            {{ modal.saving ? 'Saving…' : 'Save' }}
          </button>
        </div>
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

import { ref, onMounted, computed, h } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import api from '@/api/index.js'
import LocationNode from '@/components/UI/LocationNode.vue'

const route = useRoute()
const code = route.params.code
const tree = ref([])
const loading = ref(true)
const allFlat = ref([])   // flattened for counting

const modal = ref({ open: false, isEdit: false, editId: null, parentId: null, parentType: null,
  name: '', code: '', sortOrder: 0, saving: false, error: '' })

onMounted(load)

async function load() {
  loading.value = true
  try {
    const { data } = await api.get(`/sources/${code}/locations`)
    tree.value = data
    flattenTree(data)
  } finally { loading.value = false }
}

function flattenTree(nodes) {
  allFlat.value = []
  function walk(arr) {
    for (const n of arr) {
      allFlat.value.push(n)
      const children = n.children_deep || n.childrenDeep || []
      if (children.length) walk(children)
    }
  }
  walk(nodes)
}

function countByType(type) {
  return allFlat.value.filter(n => n.type === type).length
}

function nextTypeLabel(parentType) {
  return parentType === 'row' ? 'Shelf' : parentType === 'shelf' ? 'Column' : 'Row'
}

function typeHint(parentType) {
  if (!parentType) return 'Top-level grouping (e.g. Row A, Row B)'
  if (parentType === 'row') return 'Second level inside a row (e.g. Shelf 1, Top Shelf)'
  return 'Final level where items are stored (e.g. Column 3, Slot 5)'
}

function namePlaceholder(parentType) {
  return !parentType ? 'Row A' : parentType === 'row' ? 'Shelf 1' : 'Column 3'
}

function codePlaceholder(parentType) {
  return !parentType ? 'A' : parentType === 'row' ? '1' : '3'
}

function typeColors(parentType) {
  if (!parentType) return { pill: 'bg-blue-50 text-blue-700' }
  if (parentType === 'row') return { pill: 'bg-amber-50 text-amber-700' }
  return { pill: 'bg-emerald-50 text-emerald-700' }
}

const typeIcon = (parentType) => ({
  render() {
    const d = !parentType
      ? 'M4 6h16M4 12h16M4 18h7'
      : parentType === 'row'
        ? 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'
        : 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'
    return h('svg', { class: 'w-4 h-4', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor', 'stroke-width': 2 }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d })
    ])
  }
})

function openAdd(node) {
  modal.value = { open: true, isEdit: false, editId: null,
    parentId: node?.id ?? null, parentType: node?.type ?? null,
    name: '', code: '', sortOrder: 0, saving: false, error: '' }
}

function openEdit(node) {
  modal.value = { open: true, isEdit: true, editId: node.id,
    parentId: node.parent_id, parentType: null,
    name: node.name, code: node.code, sortOrder: node.sort_order ?? 0, saving: false, error: '' }
}

async function save() {
  const m = modal.value
  if (!m.name.trim() || !m.code.trim()) { m.error = 'Name and Code are required'; return }
  m.saving = true; m.error = ''
  try {
    if (m.isEdit) {
      await api.put(`/sources/${code}/locations/${m.editId}`, { name: m.name, code: m.code, sort_order: m.sortOrder })
    } else {
      await api.post(`/sources/${code}/locations`, { parent_id: m.parentId, name: m.name, code: m.code, sort_order: m.sortOrder })
    }
    modal.value.open = false
    await load()
  } catch (e) {
    m.error = e.response?.data?.message || 'Save failed'
  } finally { m.saving = false }
}

async function deleteNode(node) {
  const desc = node.type === 'row' ? 'row and all its shelves/columns'
    : node.type === 'shelf' ? 'shelf and all its columns' : 'column'
  if (!confirm(`Delete "${node.name}"?\nThis will remove this ${desc} and all product assignments within it.`)) return
  try {
    await api.delete(`/sources/${code}/locations/${node.id}`)
    await load()
  } catch (e) { alert(e.response?.data?.message || 'Delete failed') }
}
</script>
