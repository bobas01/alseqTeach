export const APP_TIMEZONE = 'America/Sao_Paulo'

export function todayKey(timeZone = APP_TIMEZONE): string {
  return dateKeyFromInstant(new Date(), timeZone)
}

export function addDays(isoDate: string, days: number): string {
  const [year, month, day] = isoDate.split('-').map(Number)
  const utc = new Date(Date.UTC(year, month - 1, day + days))
  return utc.toISOString().slice(0, 10)
}

export function dateKeyFromInstant(value: Date | string, timeZone = APP_TIMEZONE): string {
  return new Intl.DateTimeFormat('en-CA', {
    timeZone,
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  }).format(typeof value === 'string' ? new Date(value) : value)
}

export function formatMoney(centavos: number, currency = 'BRL'): string {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency }).format(centavos / 100)
}

export function formatTime(iso: string, timeZone = APP_TIMEZONE): string {
  return new Intl.DateTimeFormat('pt-BR', {
    timeZone,
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(iso))
}

export function formatDayChip(isoDate: string): { weekday: string; day: string } {
  const date = civilDate(isoDate)
  return {
    weekday: new Intl.DateTimeFormat('pt-BR', { weekday: 'short', timeZone: 'UTC' }).format(date).replace('.', ''),
    day: new Intl.DateTimeFormat('pt-BR', { day: '2-digit', timeZone: 'UTC' }).format(date),
  }
}

export function formatLongDate(iso: string, timeZone = APP_TIMEZONE): string {
  return new Intl.DateTimeFormat('pt-BR', {
    timeZone,
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(iso))
}

export function remainingLabel(expiresAt: string | null, now = Date.now()): string | null {
  if (!expiresAt) {
    return null
  }
  const ms = new Date(expiresAt).getTime() - now
  if (ms <= 0) {
    return 'expirado'
  }
  const totalMinutes = Math.ceil(ms / 60_000)
  const minutes = totalMinutes % 60
  const hours = Math.floor(totalMinutes / 60)
  if (hours > 0) {
    return `${hours}h ${minutes.toString().padStart(2, '0')}min`
  }
  return `${minutes} min`
}

function civilDate(isoDate: string): Date {
  const [year, month, day] = isoDate.split('-').map(Number)
  return new Date(Date.UTC(year, month - 1, day, 12))
}
