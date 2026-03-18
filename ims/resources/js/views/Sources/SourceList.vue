<template>
  <div class="space-y-4">

    <!-- Page header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-base font-semibold text-slate-900">Warehouses</h1>
        <p class="text-xs text-slate-500 mt-0.5">Manage inventory sources and their location layouts</p>
      </div>
      <button @click="openForm(null)" class="btn-primary">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Add Warehouse
      </button>
    </div>

    <!-- Stats row -->
    <div class="grid grid-cols-3 gap-3">
      <div class="stat-card">
        <div class="stat-icon bg-blue-100">
          <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
        </div>
        <div>
          <div class="stat-val">{{ sources.length }}</div>
          <div class="stat-lbl">Total Warehouses</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon bg-emerald-100">
          <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <div>
          <div class="stat-val">{{ sources.filter(s => s.enabled).length }}</div>
          <div class="stat-lbl">Active</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon bg-slate-100">
          <svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
          </svg>
        </div>
        <div>
          <div class="stat-val">{{ sources.filter(s => !s.enabled).length }}</div>
          <div class="stat-lbl">Inactive</div>
        </div>
      </div>
    </div>

    <!-- Table card -->
    <div class="card overflow-hidden">
      <!-- Toolbar -->
      <div class="filter-bar">
        <div class="filter-group flex-1 max-w-xs">
          <label class="filter-label">Search</label>
          <div class="relative">
            <svg class="absolute left-2.5 top-2 w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input v-model="search" type="text" class="form-input pl-8" placeholder="Code or name…" />
          </div>
        </div>
        <div class="filter-group">
          <label class="filter-label">Status</label>
          <select v-model="statusFilter" class="form-select">
            <option value="">All</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead>
            <tr class="border-b border-slate-200">
              <th class="table-header">Source Code</th>
              <th class="table-header">Name</th>
              <th class="table-header">Contact</th>
              <th class="table-header">Location</th>
              <th class="table-header text-center">Status</th>
              <th class="table-header text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="6" class="table-cell text-center py-12">
                <div class="flex flex-col items-center gap-2 text-slate-400">
                  <svg class="w-8 h-8 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                  </svg>
                  <span class="text-xs">Loading warehouses…</span>
                </div>
              </td>
            </tr>
            <template v-else>
              <tr v-for="s in filtered" :key="s.source_code" class="table-row">
                <td class="table-cell">
                  <code class="font-mono text-xs font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded">{{ s.source_code }}</code>
                </td>
                <td class="table-cell font-medium text-slate-900">{{ s.name }}</td>
                <td class="table-cell">
                  <div v-if="s.contact_name" class="text-slate-700 text-xs">{{ s.contact_name }}</div>
                  <div v-if="s.email" class="text-slate-400 text-xs">{{ s.email }}</div>
                  <span v-if="!s.contact_name && !s.email" class="text-slate-300 text-xs">—</span>
                </td>
                <td class="table-cell text-xs text-slate-500">
                  {{ [s.city, s.country_id].filter(Boolean).join(', ') || '—' }}
                </td>
                <td class="table-cell text-center">
                  <span class="badge" :class="s.enabled ? 'badge-green' : 'badge-gray'">
                    <span class="w-1.5 h-1.5 rounded-full mr-1" :class="s.enabled ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                    {{ s.enabled ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="table-cell">
                  <div class="flex items-center justify-end gap-1">
                    <!-- Locations -->
                    <RouterLink :to="`/ims/sources/${s.source_code}/locations`"
                      class="icon-btn-purple" title="Manage Locations">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                      </svg>
                    </RouterLink>
                    <!-- Edit -->
                    <button @click="openForm(s)" class="icon-btn-blue" title="Edit">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                    </button>
                    <!-- Delete -->
                    <button @click="del(s.source_code)" class="icon-btn-red" title="Delete">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!filtered.length">
                <td colspan="6" class="table-cell text-center py-12 text-slate-400">
                  <svg class="w-10 h-10 mx-auto mb-2 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                  </svg>
                  No warehouses found
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add / Edit Modal -->
    <div v-if="showForm" class="modal-overlay" @click.self="cancelForm">
      <div class="modal-box max-w-xl">
        <div class="modal-header">
          <div>
            <h3 class="font-semibold text-slate-900">{{ editCode ? 'Edit Warehouse' : 'New Warehouse' }}</h3>
            <p class="text-xs text-slate-500 mt-0.5">{{ editCode ? `Editing ${editCode}` : 'Fill in the details below' }}</p>
          </div>
          <button @click="cancelForm" class="icon-btn-gray">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div class="p-5 space-y-4 overflow-y-auto">
          <div class="grid grid-cols-2 gap-4">
            <div v-if="!editCode">
              <label class="form-label">Source Code <span class="text-red-500">*</span></label>
              <input v-model="form.source_code" class="form-input" placeholder="e.g. warehouse-east" />
            </div>
            <div>
              <label class="form-label">Name <span class="text-red-500">*</span></label>
              <input v-model="form.name" class="form-input" />
            </div>
            <div>
              <label class="form-label">Status</label>
              <select v-model="form.enabled" class="form-select w-full">
                <option :value="true">Active</option>
                <option :value="false">Inactive</option>
              </select>
            </div>
          </div>

          <div class="border-t border-slate-100 pt-4">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Contact & Address</p>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="form-label">Contact Name</label>
                <input v-model="form.contact_name" class="form-input" />
              </div>
              <div>
                <label class="form-label">Email</label>
                <input v-model="form.email" type="email" class="form-input" />
              </div>
              <div>
                <label class="form-label">Phone</label>
                <input v-model="form.phone" class="form-input" />
              </div>
              <div>
                <label class="form-label">City</label>
                <input v-model="form.city" class="form-input" />
              </div>
              <div>
                <label class="form-label">Country (2-letter)</label>
                <input v-model="form.country_id" maxlength="2" class="form-input" placeholder="US" />
              </div>
              <div>
                <label class="form-label">Postcode</label>
                <input v-model="form.postcode" class="form-input" />
              </div>
            </div>
          </div>

          <div v-if="formError" class="flex items-center gap-2 px-3 py-2 bg-red-50 text-red-700 text-xs rounded-md border border-red-200">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ formError }}
          </div>
        </div>

        <div class="modal-footer">
          <button @click="cancelForm" class="btn-secondary">Cancel</button>
          <button @click="save" :disabled="saving" class="btn-primary">
            <svg v-if="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            {{ saving ? 'Saving…' : 'Save Warehouse' }}
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

