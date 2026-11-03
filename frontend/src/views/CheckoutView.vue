<script setup lang="ts">
import PixCheckout from '../components/PixCheckout.vue'

defineProps<{
  id: string
}>()
</script>

<template>
  <PixCheckout :booking-id="id" />
</template>
