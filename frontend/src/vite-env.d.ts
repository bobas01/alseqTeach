/// <reference types="vite/client" />

declare module '*.vue' {
  import type { DefineComponent } from 'vue'
  const component: DefineComponent<object, object, unknown>
  export default component
}

interface ImportMetaEnv {
  readonly VITE_API_BASE_URL: string
}

interface ImportMeta {
  readonly env: ImportMetaEnv
}

interface JitsiMeetExternalAPI {
  dispose: () => void
  addListener: (event: string, listener: () => void) => void
  executeCommand: (command: string, ...args: unknown[]) => void
}

interface Window {
  JitsiMeetExternalAPI?: new (
    domain: string,
    options: {
      roomName: string
      parentNode: HTMLElement
      width: string | number
      height: string | number
      lang?: string
      userInfo?: { displayName?: string }
      configOverwrite?: Record<string, unknown>
      interfaceConfigOverwrite?: Record<string, unknown>
    },
  ) => JitsiMeetExternalAPI
}
