<script setup lang="ts">
import { faqItems } from '../content/faq'
</script>

<template>
  <section class="section wrap faq-page">
    <div class="section-head">
      <p class="kicker">Perguntas</p>
      <h1>Dúvidas frequentes</h1>
      <p>Respostas curtas sobre reserva, Pix e a aula ao vivo. O assistente no canto da tela usa as mesmas informações.</p>
    </div>
    <div class="faq-list">
      <details v-for="item in faqItems" :key="item.id" class="panel faq-item">
        <summary>{{ item.question }}</summary>
        <p>{{ item.answer }}</p>
      </details>
    </div>
  </section>
</template>
