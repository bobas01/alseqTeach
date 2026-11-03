<script setup lang="ts">
import { nextTick, ref } from 'vue'
import { faqFallback, faqItems } from '../content/faq'

interface ChatLine {
  from: 'bot' | 'you'
  text: string
}

const open = ref(false)
const input = ref('')
const lines = ref<ChatLine[]>([
  {
    from: 'bot',
    text: 'Olá. Posso ajudar com reserva, Pix ou a sala de vídeo. Escolha uma pergunta, ou escreva a sua.',
  },
])
const log = ref<HTMLElement | null>(null)

function normalize(value: string): string {
  return value
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
}

function replyTo(question: string) {
  const query = normalize(question)
  let best = faqFallback
  let score = 0
  for (const item of faqItems) {
    let points = 0
    if (normalize(item.question).includes(query) || query.includes(normalize(item.question).slice(0, 18))) {
      points += 4
    }
    for (const keyword of item.keywords) {
      if (query.includes(keyword)) {
        points += 2
      }
    }
    if (points > score) {
      score = points
      best = item.answer
    }
  }
  lines.value.push({ from: 'bot', text: score === 0 ? faqFallback : best })
  void nextTick(() => {
    log.value?.scrollTo({ top: log.value.scrollHeight, behavior: 'smooth' })
  })
}

function ask(question: string) {
  const text = question.trim()
  if (!text) {
    return
  }
  lines.value.push({ from: 'you', text })
  input.value = ''
  replyTo(text)
}

function submit() {
  ask(input.value)
}
</script>

<template>
  <div class="assistant">
    <button class="assistant-toggle" type="button" :aria-expanded="open" @click="open = !open">
      {{ open ? 'Fechar' : 'Dúvidas' }}
    </button>
    <section v-if="open" class="assistant-panel" aria-label="Assistente de dúvidas">
      <div ref="log" class="assistant-log">
        <p v-for="(line, index) in lines" :key="index" :class="line.from">{{ line.text }}</p>
      </div>
      <div class="assistant-chips">
        <button v-for="item in faqItems.slice(0, 4)" :key="item.id" type="button" @click="ask(item.question)">
          {{ item.question }}
        </button>
      </div>
      <form class="assistant-form" @submit.prevent="submit">
        <input v-model="input" type="text" maxlength="180" placeholder="Escreva sua dúvida" aria-label="Sua dúvida">
        <button class="btn btn-primary" type="submit">Enviar</button>
      </form>
    </section>
  </div>
</template>
