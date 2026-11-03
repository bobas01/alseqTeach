<script setup lang="ts">
defineProps<{
  compact?: boolean
}>()
</script>

<template>
  <span class="brand-mark" :class="{ 'is-compact': compact }">
    <span class="brand-name">
      <svg class="brand-chevron" viewBox="4 4 112 92" aria-hidden="true">
        <path fill="currentColor" d="M60 4 L116 96 L88 96 L60 46 L32 96 L4 96 Z" />
      </svg>
      <span>LSEQ</span>
    </span>
    <span class="brand-sub">TEACH</span>
  </span>
</template>
