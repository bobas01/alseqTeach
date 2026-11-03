<script setup lang="ts">
import { nextTick, onMounted, onUnmounted, ref } from 'vue'
import { aulaFundoDataUrl, AULA_FUNDO } from '../lib/background'
import { loadJitsiApi, meetingParts } from '../lib/jitsi'

const props = defineProps<{
  meetingUrl: string
  displayName: string
}>()

const host = ref<HTMLDivElement | null>(null)
const error = ref('')
let api: JitsiMeetExternalAPI | null = null

onMounted(async () => {
  await nextTick()
  const parts = meetingParts(props.meetingUrl)
  if (!parts || !host.value) {
    error.value = 'A sala de vídeo não está disponível.'
    return
  }

  try {
    await loadJitsiApi(parts.domain)
    if (!window.JitsiMeetExternalAPI || !host.value) {
      throw new Error('api')
    }
    api = new window.JitsiMeetExternalAPI(parts.domain, {
      roomName: parts.roomName,
      parentNode: host.value,
      width: '100%',
      height: '100%',
      lang: 'ptBR',
      userInfo: { displayName: props.displayName },
      configOverwrite: {
        disableDeepLinking: true,
        prejoinPageEnabled: true,
        disableInviteFunctions: true,
        useHostPageLocalStorage: true,
        virtualBackgrounds: [new URL(AULA_FUNDO, window.location.origin).href],
      },
      interfaceConfigOverwrite: {
        MOBILE_APP_PROMO: false,
        SHOW_JITSI_WATERMARK: false,
        HIDE_INVITE_MORE_HEADER: true,
      },
    })
    api.addListener('videoConferenceJoined', () => {
      void aulaFundoDataUrl()
        .then((dataUrl) => api?.executeCommand('setVirtualBackground', true, dataUrl))
        .catch(() => undefined)
    })
  } catch {
    error.value = 'Não foi possível abrir a sala neste navegador. Tente de novo, ou use o link direto.'
  }
})

onUnmounted(() => {
  api?.dispose()
})
</script>

<template>
  <div class="jitsi-shell">
    <p v-if="error" class="banner-error">
      {{ error }}
      <a :href="meetingUrl" target="_blank" rel="noreferrer">Abrir em outra aba</a>
    </p>
    <div ref="host" class="jitsi-host" />
  </div>
</template>
