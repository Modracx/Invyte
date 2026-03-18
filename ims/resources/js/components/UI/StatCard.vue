<template>
  <div class="card p-4 flex items-center gap-4">
    <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center" :class="iconBg">
      <component :is="iconComponent" class="w-5 h-5" :class="iconColor" />
    </div>
    <div class="min-w-0">
      <p class="text-xs font-medium text-slate-500 truncate">{{ label }}</p>
      <p class="text-2xl font-bold text-slate-900 leading-tight">{{ value }}</p>
      <p v-if="sub" class="text-xs text-slate-400 truncate">{{ sub }}</p>
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

import { computed, h } from 'vue'

const props = defineProps({
  label: String,
  value: [String, Number],
  icon: { type: String, default: 'box' },
  sub: String,
})

const iconMap = {
  box: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
  products: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
  alert: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
  warehouse: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
  cart: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
}

const iconBgMap = { box: 'bg-blue-50', products: 'bg-blue-50', alert: 'bg-amber-50', warehouse: 'bg-violet-50', cart: 'bg-emerald-50' }
const iconColorMap = { box: 'text-blue-600', products: 'text-blue-600', alert: 'text-amber-600', warehouse: 'text-violet-600', cart: 'text-emerald-600' }

const iconBg = computed(() => iconBgMap[props.icon] || 'bg-slate-100')
const iconColor = computed(() => iconColorMap[props.icon] || 'text-slate-600')

const iconComponent = computed(() => ({
  render: () => h('svg', { fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor', strokeWidth: 2 }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: iconMap[props.icon] || iconMap.box })
  ])
}))
</script>
