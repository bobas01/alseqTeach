<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import {
  adminLogin,
  approveAdminBooking,
  blockAdminSlot,
  fetchAdminBookings,
  fetchAdminSlots,
  getAdminToken,
  rejectAdminBooking,
  setAdminToken,
  unblockAdminSlot,
} from '../api/client'
import type { AdminBooking, AdminSlot } from '../api/types'
import IntakeSummary from '../components/IntakeSummary.vue'
import { addDays, dateKeyFromInstant, formatDayChip, formatLongDate, formatMoney, formatTime, todayKey } from '../lib/format'

const password = ref('')
const token = ref(getAdminToken())
const loginError = ref('')
const loggingIn = ref(false)
const tab = ref<'agenda' | 'pedidos'>('agenda')
const loading = ref(false)
const error = ref('')
const bookings = ref<AdminBooking[]>([])
const slots = ref<AdminSlot[]>([])
const weekStart = ref(todayKey())
const selectedDay = ref(todayKey())
const selectedSlotId = ref<number | null>(null)
const today = todayKey()
const actingId = ref<string | number | null>(null)

const days = computed(() => Array.from({ length: 7 }, (_, index) => addDays(weekStart.value, index)))
const canGoBack = computed(() => weekStart.value > today)
const awaiting = computed(() => bookings.value.filter((item) => item.status === 'awaiting_teacher'))
const upcomingPaid = computed(() => bookings.value.filter((item) => item.status === 'paid'))
const waitingPix = computed(() => bookings.value.filter((item) => item.status === 'pending'))
const slotsByDay = computed(() => {
  const grouped = new Map<string, AdminSlot[]>()
  for (const day of days.value) {
    grouped.set(day, [])
  }
  for (const slot of slots.value) {
    grouped.get(dateKeyFromInstant(slot.startsAt))?.push(slot)
  }
  return grouped
})
const daySlots = computed(() => slotsByDay.value.get(selectedDay.value) ?? [])
const selectedSlot = computed(() => slots.value.find((slot) => slot.id === selectedSlotId.value) ?? null)

async function login() {
  loginError.value = ''
  loggingIn.value = true
  try {
    const result = await adminLogin(password.value)
    setAdminToken(result.token)
    token.value = result.token
    password.value = ''
    tab.value = 'agenda'
    await load()
    if (awaiting.value.length > 0) {
      tab.value = 'pedidos'
    }
  } catch (cause) {
    loginError.value = cause instanceof Error ? cause.message : 'Não foi possível entrar.'
  } finally {
    loggingIn.value = false
  }
}

function logout() {
  setAdminToken(null)
  token.value = null
  selectedSlotId.value = null
}

async function load() {
  if (!token.value) {
    return
  }
  loading.value = true
  error.value = ''
  try {
    const [agendaData, week] = await Promise.all([
      fetchAdminBookings(),
      fetchAdminSlots(weekStart.value, addDays(weekStart.value, 7)),
    ])
    bookings.value = agendaData.bookings
    slots.value = week.slots
    if (selectedSlotId.value && !week.slots.some((slot) => slot.id === selectedSlotId.value)) {
      selectedSlotId.value = null
    }
  } catch (cause) {
    error.value = cause instanceof Error ? cause.message : 'Não foi possível carregar o admin.'
    if (!getAdminToken()) {
      token.value = null
    }
  } finally {
    loading.value = false
  }
}

async function approve(booking: AdminBooking) {
  actingId.value = booking.id
  try {
    await approveAdminBooking(booking.id)
    await load()
  } catch (cause) {
    error.value = cause instanceof Error ? cause.message : 'Não foi possível confirmar.'
  } finally {
    actingId.value = null
  }
}

async function reject(booking: AdminBooking) {
  actingId.value = booking.id
  try {
    await rejectAdminBooking(booking.id)
    await load()
  } catch (cause) {
    error.value = cause instanceof Error ? cause.message : 'Não foi possível recusar.'
  } finally {
    actingId.value = null
  }
}

async function onSlotClick(slot: AdminSlot) {
  selectedSlotId.value = slot.id
  selectedDay.value = dateKeyFromInstant(slot.startsAt)
  if (slot.status !== 'available' && slot.status !== 'blocked') {
    return
  }
  actingId.value = slot.id
  try {
    const updated = slot.status === 'blocked' ? await unblockAdminSlot(slot.id) : await blockAdminSlot(slot.id)
    slots.value = slots.value.map((item) => (item.id === updated.id ? updated : item))
  } catch (cause) {
    error.value = cause instanceof Error ? cause.message : 'Não foi possível atualizar o horário.'
  } finally {
    actingId.value = null
  }
}

