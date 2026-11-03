<script setup lang="ts">
import { computed } from 'vue'
import { RouterView, useRoute } from 'vue-router'
import SiteHeader from './components/SiteHeader.vue'
import SiteFooter from './components/SiteFooter.vue'
import FaqAssistant from './components/FaqAssistant.vue'
import CookieNotice from './components/CookieNotice.vue'

const route = useRoute()
const inLesson = computed(() => route.name === 'lesson')
const inAdmin = computed(() => route.name === 'admin')
</script>

<template>
  <div class="page" :class="{ 'is-lesson': inLesson }">
    <SiteHeader />
    <main>
      <RouterView />
    </main>
    <SiteFooter v-if="!inLesson && !inAdmin" />
    <CookieNotice v-if="!inLesson && !inAdmin" />
    <FaqAssistant v-if="!inLesson && !inAdmin" />
  </div>
</template>
