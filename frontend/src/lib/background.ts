export const AULA_FUNDO = '/aula-fundo.jpg'

export async function aulaFundoDataUrl(): Promise<string> {
  const response = await fetch(AULA_FUNDO)
  if (!response.ok) {
    throw new Error('fundo')
  }
  const blob = await response.blob()
  return await new Promise((resolve, reject) => {
    const reader = new FileReader()
    reader.onload = () => resolve(String(reader.result))
    reader.onerror = () => reject(new Error('fundo'))
    reader.readAsDataURL(blob)
  })
}
