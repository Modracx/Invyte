<template>
  <span class="badge" :class="cls">{{ label }}</span>
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
const props = defineProps({ qty: { type: Number, required: true }, threshold: { type: Number, default: 10 } })
const cls = computed(() => props.qty === 0 ? 'badge-red' : props.qty <= props.threshold ? 'badge-yellow' : 'badge-green')
const label = computed(() => props.qty === 0 ? 'Out of Stock' : props.qty <= props.threshold ? `Low (${props.qty})` : `${props.qty}`)
</script>
