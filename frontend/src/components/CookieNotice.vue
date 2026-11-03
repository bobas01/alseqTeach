<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'

const STORAGE_KEY = 'alseq-cookie-ack'
const visible = ref(false)

function accept() {
  localStorage.setItem(STORAGE_KEY, '1')
  visible.value = false
}

onMounted(() => {
  visible.value = localStorage.getItem(STORAGE_KEY) !== '1'
})
</script>

<template>
  <div v-if="visible" class="cookie-notice" role="dialog" aria-label="Aviso de cookies">
    <p>
      Só usamos cookies e dados locais necessários para a reserva, o Pix e este aviso.
      Não há anúncios.
      <RouterLink to="/privacidade">Política de Privacidade</RouterLink>
    </p>
    <button class="btn btn-primary" type="button" @click="accept">Entendi</button>
  </div>
</template>
