import { dateKeyFromInstant, todayKey } from './format'

export function isSameLocalDay(iso: string): boolean {
  return dateKeyFromInstant(iso) === todayKey()
}
