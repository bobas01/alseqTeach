<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import BrandMark from './BrandMark.vue'

const route = useRoute()
const scrolled = ref(false)
const menuOpen = ref(false)

function onScroll() {
  scrolled.value = window.scrollY > 8
}

function closeMenu() {
  menuOpen.value = false
}

watch(() => route.fullPath, closeMenu)

onMounted(() => {
  onScroll()
  window.addEventListener('scroll', onScroll, { passive: true })
})

onUnmounted(() => {
  window.removeEventListener('scroll', onScroll)
})
</script>

<template>
  <header class="site-header" :class="{ 'is-scrolled': scrolled, 'is-open': menuOpen }">
    <div class="wrap">
      <RouterLink class="brand" to="/" aria-label="Alseq Teach">
        <BrandMark compact />
      </RouterLink>
      <nav class="nav" aria-label="Principal">
        <a href="/#aulas">Aulas</a>
        <a href="/#professor">O professor</a>
        <a href="/#funcionamento">Como funciona</a>
        <a href="/#agenda">Agenda</a>
        <RouterLink to="/faq">Perguntas</RouterLink>
      </nav>
      <a class="btn btn-primary header-cta" href="/#agenda">Reservar</a>
      <button class="menu-btn" type="button" :aria-expanded="menuOpen" aria-controls="mobile-nav" @click="menuOpen = !menuOpen">
        {{ menuOpen ? 'Fechar' : 'Menu' }}
      </button>
    </div>
    <nav v-if="menuOpen" id="mobile-nav" class="mobile-nav" aria-label="Menu">
      <a href="/#aulas" @click="closeMenu">Aulas</a>
      <a href="/#professor" @click="closeMenu">O professor</a>
      <a href="/#funcionamento" @click="closeMenu">Como funciona</a>
      <a href="/#agenda" @click="closeMenu">Agenda</a>
      <RouterLink to="/faq" @click="closeMenu">Perguntas</RouterLink>
      <a class="btn btn-primary" href="/#agenda" @click="closeMenu">Reservar horário</a>
    </nav>
  </header>
</template>
