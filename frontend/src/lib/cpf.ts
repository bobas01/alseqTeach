export function digitsOnly(value: string): string {
  return value.replace(/\D+/g, '')
}

export function maskCpf(value: string): string {
  const digits = digitsOnly(value).slice(0, 11)
  const parts = [
    digits.slice(0, 3),
    digits.slice(3, 6),
    digits.slice(6, 9),
    digits.slice(9, 11),
  ]
  if (digits.length <= 3) {
    return parts[0]
  }
  if (digits.length <= 6) {
    return `${parts[0]}.${parts[1]}`
  }
  if (digits.length <= 9) {
    return `${parts[0]}.${parts[1]}.${parts[2]}`
  }
  return `${parts[0]}.${parts[1]}.${parts[2]}-${parts[3]}`
}

export function isValidCpf(value: string): boolean {
  const digits = digitsOnly(value)
  if (digits.length !== 11 || /^(\d)\1{10}$/.test(digits)) {
    return false
  }

  for (let length = 9; length <= 10; length++) {
    let sum = 0
    for (let i = 0; i < length; i++) {
      sum += Number(digits[i]) * (length + 1 - i)
    }
    const remainder = sum % 11
    const expected = remainder < 2 ? 0 : 11 - remainder
    if (Number(digits[length]) !== expected) {
      return false
    }
  }

  return true
}
