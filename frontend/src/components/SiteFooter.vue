<script setup lang="ts">
import { RouterLink } from 'vue-router'
import BrandMark from './BrandMark.vue'
import { legalLinks } from '../content/legal'
import { documentLine, supplier } from '../content/supplier'
</script>

<template>
  <footer class="site-footer">
    <div class="wrap">
      <div class="footer-brand">
        <BrandMark />
        <p>Aulas particulares de francês, pagas por Pix, no fuso de Brasília.</p>
      </div>
      <div class="footer-legal">
        <p>
          <strong>{{ supplier.name }}</strong>
          · {{ supplier.role }}
        </p>
        <p>{{ documentLine() }}</p>
        <p>{{ supplier.address }}</p>
        <p>
          <a :href="`mailto:${supplier.email}`">{{ supplier.email }}</a>
          <template v-if="supplier.whatsapp">
            · WhatsApp {{ supplier.whatsapp }}
          </template>
        </p>
        <p>
          Atendimento eletrônico para dúvida, reclamação ou cancelamento.
          Resposta em até 5 dias úteis.
        </p>
      </div>
      <nav class="footer-nav" aria-label="Legal">
        <RouterLink v-for="link in legalLinks" :key="link.slug" :to="{ name: link.slug }">
          {{ link.label }}
        </RouterLink>
        <RouterLink to="/faq">Perguntas frequentes</RouterLink>
        <a href="/#agenda">Reservar horário</a>
        <RouterLink to="/admin">Agenda do professor</RouterLink>
      </nav>
    </div>
  </footer>
</template>
