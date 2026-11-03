<script setup lang="ts">
import { formatMoney } from '../lib/format'

defineProps<{
  priceCentavos: number
  durationMinutes: number
}>()
</script>

<template>
  <section class="hero wrap" id="aulas">
    <div>
      <p class="kicker">Aulas particulares · online</p>
      <h1>Francês claro, vivo e no seu ritmo.</h1>
      <p class="lead">
        Conversação, gramática e pronúncia em aulas de {{ durationMinutes }} minutos,
        pensadas para o público brasileiro. Você escolhe o horário, paga com Pix e
        entra na aula neste mesmo site.
      </p>
      <div class="hero-actions">
        <a class="btn btn-primary" href="#agenda">Ver horários disponíveis</a>
        <a class="btn btn-ghost" href="#funcionamento">Como reservar</a>
      </div>
    </div>
    <aside class="hero-card">
      <dl>
        <div>
          <dt>Aula avulsa</dt>
          <dd>{{ formatMoney(priceCentavos) }}</dd>
        </div>
        <div>
          <dt>Duração</dt>
          <dd>{{ durationMinutes }} minutos</dd>
        </div>
        <div>
          <dt>Pagamento</dt>
          <dd>Pix · preço final</dd>
        </div>
        <div>
          <dt>Sala</dt>
          <dd>Vídeo no site</dd>
        </div>
      </dl>
    </aside>
  </section>
</template>
