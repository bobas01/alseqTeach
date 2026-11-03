<script setup lang="ts">
import type { LessonIntake } from '../api/types'

defineProps<{
  intake: LessonIntake | null | undefined
}>()
</script>

<template>
  <div v-if="intake" class="intake-summary">
    <p v-if="intake.firstLesson"><strong>Primeira aula</strong></p>
    <p v-if="intake.levelLabel">Nível: {{ intake.levelLabel }}</p>
    <p v-if="intake.goalLabels.length">Quer trabalhar: {{ intake.goalLabels.join(' · ') }}</p>
    <p v-if="intake.note">{{ intake.note }}</p>
  </div>
</template>
