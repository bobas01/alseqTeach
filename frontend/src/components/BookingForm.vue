<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { ApiError, createBooking, fetchIntake } from '../api/client'
import type { Slot } from '../api/types'
import { INTAKE_GOALS, INTAKE_LEVELS } from '../content/intake'
import { digitsOnly, isValidCpf, maskCpf } from '../lib/cpf'
import { formatLongDate, formatMoney } from '../lib/format'
import { isSameLocalDay } from '../lib/sameDay'

const props = defineProps<{
  selectedSlot: Slot | null
}>()

const router = useRouter()
const submitting = ref(false)
const formError = ref('')
const fieldErrors = ref<Record<string, string>>({})
const firstLesson = ref(true)
const form = reactive({
  studentName: '',
  studentEmail: '',
  studentPhone: '',
  studentCpf: '',
  frenchLevel: '',
  conversationGoals: [] as string[],
  intakeNote: '',
  acceptedTerms: false,
  acceptedPrivacy: false,
})

const canSubmit = computed(
  () =>
    props.selectedSlot !== null &&
    !submitting.value &&
    form.acceptedTerms &&
    form.acceptedPrivacy,
)
const sameDay = computed(() => props.selectedSlot !== null && isSameLocalDay(props.selectedSlot.startsAt))

function onCpfInput(event: Event) {
  const target = event.target as HTMLInputElement
  form.studentCpf = maskCpf(target.value)
}

function toggleGoal(id: string) {
  const index = form.conversationGoals.indexOf(id)
  if (index >= 0) {
    form.conversationGoals.splice(index, 1)
    return
  }
  form.conversationGoals.push(id)
}

async function submit() {
  formError.value = ''
  fieldErrors.value = {}
  if (!props.selectedSlot) {
    formError.value = 'Escolha um horário no calendário.'
    return
  }
  if (!isValidCpf(form.studentCpf)) {
    fieldErrors.value = { studentCpf: 'CPF inválido.' }
    return
  }
  if (firstLesson.value && form.frenchLevel === '') {
    fieldErrors.value = { frenchLevel: 'Informe seu nível.' }
    return
  }
  if (firstLesson.value && form.conversationGoals.length === 0) {
    fieldErrors.value = { conversationGoals: 'Escolha pelo menos um objetivo.' }
    return
  }
  if (!form.acceptedTerms) {
    fieldErrors.value = { acceptedTerms: 'Aceite os Termos de Uso para continuar.' }
    return
  }
  if (!form.acceptedPrivacy) {
    fieldErrors.value = { acceptedPrivacy: 'Confirme que leu a Política de Privacidade.' }
    return
  }

  submitting.value = true
  try {
    const booking = await createBooking({
      slotId: props.selectedSlot.id,
      studentName: form.studentName.trim(),
      studentEmail: form.studentEmail.trim(),
      studentPhone: form.studentPhone.trim() || undefined,
      studentCpf: digitsOnly(form.studentCpf),
      frenchLevel: form.frenchLevel || undefined,
      conversationGoals: form.conversationGoals,
      intakeNote: form.intakeNote.trim() || undefined,
      acceptedTerms: form.acceptedTerms,
      acceptedPrivacy: form.acceptedPrivacy,
    })
    await router.push({ name: 'checkout', params: { id: booking.id } })
  } catch (cause) {
    if (cause instanceof ApiError) {
      formError.value = cause.message
      fieldErrors.value = cause.fields ?? {}
    } else {
      formError.value = 'Não foi possível criar a reserva.'
    }
  } finally {
    submitting.value = false
  }
}

let intakeTimer = 0
watch(
  () => form.studentEmail,
  (email) => {
    window.clearTimeout(intakeTimer)
    if (!email.includes('@')) {
      firstLesson.value = true
      return
    }
    intakeTimer = window.setTimeout(async () => {
      try {
        const profile = await fetchIntake(email.trim())
        firstLesson.value = profile.firstLesson
      } catch {
        firstLesson.value = true
      }
    }, 400)
  },
)
</script>

