<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { fetchBooking, simulatePayment } from '../api/client'
import type { Booking } from '../api/types'
import { atendimentoMailto } from '../content/supplier'
import { formatLongDate, formatMoney, remainingLabel } from '../lib/format'

const props = defineProps<{
  bookingId: string
}>()

const booking = ref<Booking | null>(null)
const error = ref('')
const copying = ref(false)
const simulating = ref(false)
const now = ref(Date.now())
let pollTimer = 0
let clockTimer = 0

const awaiting = computed(() => booking.value?.status === 'awaiting_teacher')
const pending = computed(() => booking.value?.status === 'pending')
const paid = computed(() => booking.value?.status === 'paid')
const holdLabel = computed(() => remainingLabel(booking.value?.expiresAt ?? null, now.value))
const shouldPoll = computed(() => awaiting.value || pending.value)

async function refresh() {
  try {
    booking.value = await fetchBooking(props.bookingId)
    error.value = ''
    if (!shouldPoll.value) {
      stopPolling()
    }
  } catch (cause) {
    error.value = cause instanceof Error ? cause.message : 'Não foi possível carregar a reserva.'
  }
}

function stopPolling() {
  window.clearInterval(pollTimer)
  pollTimer = 0
}

async function copyPix() {
  const code = booking.value?.pix?.copyPaste
  if (!code) {
    return
  }
  await navigator.clipboard.writeText(code)
  copying.value = true
  window.setTimeout(() => {
    copying.value = false
  }, 1600)
}

async function simulate() {
  simulating.value = true
  try {
    booking.value = await simulatePayment(props.bookingId)
    stopPolling()
  } catch (cause) {
    error.value = cause instanceof Error ? cause.message : 'Falha ao simular o pagamento.'
  } finally {
    simulating.value = false
  }
}

onMounted(async () => {
  await refresh()
  clockTimer = window.setInterval(() => {
    now.value = Date.now()
  }, 1000)
  pollTimer = window.setInterval(() => {
    if (shouldPoll.value) {
      void refresh()
    }
  }, 3000)
})

onUnmounted(() => {
  stopPolling()
  window.clearInterval(clockTimer)
})
</script>

<template>
  <section class="checkout wrap">
    <p v-if="error" class="banner-error">{{ error }}</p>
    <article v-else-if="booking" class="panel">
      <p class="kicker">{{ awaiting ? 'Pedido' : 'Pagamento' }}</p>
      <h1 v-if="awaiting">Aguardando o professor</h1>
      <h1 v-else-if="pending">Pague com Pix para confirmar a aula</h1>
      <h1 v-else-if="paid" class="status-ok">Reserva confirmada</h1>
      <h1 v-else>Esta reserva não está mais disponível</h1>

      <p>
        {{ booking.studentName }} · {{ formatLongDate(booking.slot.startsAt) }} ·
        {{ formatMoney(booking.amountCentavos, booking.currency) }}
      </p>
      <p v-if="(pending || awaiting) && holdLabel" class="muted">
        {{ awaiting ? 'O pedido vale até o horário da aula' : 'O horário fica reservado por mais' }}
        {{ awaiting ? ` (${holdLabel}).` : ` ${holdLabel}.` }}
      </p>

      <div v-if="awaiting" class="notice">
        <p>Como a aula é hoje, o professor precisa confirmar o horário. Você recebe um e-mail assim que o Pix for liberado — esta página também atualiza sozinha.</p>
      </div>

      <div v-else-if="pending && booking.pix">
        <img
          v-if="booking.pix.qrCodeBase64"
          class="qr"
          :src="`data:image/png;base64,${booking.pix.qrCodeBase64}`"
          alt="QR Code Pix"
        >
        <p v-if="booking.pix.copyPaste" class="pix-code">{{ booking.pix.copyPaste }}</p>
        <div class="hero-actions">
          <button v-if="booking.pix.copyPaste" class="btn btn-primary" type="button" @click="copyPix">
            {{ copying ? 'Código copiado' : 'Copiar código Pix' }}
          </button>
          <a v-if="booking.pix.ticketUrl" class="btn btn-ghost" :href="booking.pix.ticketUrl" target="_blank" rel="noreferrer">
            Abrir no Mercado Pago
          </a>
          <button v-if="booking.pix.simulated" class="btn btn-ghost" type="button" :disabled="simulating" @click="simulate">
            {{ simulating ? 'Confirmando…' : 'Simular pagamento (dev)' }}
          </button>
        </div>
        <p class="muted">A página verifica o pagamento automaticamente. Você também recebe a confirmação por e-mail, com o evento para a agenda.</p>
        <p class="muted">
          Pix processado pelo Mercado Pago. Depois de pago, o estorno — se devido — volta por um novo Pix.
          <RouterLink to="/cancelamento">Cancelamento e arrependimento</RouterLink>
        </p>
      </div>

      <div v-else-if="paid">
        <p>A reserva está confirmada. Guarde esta página: a sala de vídeo já está aberta aqui no site. Um e-mail de confirmação também foi enviado.</p>
        <div class="hero-actions">
          <RouterLink v-if="booking.meetingUrl" class="btn btn-primary" :to="{ name: 'lesson', params: { id: booking.id } }">
            Entrar na aula
          </RouterLink>
          <a v-if="booking.calendar" class="btn btn-ghost" :href="booking.calendar.googleUrl" target="_blank" rel="noreferrer">
            Google Agenda
          </a>
          <a v-if="booking.calendar" class="btn btn-ghost" :href="booking.calendar.icsUrl">
            Baixar .ics
          </a>
          <a
            class="btn btn-ghost"
            :href="atendimentoMailto(
              `Cancelamento da reserva ${booking.id}`,
              `Quero cancelar a reserva ${booking.id} (${booking.studentName}).`,
            )"
          >
            Pedir cancelamento
          </a>
        </div>
        <p class="muted">
          Direito de arrependimento: até 7 dias, se a aula ainda não ocorreu.
          <RouterLink to="/cancelamento">Ver regras</RouterLink>
        </p>
      </div>
    </article>
    <p v-else class="muted">Carregando reserva…</p>
  </section>
</template>
