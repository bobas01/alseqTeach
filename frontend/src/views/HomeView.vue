<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import HeroSection from '../components/HeroSection.vue'
import TeacherIntro from '../components/TeacherIntro.vue'
import CourseHighlights from '../components/CourseHighlights.vue'
import BookingCalendar from '../components/BookingCalendar.vue'
import BookingForm from '../components/BookingForm.vue'
import { fetchConfig } from '../api/client'
import type { PublicConfig, Slot } from '../api/types'

const config = ref<PublicConfig | null>(null)
const selectedSlot = ref<Slot | null>(null)
const configError = ref('')

onMounted(async () => {
  try {
    config.value = await fetchConfig()
  } catch (cause) {
    configError.value = cause instanceof Error ? cause.message : 'API indisponível.'
  }
})
</script>

<template>
  <HeroSection
    :price-centavos="config?.priceCentavos ?? 8000"
    :duration-minutes="config?.lessonDurationMinutes ?? 50"
  />
  <TeacherIntro />
  <CourseHighlights />
  <p v-if="configError" class="wrap banner-error">{{ configError }}</p>
  <section class="section wrap" id="agenda">
    <div class="section-head">
      <p class="kicker">Reserva</p>
      <h2>Escolha o dia e confirme com Pix.</h2>
      <p>Os horários já ocupados, bloqueados ou em pagamento não aparecem na grade. Para o mesmo dia, o professor confirma o horário antes do Pix.</p>
    </div>
    <div class="booking-grid">
      <BookingCalendar v-model="selectedSlot" />
      <BookingForm :selected-slot="selectedSlot" />
    </div>
    <p class="muted">Dúvidas de Pix ou da sala? Veja as <RouterLink to="/faq">perguntas frequentes</RouterLink>.</p>
  </section>
</template>