import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/api/index.js'

const sources = ref([])
const loading = ref(true)
const search = ref('')
const statusFilter = ref('')
const showForm = ref(false)
const editCode = ref(null)
const saving = ref(false)
const formError = ref('')
const form = ref({ source_code: '', name: '', contact_name: '', email: '', phone: '', city: '', country_id: '', postcode: '', enabled: true })

const filtered = computed(() => sources.value.filter(s => {
  if (search.value && !s.source_code.toLowerCase().includes(search.value.toLowerCase()) && !s.name.toLowerCase().includes(search.value.toLowerCase())) return false
  if (statusFilter.value !== '' && String(+s.enabled) !== statusFilter.value) return false
  return true
}))

onMounted(load)

async function load() {
  loading.value = true
  try { const { data } = await api.get('/sources'); sources.value = data } finally { loading.value = false }
}

function openForm(s) {
  editCode.value = s ? s.source_code : null
  form.value = s ? { ...s } : { source_code: '', name: '', contact_name: '', email: '', phone: '', city: '', country_id: '', postcode: '', enabled: true }
  showForm.value = true
  formError.value = ''
}

function cancelForm() { showForm.value = false; editCode.value = null; formError.value = '' }

async function save() {
  saving.value = true; formError.value = ''
  try {
    editCode.value ? await api.put(`/sources/${editCode.value}`, form.value) : await api.post('/sources', form.value)
    cancelForm(); await load()
  } catch (e) { formError.value = e.response?.data?.message || 'Save failed' }
  finally { saving.value = false }
}

async function del(code) {
  if (!confirm(`Delete warehouse '${code}'? This cannot be undone.`)) return
  try { await api.delete(`/sources/${code}`); await load() } catch (e) { alert(e.response?.data?.message || 'Delete failed') }
}
</script>
