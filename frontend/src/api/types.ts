export type BookingStatus = 'awaiting_teacher' | 'pending' | 'paid' | 'cancelled' | 'expired'
export type SlotStatus = 'available' | 'held' | 'booked' | 'blocked'

export interface PublicConfig {
  priceCentavos: number
  currency: 'BRL'
  lessonDurationMinutes: number
  holdMinutes: number
  timezone: string
}

export interface Slot {
  id: number
  startsAt: string
  endsAt: string
  priceCentavos: number
  currency: string
}

export interface AdminSlotBooking {
  id: string
  status: BookingStatus
  studentName: string
  studentEmail: string
  studentPhone: string | null
  amountCentavos: number
  currency: string
  meetingUrl: string | null
  teacherCalendar: BookingCalendar | null
  intake: LessonIntake | null
}

export interface AdminSlot extends Slot {
  status: SlotStatus
  holdExpiresAt: string | null
  booking: AdminSlotBooking | null
}

export interface SlotList {
  timezone: string
  slots: Slot[]
}

export interface AdminSlotList {
  timezone: string
  slots: AdminSlot[]
}

export interface PixPayload {
  copyPaste: string | null
  qrCodeBase64: string | null
  ticketUrl: string | null
  simulated: boolean
}

export interface BookingCalendar {
  icsUrl: string
  googleUrl: string
}

export interface Booking {
  id: string
  status: BookingStatus
  studentName: string
  amountCentavos: number
  currency: string
  expiresAt: string | null
  paidAt: string | null
  meetingUrl: string | null
  slot: Slot
  pix: PixPayload | null
  calendar: BookingCalendar | null
}

export interface LessonIntake {
  firstLesson: boolean
  level: string | null
  levelLabel: string | null
  goals: string[]
  goalLabels: string[]
  note: string | null
}

export interface AdminBooking extends Booking {
  studentEmail: string
  studentPhone: string | null
  createdAt: string
  teacherCalendar: BookingCalendar | null
  intake: LessonIntake | null
}

export interface CreateBookingPayload {
  slotId: number
  studentName: string
  studentEmail: string
  studentPhone?: string
  studentCpf: string
  frenchLevel?: string
  conversationGoals?: string[]
  intakeNote?: string
  acceptedTerms: boolean
  acceptedPrivacy: boolean
}

export interface ApiErrorBody {
  error?: string
  message?: string
  fields?: Record<string, string>
}
