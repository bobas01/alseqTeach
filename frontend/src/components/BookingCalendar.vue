<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { fetchSlots } from '../api/client'
import type { Slot } from '../api/types'
import { addDays, dateKeyFromInstant, formatDayChip, formatTime, todayKey } from '../lib/format'

const selectedSlot = defineModel<Slot | null>({ default: null })

const loading = ref(false)
const error = ref('')
const slots = ref<Slot[]>([])
const weekStart = ref(todayKey())
const selectedDay = ref(todayKey())
const today = todayKey()

const days = computed(() => Array.from({ length: 7 }, (_, index) => addDays(weekStart.value, index)))

const slotsByDay = computed(() => {
  const grouped = new Map<string, Slot[]>()
  for (const day of days.value) {
    grouped.set(day, [])
  }
  for (const slot of slots.value) {
    const key = dateKeyFromInstant(slot.startsAt)
    const list = grouped.get(key)
    if (list) {
      list.push(slot)
    }
  }
  return grouped
})

const daySlots = computed(() => slotsByDay.value.get(selectedDay.value) ?? [])
const canGoBack = computed(() => weekStart.value > today)

function pickDefaultDay() {
  const withSlots = days.value.find((day) => (slotsByDay.value.get(day)?.length ?? 0) > 0)
  selectedDay.value = withSlots ?? days.value[0] ?? today
}

async function load() {
  loading.value = true
  error.value = ''
  try {
    const data = await fetchSlots(weekStart.value, addDays(weekStart.value, 7))
    slots.value = data.slots
    if (selectedSlot.value && !data.slots.some((slot) => slot.id === selectedSlot.value?.id)) {
      selectedSlot.value = null
    }
    pickDefaultDay()
  } catch (cause) {
    error.value = cause instanceof Error ? cause.message : 'Não foi possível carregar os horários.'
  } finally {
    loading.value = false
  }
}

function previousWeek() {
  if (!canGoBack.value) {
    return
  }
  const previous = addDays(weekStart.value, -7)
  weekStart.value = previous < today ? today : previous
}

function nextWeek() {
  weekStart.value = addDays(weekStart.value, 7)
}

function selectSlot(slot: Slot) {
  selectedSlot.value = selectedSlot.value?.id === slot.id ? null : slot
  selectedDay.value = dateKeyFromInstant(slot.startsAt)
}

watch(weekStart, load)
onMounted(load)
</script>

<template>
  <div class="panel calendar">
    <div class="calendar-nav">
      <div>
        <p class="kicker">Agenda</p>
        <h2>Escolha um horário</h2>
      </div>
      <div>
        <button class="btn btn-ghost" type="button" :disabled="!canGoBack" @click="previousWeek">Anterior</button>
        <button class="btn btn-ghost" type="button" @click="nextWeek">Próxima</button>
      </div>
    </div>
    <p v-if="loading" class="muted">Carregando horários…</p>
    <p v-else-if="error" class="banner-error">{{ error }}</p>
    <template v-else>
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
              :class="{ 'is-selected': selectedSlot?.id === slot.id }"
              type="button"
              @click="selectSlot(slot)"
            >
              {{ formatTime(slot.startsAt) }}
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
            :class="{ 'is-selected': selectedSlot?.id === slot.id }"
            type="button"
            @click="selectSlot(slot)"
          >
            {{ formatTime(slot.startsAt) }}
          </button>
          <p v-if="!daySlots.length" class="muted">Nenhum horário neste dia.</p>
        </div>
      </div>
    </template>
  </div>
</template>
