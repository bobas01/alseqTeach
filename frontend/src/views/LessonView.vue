<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import JitsiRoom from '../components/JitsiRoom.vue'
import { fetchBooking } from '../api/client'
import type { Booking } from '../api/types'
import { formatLongDate } from '../lib/format'

const props = defineProps<{
  id: string
}>()

const booking = ref<Booking | null>(null)
const error = ref('')
const now = ref(Date.now())
let clock = 0

const paid = computed(() => booking.value?.status === 'paid' && Boolean(booking.value.meetingUrl))
const startsAt = computed(() => (booking.value ? new Date(booking.value.slot.startsAt).getTime() : 0))
const notStarted = computed(() => paid.value && startsAt.value > now.value)
const waitLabel = computed(() => {
  const ms = startsAt.value - now.value
  if (ms <= 0) {
    return null
  }
  const minutes = Math.ceil(ms / 60_000)
  if (minutes >= 60) {
    const hours = Math.floor(minutes / 60)
    return `${hours}h ${String(minutes % 60).padStart(2, '0')}min`
  }
  return `${minutes} min`
})

onMounted(async () => {
  try {
    booking.value = await fetchBooking(props.id)
  } catch (cause) {
    error.value = cause instanceof Error ? cause.message : 'Reserva não encontrada.'
  }
  clock = window.setInterval(() => {
    now.value = Date.now()
  }, 1000)
})

onUnmounted(() => {
  window.clearInterval(clock)
})
</script>

<template>
  <section class="lesson wrap">
    <p v-if="error" class="banner-error">{{ error }}</p>
    <template v-else-if="booking && paid">
      <header class="lesson-head">
        <p class="kicker">Aula ao vivo</p>
        <h1>{{ formatLongDate(booking.slot.startsAt) }}</h1>
        <p class="muted">{{ booking.studentName }} · 50 minutos · câmera e microfone no navegador</p>
        <p v-if="notStarted && waitLabel" class="muted">A aula começa em {{ waitLabel }}. Você já pode testar a sala.</p>
      </header>
      <JitsiRoom :meeting-url="booking.meetingUrl!" :display-name="booking.studentName" />
    </template>
    <div v-else-if="booking" class="panel">
      <p>A sala só abre depois do Pix confirmado.</p>
      <p><RouterLink class="btn btn-primary" :to="{ name: 'checkout', params: { id: booking.id } }">Ir para o pagamento</RouterLink></p>
    </div>
    <p v-else class="muted">Preparando a sala…</p>
  </section>
</template>
