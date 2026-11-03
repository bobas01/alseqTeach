import { documentLine, supplier } from './supplier'

export type LegalSlug = 'termos' | 'privacidade' | 'cancelamento'

export interface LegalSection {
  title: string
  paragraphs: string[]
}

export interface LegalDocument {
  slug: LegalSlug
  kicker: string
  title: string
  intro: string
  sections: LegalSection[]
}

const forum = 'Comarca de Niterói, Estado do Rio de Janeiro, Brasil'

export const legalDocuments: Record<LegalSlug, LegalDocument> = {
  termos: {
    slug: 'termos',
    kicker: 'Contrato',
    title: 'Termos de Uso',
    intro:
      `Ao reservar uma aula neste site, você contrata um serviço de ensino particular à distância com ${supplier.name}. ` +
      'Estes termos seguem a legislação brasileira, em especial o Código de Defesa do Consumidor (Lei 8.078/1990), ' +
      'o Marco Civil da Internet (Lei 12.965/2014) e o Decreto 7.962/2013 sobre comércio eletrônico.',
    sections: [
      {
        title: '1. Quem oferece o serviço',
        paragraphs: [
          `${supplier.name}, ${supplier.role}, marca ${supplier.brand}. ${documentLine()}. Endereço: ${supplier.address}.`,
          `Atendimento eletrônico: ${supplier.email}` +
            (supplier.whatsapp ? `. WhatsApp: ${supplier.whatsapp}.` : '.') +
            ' Demandas de informação, reclamação, suspensão ou cancelamento são confirmadas pelo mesmo canal e respondidas em até 5 dias úteis (Decreto 7.962/2013).',
        ],
      },
      {
        title: '2. O que você contrata',
        paragraphs: [
          'Aula particular de francês, ao vivo, com duração de 50 minutos, no fuso de Brasília (America/Sao_Paulo), de segunda a sexta nos horários publicados na agenda.',
          'A aula acontece neste mesmo site, por videoconferência no navegador (câmera e microfone). Não há material físico nem deslocamento.',
          'O conteúdo se adapta ao seu nível e ao que você indicar no questionário da primeira aula (conversação do dia a dia, trabalho, entrevista, linguagem técnica etc.).',
        ],
      },
      {
        title: '3. Preço e pagamento',
        paragraphs: [
          'O preço da aula avulsa é o valor exibido na agenda e no checkout, em reais (BRL). Não há taxa extra cobrada por este site além desse valor. Tributos incidentes sobre o serviço seguem a legislação fiscal aplicável ao fornecedor (Lei 12.741/2012: o preço informado já é o total cobrado aqui).',
          'O pagamento é feito por Pix, processado pelo Mercado Pago. O CPF é exigido pelo arranjo de pagamento. Depois que o Pix é confirmado, a reserva fica paga. Estornos, quando devidos, são feitos de volta via Pix, não “desfazendo” a transação original no Banco Central.',
          'Se a aula for no mesmo dia, o professor confirma a disponibilidade antes de liberar o Pix. Enquanto o Pix não é pago, o horário pode expirar e voltar à agenda.',
        ],
      },
      {
        title: '4. Como o contrato se forma',
        paragraphs: [
          'Antes de concluir, o site mostra um resumo: horário, duração, preço, meio de pagamento e o direito de desistir. Você pode corrigir os dados no formulário.',
          'O contrato se aperfeiçoa quando você aceita estes Termos e a reserva é criada. A confirmação chega na tela e por e-mail. Guarde o e-mail e o link da reserva: são a cópia do contrato em meio eletrônico (Decreto 7.962/2013, art. 4º).',
          'Você declara ter 18 anos ou mais, ou estar representado por responsável legal.',
        ],
      },
      {
        title: '5. Aula, atraso e conduta',
        paragraphs: [
          'Entre na sala pelo link da reserva. Atrasos do aluno não prorrogram o término da aula. Se o professor atrasar ou não puder dar a aula, a aula é remarcada ou o valor é devolvido.',
          'A sala é só para o aluno da reserva. É proibido gravar, transmitir ou usar a aula para fins comerciais sem autorização. Linguagem ofensiva ou assédio encerra a aula sem direito a reembolso.',
        ],
      },
      {
        title: '6. Internet e dados',
        paragraphs: [
          'O uso deste site também observa o Marco Civil da Internet: privacidade, proteção dos dados e liberdade de expressão, com os limites da lei. A Política de Privacidade detalha o tratamento (LGPD, Lei 13.709/2018).',
          'A videoconferência depende de conexão estável e da autorização de câmera e microfone no seu navegador. Falhas da sua rede não geram reembolso automático.',
        ],
      },
      {
        title: '7. Cancelamento',
        paragraphs: [
          'O direito de arrependimento, prazos e reembolso estão na Política de Cancelamento, que faz parte deste contrato. Em resumo: você pode desistir em até 7 dias da contratação se a aula ainda não tiver ocorrido, sem ônus, pelo mesmo canal da reserva ou por e-mail.',
        ],
      },
      {
        title: '8. Alterações e foro',
        paragraphs: [
          `Mudanças nestes termos valem para reservas feitas depois da data de atualização (${supplier.updatedAtLabel}). A versão aceita na sua reserva continua valendo para aquela aula.`,
          `Aplica-se a lei brasileira. Fica eleito o foro da ${forum}, sem prejuízo do foro do consumidor quando a lei assim determinar.`,
        ],
      },
    ],
  },
  privacidade: {
    slug: 'privacidade',
    kicker: 'LGPD',
    title: 'Política de Privacidade',
    intro:
      'Esta política explica como o Alseq Teach trata dados pessoais, de acordo com a Lei Geral de Proteção de Dados (Lei 13.709/2018), ' +
      'o Marco Civil da Internet (Lei 12.965/2014) e o guia da ANPD sobre cookies. O professor atua como controlador de pequeno porte ' +
      '(Resolução CD/ANPD nº 2/2022), com obrigações simplificadas, mas com os mesmos deveres de transparência e segurança.',
    sections: [
      {
        title: '1. Controlador',
        paragraphs: [
          `${supplier.name} (${supplier.brand}). ${documentLine()}. ${supplier.address}.`,
          `Canal do titular: ${supplier.email}. Pedidos de acesso, correção, exclusão ou informação são respondidos por este e-mail.`,
        ],
      },
      {
        title: '2. Quais dados e para quê',
        paragraphs: [
          'Nome, e-mail, WhatsApp (opcional), CPF, horário escolhido, valor, status da reserva, e, na primeira aula, nível de francês, objetivos de conversação e recado livre.',
          'Finalidades: identificar você, reservar o horário, emitir o Pix, enviar confirmação e lembrete, preparar a aula, cumprir obrigação legal (fiscal e de defesa do consumidor) e prevenir fraude.',
          'Bases legais principais (LGPD art. 7º): execução de contrato (reserva e aula), obrigação legal (CPF e dados fiscais do pagamento) e, quando necessário, legítimo interesse para segurança da reserva. Não usamos seus dados para anúncio de terceiros.',
        ],
      },
      {
        title: '3. Com quem compartilhamos',
        paragraphs: [
          'Mercado Pago (pagamento Pix): recebe nome, e-mail, CPF e valor. O tratamento deles segue os termos e a política do Mercado Pago.',
          'Infraestrutura de videoconferência (Jitsi / 8x8, meet.jit.si): áudio, vídeo e dados de sessão da aula. Há transferência internacional possível, necessária para prestar a aula.',
          'Provedor de e-mail, quando o envio estiver ativo, para confirmação, Pix liberado e lembrete. Não vendemos cadastro.',
        ],
      },
      {
        title: '4. Cookies e armazenamento local',
        paragraphs: [
          'Usamos apenas cookies e armazenamento estritamente necessários: funcionamento da reserva, preferência do aviso de cookies e, para o professor, o token da área admin. Não há cookies de publicidade nem de rede social neste site.',
          'Pela orientação da ANPD, cookies estritamente necessários à prestação do serviço não exigem um consentimento extra, mas exigem esta informação clara. Você pode bloquear cookies no navegador; a reserva e o Pix podem deixar de funcionar.',
        ],
      },
      {
        title: '5. Retenção e segurança',
        paragraphs: [
          'Dados da reserva e do pagamento são guardados pelo tempo da aula, do eventual cancelamento/reembolso e dos prazos legais (consumidor, fiscal e eventuais disputas). Depois, são eliminados ou anonimizados.',
          'Acesso restrito ao professor, conexão do site em HTTPS em produção, e pagamento no ambiente do Mercado Pago. Nenhum sistema é isento de risco; se houver incidente relevante, você será informado na forma da LGPD.',
        ],
      },
      {
        title: '6. Seus direitos',
        paragraphs: [
          'Você pode confirmar se tratamos seus dados, acessar, corrigir, anonimizar, eliminar (quando a lei permitir), pedir portabilidade, informação sobre compartilhamentos e revogar consentimento eventual (LGPD art. 18). Também pode reclamar à ANPD (gov.br/anpd).',
          'Alguns dados do Pix e da nota fiscal, quando emitida, precisam ser conservados por obrigação legal mesmo após um pedido de exclusão.',
        ],
      },
      {
        title: '7. Atualização',
        paragraphs: [
          `Texto vigente em ${supplier.updatedAtLabel} (versão ${supplier.legalVersion}). Mudanças materiais serão publicadas nesta página.`,
        ],
      },
    ],
  },
  cancelamento: {
    slug: 'cancelamento',
    kicker: 'CDC',
    title: 'Cancelamento e reembolso',
    intro:
      'Esta política cumpre o direito de arrependimento do consumidor em compras fora do estabelecimento ' +
      '(CDC art. 49 e Decreto 7.962/2013) e explica como pedir o estorno do Pix. Ela faz parte dos Termos de Uso.',
    sections: [
      {
        title: '1. Direito de arrependimento (7 dias)',
        paragraphs: [
          'Você pode desistir da contratação em até 7 dias, contados da reserva (ou do pagamento, o que ocorrer por último), sem precisar justificar, se a aula ainda não tiver sido prestada.',
          'O pedido pode ser feito pela mesma ferramenta da contratação: e-mail para ' +
            supplier.email +
            ' com o número da reserva, ou o link “Pedir cancelamento” na página do Pix. Confirmamos o recebimento e respondemos em até 5 dias úteis.',
          'Se a aula já ocorreu (serviço prestado), o arrependimento não devolve aquela aula. Se o professor não deu a aula, o valor é devolvido ou a aula é remarcada, à sua escolha.',
        ],
      },
      {
        title: '2. Depois dos 7 dias',
        paragraphs: [
          'Passado o prazo legal, você ainda pode pedir remarcação com pelo menos 24 horas de antecedência, sujeito a horário livre na agenda. Pedido feito depois disso, ou falta sem aviso, não gera reembolso, porque o horário foi reservado com exclusividade.',
          'Essa regra contratual não afasta direitos que a lei do consumidor garanta no caso concreto.',
        ],
      },
      {
        title: '3. Reserva ainda sem Pix',
        paragraphs: [
          'Se você não pagar no prazo do código Pix, ou se o professor recusar um pedido do mesmo dia, a reserva cai sozinha e nada é cobrado. Não é preciso cancelar.',
        ],
      },
      {
        title: '4. Como o dinheiro volta',
        paragraphs: [
          'O Pix, uma vez liquidado, não se “desfaz” no Banco Central. O reembolso é um novo Pix, iniciado pelo professor via Mercado Pago, para a mesma chave/CPF do pagamento, em até 10 dias úteis após o aceite do cancelamento (CDC: valores pagos a qualquer título são devolvidos).',
          'Não cobramos multa nem taxa de cancelamento no exercício do direito de arrependimento.',
        ],
      },
      {
        title: '5. Contato',
        paragraphs: [
          `E-mail: ${supplier.email}. ${supplier.whatsapp ? `WhatsApp: ${supplier.whatsapp}. ` : ''}` +
            `Endereço: ${supplier.address}. ${documentLine()}.`,
        ],
      },
    ],
  },
}

export const legalLinks: { slug: LegalSlug; label: string }[] = [
  { slug: 'termos', label: 'Termos de Uso' },
  { slug: 'privacidade', label: 'Privacidade' },
  { slug: 'cancelamento', label: 'Cancelamento' },
]
