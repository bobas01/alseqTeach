import type {
  AdminBooking,
  AdminSlot,
  AdminSlotList,
  ApiErrorBody,
  Booking,
  CreateBookingPayload,
  PublicConfig,
  SlotList,
} from './types'

const baseUrl = import.meta.env.VITE_API_BASE_URL ?? ''
const ADMIN_TOKEN_KEY = 'alseq-admin-token'

export class ApiError extends Error {
  readonly status: number
  readonly code?: string
  readonly fields?: Record<string, string>

  constructor(status: number, body: ApiErrorBody) {
    super(body.message || 'Não foi possível concluir o pedido.')
    this.name = 'ApiError'
    this.status = status
    this.code = body.error
    this.fields = body.fields
  }
}

export function getAdminToken(): string | null {
  return localStorage.getItem(ADMIN_TOKEN_KEY)
}

export function setAdminToken(token: string | null): void {
  if (token) {
    localStorage.setItem(ADMIN_TOKEN_KEY, token)
    return
  }
  localStorage.removeItem(ADMIN_TOKEN_KEY)
}

async function request<T>(path: string, init: RequestInit = {}, admin = false): Promise<T> {
  const headers = new Headers(init.headers)
  if (init.body && !headers.has('Content-Type')) {
    headers.set('Content-Type', 'application/json')
  }
  if (admin) {
    const token = getAdminToken()
    if (token) {
      headers.set('X-Admin-Token', token)
    }
  }

  const response = await fetch(`${baseUrl}${path}`, { ...init, headers })
  const body = (await response.json().catch(() => ({}))) as ApiErrorBody & T
  if (!response.ok) {
    if (admin && response.status === 401) {
      setAdminToken(null)
    }
    throw new ApiError(response.status, body)
  }

  return body
}

export function fetchConfig(): Promise<PublicConfig> {
  return request('/api/config')
}

export function fetchIntake(email: string): Promise<{ firstLesson: boolean }> {
  const query = new URLSearchParams({ email })
  return request(`/api/intake?${query.toString()}`)
}

export function fetchSlots(from: string, to: string): Promise<SlotList> {
  const query = new URLSearchParams({ from, to })
  return request(`/api/slots?${query.toString()}`)
}

export function createBooking(payload: CreateBookingPayload): Promise<Booking> {
  return request('/api/bookings', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function fetchBooking(id: string): Promise<Booking> {
  return request(`/api/bookings/${encodeURIComponent(id)}`)
}

export function simulatePayment(id: string): Promise<Booking> {
  return request(`/api/dev/bookings/${encodeURIComponent(id)}/simulate-payment`, {
    method: 'POST',
  })
}

export function adminLogin(password: string): Promise<{ token: string }> {
  return request('/api/admin/login', {
    method: 'POST',
    body: JSON.stringify({ password }),
  })
}

export function fetchAdminSlots(from: string, to: string): Promise<AdminSlotList> {
  const query = new URLSearchParams({ from, to })
  return request(`/api/admin/slots?${query.toString()}`, {}, true)
}

export function blockAdminSlot(id: number): Promise<AdminSlot> {
  return request(`/api/admin/slots/${id}/block`, { method: 'POST' }, true)
}

export function unblockAdminSlot(id: number): Promise<AdminSlot> {
  return request(`/api/admin/slots/${id}/unblock`, { method: 'POST' }, true)
}

export function fetchAdminBookings(): Promise<{ bookings: AdminBooking[]; awaiting: AdminBooking[] }> {
  return request('/api/admin/bookings', {}, true)
}

export function approveAdminBooking(id: string): Promise<AdminBooking> {
  return request(`/api/admin/bookings/${encodeURIComponent(id)}/approve`, { method: 'POST' }, true)
}

export function rejectAdminBooking(id: string): Promise<AdminBooking> {
  return request(`/api/admin/bookings/${encodeURIComponent(id)}/reject`, { method: 'POST' }, true)
}
