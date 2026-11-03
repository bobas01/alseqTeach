<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { legalDocuments, legalLinks, type LegalSlug } from '../content/legal'
import { supplier } from '../content/supplier'

const props = defineProps<{
  slug: LegalSlug
}>()

const doc = computed(() => legalDocuments[props.slug])
</script>

<template>
  <article class="section wrap legal-page">
    <div class="section-head">
      <p class="kicker">{{ doc.kicker }}</p>
      <h1>{{ doc.title }}</h1>
      <p>{{ doc.intro }}</p>
      <p class="muted">Atualizado em {{ supplier.updatedAtLabel }} · versão {{ supplier.legalVersion }}</p>
    </div>

    <nav class="legal-switch" aria-label="Documentos">
      <RouterLink
        v-for="link in legalLinks"
        :key="link.slug"
        :to="{ name: link.slug }"
        :class="{ 'is-current': link.slug === slug }"
      >
        {{ link.label }}
      </RouterLink>
    </nav>

    <section v-for="section in doc.sections" :key="section.title" class="legal-block">
      <h2>{{ section.title }}</h2>
      <p v-for="(paragraph, index) in section.paragraphs" :key="index">{{ paragraph }}</p>
    </section>
  </article>
</template>
