<template>
  <div v-if="lastPage > 1" class="flex items-center justify-between px-3 py-2 border-t border-slate-200 bg-slate-50 rounded-b-lg">
    <div class="text-xs text-slate-500">
      Showing {{ from }}–{{ to }} of {{ total }}
    </div>
    <div class="flex items-center gap-1">
      <button @click="$emit('change', 1)" :disabled="currentPage === 1" class="px-2 py-1 rounded text-xs border border-slate-300 disabled:opacity-40 hover:bg-white transition-colors">«</button>
      <button @click="$emit('change', currentPage - 1)" :disabled="currentPage === 1" class="px-2 py-1 rounded text-xs border border-slate-300 disabled:opacity-40 hover:bg-white transition-colors">‹</button>

      <template v-for="p in pages" :key="p">
        <span v-if="p === '...'" class="px-2 py-1 text-xs text-slate-400">…</span>
        <button v-else @click="$emit('change', p)"
          class="px-2 py-1 rounded text-xs border transition-colors"
          :class="p === currentPage ? 'bg-blue-600 text-white border-blue-600' : 'border-slate-300 hover:bg-white'">
          {{ p }}
        </button>
      </template>

      <button @click="$emit('change', currentPage + 1)" :disabled="currentPage === lastPage" class="px-2 py-1 rounded text-xs border border-slate-300 disabled:opacity-40 hover:bg-white transition-colors">›</button>
      <button @click="$emit('change', lastPage)" :disabled="currentPage === lastPage" class="px-2 py-1 rounded text-xs border border-slate-300 disabled:opacity-40 hover:bg-white transition-colors">»</button>

      <select :value="perPage" @change="$emit('per-page', +$event.target.value)"
        class="ml-2 rounded border-slate-300 text-xs py-1">
        <option v-for="n in [10, 20, 50, 100]" :key="n" :value="n">{{ n }} / page</option>
      </select>
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

import { computed } from 'vue'

const props = defineProps({
  currentPage: { type: Number, required: true },
  lastPage: { type: Number, required: true },
  total: { type: Number, required: true },
  perPage: { type: Number, default: 20 },
})

defineEmits(['change', 'per-page'])

const from = computed(() => Math.min((props.currentPage - 1) * props.perPage + 1, props.total))
const to = computed(() => Math.min(props.currentPage * props.perPage, props.total))

const pages = computed(() => {
  const { currentPage: c, lastPage: l } = props
  if (l <= 7) return Array.from({ length: l }, (_, i) => i + 1)
  if (c <= 4) return [1, 2, 3, 4, 5, '...', l]
  if (c >= l - 3) return [1, '...', l - 4, l - 3, l - 2, l - 1, l]
  return [1, '...', c - 1, c, c + 1, '...', l]
})
</script>
