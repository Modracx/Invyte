<template>
  <div class="space-y-3">
    <div class="filter-bar">
      <div class="filter-group flex-1">
        <label class="filter-label">Search</label>
        <input v-model="search" @input="debounce" type="text" class="form-input" placeholder="Name, code or contact…" />
      </div>
      <div class="filter-group">
        <label class="filter-label">Status</label>
        <select v-model="statusFilter" @change="load(1)" class="form-select">
          <option value="">All</option>
          <option value="1">Active</option>
          <option value="0">Inactive</option>
        </select>
      </div>
      <button v-if="!showForm" @click="openForm(null)" class="btn-primary self-end">+ Add Supplier</button>
    </div>

    <div v-if="showForm" class="card p-4 space-y-3">
      <h4 class="text-sm font-semibold text-slate-700">{{ editId ? 'Edit Supplier' : 'New Supplier' }}</h4>
      <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
        <div class="filter-group">
          <label class="filter-label">Name *</label>
          <input v-model="form.name" class="form-input" />
        </div>
        <div class="filter-group">
          <label class="filter-label">Code *</label>
          <input v-model="form.code" class="form-input" :disabled="!!editId" />
        </div>
        <div class="filter-group">
          <label class="filter-label">Contact Name</label>
          <input v-model="form.contact_name" class="form-input" />
        </div>
        <div class="filter-group">
          <label class="filter-label">Email</label>
          <input v-model="form.email" type="email" class="form-input" />
        </div>
        <div class="filter-group">
          <label class="filter-label">Phone</label>
          <input v-model="form.phone" class="form-input" />
        </div>
        <div class="filter-group">
          <label class="filter-label">City</label>
          <input v-model="form.city" class="form-input" />
        </div>
        <div class="filter-group">
          <label class="filter-label">Status</label>
          <select v-model="form.is_active" class="form-select">
            <option :value="true">Active</option>
            <option :value="false">Inactive</option>
          </select>
        </div>
      </div>
      <div v-if="formError" class="text-xs text-red-600">{{ formError }}</div>
      <div class="flex gap-2">
        <button @click="save" :disabled="saving" class="btn-primary">{{ saving ? 'Saving…' : 'Save' }}</button>
        <button @click="cancelForm" class="btn-secondary">Cancel</button>
      </div>
    </div>

    <div class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead>
            <tr class="border-b border-slate-200">
              <th class="table-header">Name</th>
              <th class="table-header">Code</th>
              <th class="table-header">Contact</th>
              <th class="table-header">Email</th>
              <th class="table-header">Phone</th>
              <th class="table-header">City</th>
              <th class="table-header">Status</th>
              <th class="table-header"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading"><td colspan="8" class="table-cell text-center text-slate-400 py-8">Loading…</td></tr>
            <template v-else>
              <tr v-for="s in data.data" :key="s.supplier_id" class="table-row">
                <td class="table-cell font-medium">{{ s.name }}</td>
                <td class="table-cell font-mono text-xs text-slate-500">{{ s.code }}</td>
                <td class="table-cell text-xs text-slate-500">{{ s.contact_name || '—' }}</td>
                <td class="table-cell text-xs text-slate-500">{{ s.email || '—' }}</td>
                <td class="table-cell text-xs text-slate-500">{{ s.phone || '—' }}</td>
                <td class="table-cell text-xs text-slate-500">{{ s.city || '—' }}</td>
                <td class="table-cell"><span class="badge" :class="s.is_active ? 'badge-green' : 'badge-gray'">{{ s.is_active ? 'Active' : 'Inactive' }}</span></td>
                <td class="table-cell flex gap-2">
                  <button @click="openForm(s)" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Edit</button>
                  <button @click="del(s.supplier_id)" class="text-red-500 hover:text-red-700 text-xs font-medium">Delete</button>
                </td>
              </tr>
              <tr v-if="!data.data?.length">
                <td colspan="8" class="table-cell text-center text-slate-400 py-8">No suppliers found</td>
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

import { ref, onMounted } from 'vue'
import Pagination from '@/components/UI/Pagination.vue'
import api from '@/api/index.js'

const data = ref({ data: [], total: 0, current_page: 1, last_page: 1 })
const loading = ref(true)
const perPage = ref(20)
const search = ref('')
const statusFilter = ref('')
const showForm = ref(false)
const editId = ref(null)
const saving = ref(false)
const formError = ref('')
const form = ref({ name: '', code: '', contact_name: '', email: '', phone: '', city: '', is_active: true })
let timer = null

onMounted(() => load(1))

async function load(page = 1) {
  loading.value = true
  try {
    const params = { page, per_page: perPage.value }
    if (search.value) params.search = search.value
    if (statusFilter.value !== '') params.is_active = statusFilter.value
    const { data: res } = await api.get('/suppliers', { params })
    data.value = res
  } finally { loading.value = false }
}

function debounce() { clearTimeout(timer); timer = setTimeout(() => load(1), 350) }

function openForm(s) {
  editId.value = s?.supplier_id || null
  form.value = s ? { ...s } : { name: '', code: '', contact_name: '', email: '', phone: '', city: '', is_active: true }
  showForm.value = true; formError.value = ''
}

function cancelForm() { showForm.value = false; editId.value = null; formError.value = '' }

async function save() {
  saving.value = true; formError.value = ''
  try {
    editId.value ? await api.put(`/suppliers/${editId.value}`, form.value) : await api.post('/suppliers', form.value)
    cancelForm(); await load(1)
  } catch (e) { formError.value = e.response?.data?.message || 'Save failed' }
  finally { saving.value = false }
}

async function del(id) {
  if (!confirm('Delete this supplier?')) return
  try { await api.delete(`/suppliers/${id}`); await load(1) } catch (e) { alert(e.response?.data?.message || 'Failed') }
}
</script>