<template>
  <form class="panel form" @submit.prevent="submit">
    <div>
      <p class="kicker">Seus dados</p>
      <h2>Confirmar reserva</h2>
    </div>
    <p v-if="selectedSlot" class="selected-slot">
      {{ formatLongDate(selectedSlot.startsAt) }} · {{ formatMoney(selectedSlot.priceCentavos) }}
    </p>
    <p v-else class="muted">Selecione um horário na agenda para continuar.</p>
    <p v-if="sameDay" class="notice">
      Aula para hoje: o professor confirma o horário antes do Pix. Você recebe um e-mail assim que for liberado.
    </p>

    <label>
      Nome completo
      <input v-model="form.studentName" type="text" autocomplete="name" required maxlength="120">
      <span v-if="fieldErrors.studentName" class="field-error">{{ fieldErrors.studentName }}</span>
    </label>
    <label>
      E-mail
      <input v-model="form.studentEmail" type="email" autocomplete="email" required maxlength="180">
      <span v-if="fieldErrors.studentEmail" class="field-error">{{ fieldErrors.studentEmail }}</span>
    </label>
    <label>
      WhatsApp (opcional)
      <input v-model="form.studentPhone" type="tel" autocomplete="tel" maxlength="30">
    </label>
    <label>
      CPF
      <input v-model="form.studentCpf" inputmode="numeric" autocomplete="off" required placeholder="000.000.000-00" @input="onCpfInput">
      <span v-if="fieldErrors.studentCpf" class="field-error">{{ fieldErrors.studentCpf }}</span>
    </label>

    <fieldset v-if="firstLesson" class="intake">
      <legend>Sua primeira aula</legend>
      <p class="muted">O professor usa isso para preparar o primeiro encontro: nível e o tipo de conversa que você quer.</p>

      <p class="intake-label">Qual é o seu nível?</p>
      <div class="choice-chips" role="radiogroup" aria-label="Nível de francês">
        <button
          v-for="level in INTAKE_LEVELS"
          :key="level.id"
          class="choice-chip"
          :class="{ 'is-selected': form.frenchLevel === level.id }"
          type="button"
          @click="form.frenchLevel = level.id"
        >
          {{ level.label }}
        </button>
      </div>
      <span v-if="fieldErrors.frenchLevel" class="field-error">{{ fieldErrors.frenchLevel }}</span>

      <p class="intake-label">O que você quer trabalhar?</p>
      <div class="choice-list">
        <label v-for="goal in INTAKE_GOALS" :key="goal.id" class="choice-check">
          <input type="checkbox" :checked="form.conversationGoals.includes(goal.id)" @change="toggleGoal(goal.id)">
          <span>{{ goal.label }}</span>
        </label>
      </div>
      <span v-if="fieldErrors.conversationGoals" class="field-error">{{ fieldErrors.conversationGoals }}</span>

      <label>
        Quer acrescentar alguma coisa? (opcional)
        <textarea v-model="form.intakeNote" rows="3" maxlength="600" placeholder="Ex.: preciso falar de contratos, ou só perder o medo de falar."></textarea>
      </label>
    </fieldset>

    <div v-if="selectedSlot" class="contract-summary">
      <p class="intake-label">Resumo da contratação</p>
      <ul>
        <li>Aula particular de francês, 50 minutos, ao vivo neste site.</li>
        <li>{{ formatLongDate(selectedSlot.startsAt) }} · {{ formatMoney(selectedSlot.priceCentavos) }} no Pix, sem taxa extra neste site.</li>
        <li>Você pode desistir em até 7 dias se a aula ainda não tiver ocorrido.</li>
        <li>Nome, e-mail e CPF são usados para a reserva e o pagamento.</li>
      </ul>
    </div>

    <label class="choice-check legal-check">
      <input v-model="form.acceptedTerms" type="checkbox">
      <span>
        Li e aceito os
        <RouterLink to="/termos" target="_blank">Termos de Uso</RouterLink>
        e a
        <RouterLink to="/cancelamento" target="_blank">Política de Cancelamento</RouterLink>.
        Tenho 18 anos ou estou representado por responsável.
      </span>
    </label>
    <span v-if="fieldErrors.acceptedTerms" class="field-error">{{ fieldErrors.acceptedTerms }}</span>

    <label class="choice-check legal-check">
      <input v-model="form.acceptedPrivacy" type="checkbox">
      <span>
        Estou ciente da
        <RouterLink to="/privacidade" target="_blank">Política de Privacidade</RouterLink>
        (LGPD) e do tratamento dos dados necessários à aula e ao Pix.
      </span>
    </label>
    <span v-if="fieldErrors.acceptedPrivacy" class="field-error">{{ fieldErrors.acceptedPrivacy }}</span>

    <p v-if="formError" class="banner-error">{{ formError }}</p>
    <button class="btn btn-primary" type="submit" :disabled="!canSubmit">
      {{ submitting ? (sameDay ? 'Enviando pedido…' : 'Gerando Pix…') : sameDay ? 'Pedir confirmação' : 'Ir para o Pix' }}
    </button>
  </form>
</template>
