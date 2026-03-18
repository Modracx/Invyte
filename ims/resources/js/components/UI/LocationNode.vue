<template>
  <div class="select-none">
    <!-- Node row -->
    <div
      :class="['flex items-center gap-2 rounded-lg px-3 py-2 group transition-colors', depthStyle.hover]"
    >
      <!-- Toggle chevron -->
      <button v-if="hasChildren" @click="open = !open"
        class="w-5 h-5 flex items-center justify-center text-slate-400 hover:text-slate-600 flex-shrink-0 transition-transform"
        :class="open ? '' : '-rotate-90'">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>
      <span v-else class="w-5 flex-shrink-0"></span>

      <!-- Type icon -->
      <div :class="['w-6 h-6 rounded flex items-center justify-center flex-shrink-0', depthStyle.icon]">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" :d="typeIconPath"/>
        </svg>
      </div>

      <!-- Label -->
      <span :class="['font-medium text-sm flex-shrink-0', depthStyle.text]">{{ node.name }}</span>
      <code :class="['text-[11px] font-mono px-1.5 py-0.5 rounded flex-shrink-0', depthStyle.code]">{{ node.code }}</code>
      <span :class="['text-[11px] flex-shrink-0', depthStyle.typeLbl]">{{ node.type }}</span>

      <span class="flex-1 min-w-0"></span>

      <!-- Actions (shown on hover) -->
      <div class="flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
        <button v-if="depth < 2" @click.stop="$emit('add-child', node)"
          :class="['icon-btn text-xs font-medium flex items-center gap-1 px-2 h-6 rounded-md', depthStyle.addBtn]"
          :title="`Add ${childTypeLabel}`">
          <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
          </svg>
          {{ childTypeLabel }}
        </button>
        <button @click.stop="$emit('edit', node)" class="icon-btn-gray" title="Edit">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
          </svg>
        </button>
        <button @click.stop="$emit('delete', node)" class="icon-btn-red" title="Delete">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Children with indentation guide -->
    <div v-if="open && hasChildren" class="ml-5 pl-3 border-l-2 border-slate-100 mt-1 space-y-1">
      <LocationNode
        v-for="child in children"
        :key="child.id"
        :node="child"
        :depth="depth + 1"
        @add-child="$emit('add-child', $event)"
        @edit="$emit('edit', $event)"
        @delete="$emit('delete', $event)"
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

import { ref, computed } from 'vue'

const props = defineProps({
  node:  { type: Object, required: true },
  depth: { type: Number, default: 0 },
})

defineEmits(['add-child', 'edit', 'delete'])

const open = ref(true)

const children = computed(() => props.node.children_deep ?? props.node.childrenDeep ?? [])
const hasChildren = computed(() => children.value.length > 0)

const childTypeLabel = computed(() => props.depth === 0 ? 'Shelf' : 'Column')

const typeIconPath = computed(() => {
  if (props.node.type === 'row')
    return 'M4 6h16M4 12h16M4 18h7'
  if (props.node.type === 'shelf')
    return 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'
  return 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'
})

const depthStyles = [
  {
    hover:  'hover:bg-blue-50',
    icon:   'bg-blue-100 text-blue-600',
    text:   'text-slate-900',
    code:   'bg-blue-50 text-blue-600 border border-blue-200',
    typeLbl:'text-blue-400',
    addBtn: 'bg-amber-50 text-amber-700 hover:bg-amber-100',
  },
  {
    hover:  'hover:bg-amber-50',
    icon:   'bg-amber-100 text-amber-600',
    text:   'text-slate-800',
    code:   'bg-amber-50 text-amber-600 border border-amber-200',
    typeLbl:'text-amber-400',
    addBtn: 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100',
  },
  {
    hover:  'hover:bg-emerald-50',
    icon:   'bg-emerald-100 text-emerald-600',
    text:   'text-slate-700',
    code:   'bg-emerald-50 text-emerald-600 border border-emerald-200',
    typeLbl:'text-emerald-400',
    addBtn: '',
  },
]

const depthStyle = computed(() => depthStyles[props.depth] ?? depthStyles[2])
</script>