function previousWeek() {
  const previous = addDays(weekStart.value, -7)
  weekStart.value = previous < today ? today : previous
}

function slotClass(slot: AdminSlot) {
  return {
    'is-selected': selectedSlotId.value === slot.id,
    'is-blocked': slot.status === 'blocked',
    'is-booked': slot.status === 'booked',
    'is-held': slot.status === 'held',
  }
}

function slotLabel(slot: AdminSlot) {
  const time = formatTime(slot.startsAt)
  if (slot.booking) {
    return `${time} · ${slot.booking.studentName}`
  }
  if (slot.status === 'blocked') {
    return `${time} · bloqueado`
  }
  return time
}

function statusLabel(status: string) {
  if (status === 'paid') {
    return 'Paga'
  }
  if (status === 'pending') {
    return 'Aguardando Pix'
  }
  if (status === 'awaiting_teacher') {
    return 'Esperando você'
  }
  return status
}

watch(weekStart, () => {
  if (token.value) {
    void load()
  }
})
onMounted(load)
</script>

<template>
  <section class="checkout wrap admin-page">
    <p class="kicker">Professor</p>
    <h1>Sua agenda</h1>

    <form v-if="!token" class="panel form" @submit.prevent="login">
      <p>Entre para ver os alunos, confirmar se você está disponível e bloquear horários livres.</p>
      <label>
        Senha
        <input v-model="password" type="password" autocomplete="current-password" required>
      </label>
      <p v-if="loginError" class="banner-error">{{ loginError }}</p>
      <button class="btn btn-primary" type="submit" :disabled="loggingIn">
        {{ loggingIn ? 'Entrando…' : 'Entrar na agenda' }}
      </button>
    </form>

    <template v-else>
      <div class="admin-toolbar">
        <button class="btn" :class="tab === 'agenda' ? 'btn-primary' : 'btn-ghost'" type="button" @click="tab = 'agenda'">
          Agenda
        </button>
        <button class="btn" :class="tab === 'pedidos' ? 'btn-primary' : 'btn-ghost'" type="button" @click="tab = 'pedidos'">
          Pedidos{{ awaiting.length ? ` (${awaiting.length})` : '' }}
        </button>
        <button class="btn btn-ghost" type="button" @click="load">Atualizar</button>
        <button class="btn btn-ghost" type="button" @click="logout">Sair</button>
      </div>
      <p v-if="error" class="banner-error">{{ error }}</p>
      <p v-else-if="loading" class="muted">Carregando…</p>

      <p v-if="tab === 'agenda' && awaiting.length" class="notice">
        {{ awaiting.length === 1 ? '1 pedido de hoje espera sua confirmação.' : `${awaiting.length} pedidos de hoje esperam sua confirmação.` }}
        <button class="btn btn-ghost" type="button" @click="tab = 'pedidos'">Ver pedidos</button>
      </p>

      <div v-if="tab === 'pedidos'" class="panel">
        <h2>Você está disponível?</h2>
        <p class="muted">Se confirmar, o aluno recebe um e-mail com o valor e o Pix para garantir a aula.</p>
        <article v-for="booking in awaiting" :key="booking.id" class="admin-request">
          <p><strong>{{ booking.studentName }}</strong></p>
          <p>{{ formatLongDate(booking.slot.startsAt) }} · {{ formatMoney(booking.amountCentavos, booking.currency) }}</p>
          <p class="muted">{{ booking.studentEmail }}{{ booking.studentPhone ? ` · ${booking.studentPhone}` : '' }}</p>
          <IntakeSummary :intake="booking.intake" />
          <div class="hero-actions">
            <button class="btn btn-primary" type="button" :disabled="actingId === booking.id" @click="approve(booking)">
              Estou disponível
            </button>
            <button class="btn btn-ghost" type="button" :disabled="actingId === booking.id" @click="reject(booking)">
              Não posso
            </button>
          </div>
        </article>
        <p v-if="!awaiting.length" class="muted">Nenhum pedido esperando confirmação.</p>
      </div>

      <template v-else>
        <div class="panel calendar">
          <div class="calendar-nav">
            <div>
              <p class="kicker">Semana</p>
              <h2>Alunos e horários</h2>
            </div>
            <div>
              <button class="btn btn-ghost" type="button" :disabled="!canGoBack" @click="previousWeek">Anterior</button>
              <button class="btn btn-ghost" type="button" @click="weekStart = addDays(weekStart, 7)">Próxima</button>
            </div>
          </div>
          <p class="muted">Horário com nome = aluno na sua grade. Toque num livre para bloqueá-lo; toque de novo para liberar.</p>
          <div class="days days-desktop">
            <div v-for="day in days" :key="day" class="day">
              <div class="day-label">
                <span>{{ formatDayChip(day).weekday }}</span>
                <strong>{{ formatDayChip(day).day }}</strong>
              </div>
              <div class="slots">
                <button
                  v-for="slot in slotsByDay.get(day)"
                  :key="slot.id"
                  class="slot"
                  :class="slotClass(slot)"
                  type="button"
                  :disabled="actingId === slot.id"
                  @click="onSlotClick(slot)"
                >
                  {{ slotLabel(slot) }}
                </button>
                <p v-if="!slotsByDay.get(day)?.length" class="empty-day">—</p>
              </div>
            </div>
          </div>
          <div class="days-mobile">
            <div class="day-chips" role="tablist">
              <button
                v-for="day in days"
                :key="day"
                class="day-chip"
                :class="{ 'is-selected': selectedDay === day }"
                type="button"
                @click="selectedDay = day"
              >
                <span>{{ formatDayChip(day).weekday }}</span>
                <strong>{{ formatDayChip(day).day }}</strong>
              </button>
            </div>
            <div class="slot-list">
              <button
                v-for="slot in daySlots"
                :key="slot.id"
                class="slot slot-wide"
                :class="slotClass(slot)"
                type="button"
                :disabled="actingId === slot.id"
                @click="onSlotClick(slot)"
              >
                {{ slotLabel(slot) }}
              </button>
              <p v-if="!daySlots.length" class="muted">Nenhum horário neste dia.</p>
            </div>
          </div>
        </div>

        <article v-if="selectedSlot" class="panel">
          <p class="kicker">Horário</p>
          <h2>{{ formatLongDate(selectedSlot.startsAt) }}</h2>
          <template v-if="selectedSlot.booking">
            <p><strong>{{ selectedSlot.booking.studentName }}</strong> · {{ statusLabel(selectedSlot.booking.status) }}</p>
            <p class="muted">
              {{ selectedSlot.booking.studentEmail }}
              {{ selectedSlot.booking.studentPhone ? ` · ${selectedSlot.booking.studentPhone}` : '' }}
              · {{ formatMoney(selectedSlot.booking.amountCentavos, selectedSlot.booking.currency) }}
            </p>
            <IntakeSummary :intake="selectedSlot.booking.intake" />
            <div class="hero-actions">
              <RouterLink
                v-if="selectedSlot.booking.meetingUrl"
                class="btn btn-primary"
                :to="{ name: 'lesson', params: { id: selectedSlot.booking.id } }"
              >
                Entrar na aula
              </RouterLink>
              <a
                v-if="selectedSlot.booking.teacherCalendar"
                class="btn btn-ghost"
                :href="selectedSlot.booking.teacherCalendar.googleUrl"
                target="_blank"
                rel="noreferrer"
              >
                Google Agenda
              </a>
              <a
                v-if="selectedSlot.booking.teacherCalendar"
                class="btn btn-ghost"
                :href="selectedSlot.booking.teacherCalendar.icsUrl"
              >
                Baixar .ics
              </a>
            </div>
          </template>
          <p v-else-if="selectedSlot.status === 'blocked'" class="muted">Você bloqueou este horário. Toque de novo na grade para liberá-lo.</p>
          <p v-else class="muted">Horário livre. Toque de novo para bloqueá-lo se você não estiver disponível.</p>
        </article>

        <div class="panel">
          <h2>Próximas aulas</h2>
          <p class="muted">Quem já pagou e está na sua grade.</p>
          <article v-for="booking in upcomingPaid" :key="booking.id" class="admin-request">
            <p><strong>{{ booking.studentName }}</strong> · {{ formatLongDate(booking.slot.startsAt) }}</p>
            <p class="muted">{{ booking.studentEmail }}{{ booking.studentPhone ? ` · ${booking.studentPhone}` : '' }}</p>
            <IntakeSummary :intake="booking.intake" />
            <div class="hero-actions">
              <RouterLink class="btn btn-primary" :to="{ name: 'lesson', params: { id: booking.id } }">
                Entrar na aula
              </RouterLink>
              <a
                v-if="booking.teacherCalendar"
                class="btn btn-ghost"
                :href="booking.teacherCalendar.googleUrl"
                target="_blank"
                rel="noreferrer"
              >
                Google Agenda
              </a>
            </div>
          </article>
          <p v-if="waitingPix.length" class="muted">
            {{ waitingPix.length === 1 ? '1 aluno ainda está no Pix.' : `${waitingPix.length} alunos ainda estão no Pix.` }}
          </p>
          <p v-if="!upcomingPaid.length && !waitingPix.length" class="muted">Nenhuma aula confirmada nesta agenda.</p>
        </div>
      </template>
    </template>
  </section>
</template>
