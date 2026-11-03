/** Cadastro visível do fornecedor (Decreto 7.962/2013). Complete CPF/CNPJ, endereço e WhatsApp antes de publicar. */
export const supplier = {
  brand: 'Alseq Teach',
  name: 'Alexandre Sequeira',
  role: 'Professor particular de francês',
  city: 'Niterói, RJ, Brasil',
  address: 'Niterói, RJ, Brasil',
  documentLabel: 'CPF',
  documentNumber: '',
  email: 'professor@alseqteach.local',
  whatsapp: '',
  legalVersion: '2026-10-22',
  updatedAtLabel: '22 de outubro de 2026',
}

export function documentLine(): string {
  if (supplier.documentNumber.trim() !== '') {
    return `${supplier.documentLabel} ${supplier.documentNumber}`
  }
  return 'CPF ou CNPJ: informe no atendimento até constar neste rodapé'
}

export function atendimentoMailto(subject: string, body = ''): string {
  const query = new URLSearchParams({ subject, body })
  return `mailto:${supplier.email}?${query.toString()}`
}
