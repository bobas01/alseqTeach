export function meetingParts(url: string): { domain: string; roomName: string } | null {
  try {
    const parsed = new URL(url)
    const roomName = parsed.pathname.replace(/^\//, '')
    if (!roomName) {
      return null
    }
    return { domain: parsed.hostname, roomName }
  } catch {
    return null
  }
}

export function loadJitsiApi(domain: string): Promise<void> {
  if (window.JitsiMeetExternalAPI) {
    return Promise.resolve()
  }

  return new Promise((resolve, reject) => {
    const script = document.createElement('script')
    script.src = `https://${domain}/external_api.js`
    script.async = true
    script.onload = () => resolve()
    script.onerror = () => reject(new Error('Não foi possível carregar a sala de vídeo.'))
    document.head.appendChild(script)
  })
}
