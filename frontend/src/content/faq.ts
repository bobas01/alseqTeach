export interface FaqItem {
  id: string
  question: string
  answer: string
  keywords: string[]
}

export const faqItems: FaqItem[] = [
  {
    id: 'como-reservar',
    question: 'Como faço para reservar uma aula?',
    answer: 'Escolha um horário livre na agenda e informe seus dados. Na primeira aula, diga seu nível e o tipo de conversa que você quer — entrevista, trabalho, administração, linguagem técnica ou só falar no dia a dia. Depois pague com Pix. Se a aula for hoje, o professor confirma o horário antes de liberar o Pix.',
    keywords: ['reservar', 'horario', 'agendar', 'agenda', 'marcar', 'aula'],
  },
  {
    id: 'pix',
    question: 'Como funciona o pagamento via Pix?',
    answer: 'Depois de confirmar seus dados, o site mostra o código Pix (copia e cola). Quando o pagamento é aprovado, você recebe um e-mail de confirmação com o evento para o Google Agenda. A sala de vídeo fica nesta mesma página.',
    keywords: ['pix', 'pagar', 'pagamento', 'preco', 'valor', 'quanto'],
  },
  {
    id: 'preco',
    question: 'Quanto custa a aula?',
    answer: 'A aula avulsa de 50 minutos custa R$ 80, pagos por Pix. O valor aparece de novo no calendário e no checkout.',
    keywords: ['preco', 'valor', 'custa', '80', 'reais'],
  },
  {
    id: 'mesmo-dia',
    question: 'Posso reservar uma aula para hoje?',
    answer: 'Sim, mas o professor precisa confirmar o horário — ele pode não estar na frente do computador. Você acompanha o pedido nesta página e recebe um e-mail quando o Pix for liberado ou se o horário não puder ser confirmado.',
    keywords: ['hoje', 'mesmo dia', 'confirmar', 'professor', 'agora', 'urgente'],
  },
  {
    id: 'lembrete',
    question: 'Vou receber um lembrete da aula?',
    answer: 'Sim. Depois do Pix você recebe a confirmação por e-mail, com um botão para o Google Agenda e um arquivo .ics. No dia anterior à aula, um novo e-mail lembra o horário.',
    keywords: ['email', 'e-mail', 'lembrete', 'agenda', 'google', 'calendario', 'ics'],
  },
  {
    id: 'visio',
    question: 'Onde acontece a aula?',
    answer: 'A aula é ao vivo, por videoconferência, nesta mesma plataforma. Depois do Pix, use o botão “Entrar na aula”. Câmera e microfone abrem no navegador, no celular ou no computador. Não precisa instalar nada.',
    keywords: ['visio', 'video', 'jitsi', 'sala', 'meet', 'camera', 'zoom', 'link'],
  },
  {
    id: 'fuso',
    question: 'Os horários são de qual fuso?',
    answer: 'Todos os horários estão no fuso de Brasília (America/Sao_Paulo), de segunda a sexta.',
    keywords: ['fuso', 'horario', 'brasilia', 'timezone', 'sao paulo'],
  },
  {
    id: 'cpf',
    question: 'Por que pedem o CPF?',
    answer: 'O Mercado Pago exige o CPF do pagador para emitir o Pix. Também identificamos o contrato e cumprimos a legislação fiscal e de defesa do consumidor. O uso está na Política de Privacidade; não vendemos cadastro.',
    keywords: ['cpf', 'documento', 'dados', 'lgpd', 'privacidade'],
  },
  {
    id: 'cancelar',
    question: 'Posso cancelar e receber o dinheiro de volta?',
    answer: 'Sim. Em compras pela internet você pode desistir em até 7 dias se a aula ainda não aconteceu, sem multa. Escreva para o e-mail do professor ou use “Pedir cancelamento” na página da reserva. O Pix liquidado não se desfaz: o valor volta num novo Pix. Depois dos 7 dias, remarcação com 24 horas de antecedência. Detalhes na Política de Cancelamento.',
    keywords: ['cancelar', 'cancelamento', 'reembolso', 'devolver', 'arrependimento', 'desistir', 'estorno'],
  },
  {
    id: 'termos',
    question: 'Onde estão os termos, a privacidade e o responsável pelo site?',
    answer: 'No rodapé: Termos de Uso, Política de Privacidade (LGPD) e Cancelamento. Lá estão o nome do professor, o endereço em Niterói, o e-mail de atendimento e, quando cadastrado, o CPF ou CNPJ. O pagamento Pix passa pelo Mercado Pago.',
    keywords: ['termos', 'contrato', 'lei', 'lgpd', 'privacidade', 'cnpj', 'cpf', 'procon', 'fornecedor'],
  },
  {
    id: 'primeira-aula',
    question: 'Preciso dizer meu nível na reserva?',
    answer: 'Na primeira aula, sim: o formulário pede o nível e o objetivo (entrevista, colegas, administração, linguagem técnica, conversação livre…). O professor vê isso antes da aula para já chegar preparado. Nas próximas reservas, não precisa preencher de novo.',
    keywords: ['nivel', 'primeira', 'objetivo', 'conversacao', 'entrevista', 'tecnico', 'trabalho'],
  },
  {
    id: 'professor',
    question: 'Quem dá as aulas?',
    answer: 'O Alexandre: francês, 36 anos, mora em Niterói. Aula particular, no seu ritmo. Ele já foi cuidador e passou por fábrica, logística e venda; hoje também trabalha com web. A aula fica prática, perto do que você precisa no trabalho ou no dia a dia.',
    keywords: ['professor', 'alexandre', 'quem', 'frances', 'niteroi'],
  },
  {
    id: 'niveis',
    question: 'Para quais níveis são as aulas?',
    answer: 'As aulas são particulares e se adaptam ao seu nível: do básico à conversação avançada. No começo da aula combinamos o foco — conversação, gramática ou pronúncia.',
    keywords: ['nivel', 'basico', 'iniciante', 'avancado', 'conversacao', 'gramatica'],
  },
  {
    id: 'atraso',
    question: 'E se eu atrasar ou não puder entrar?',
    answer: 'Entre na sala pelo mesmo link da reserva. Para cancelar ou remarcar, use o e-mail de atendimento ou o botão na página da reserva. Horários não pagos voltam sozinhos para a agenda depois de 30 minutos. A Política de Cancelamento explica o prazo de 7 dias.',
    keywords: ['atraso', 'faltar', 'remarcar', 'cancelar', 'faltei'],
  },
  {
    id: 'celular',
    question: 'Posso fazer a aula pelo celular?',
    answer: 'Sim. O site e a sala de vídeo funcionam no navegador do telefone. Autorize câmera e microfone quando o navegador pedir.',
    keywords: ['celular', 'telefone', 'mobile', 'iphone', 'android'],
  },
]

export const faqFallback =
  'Não encontrei isso com segurança. Veja as perguntas frequentes, os Termos de Uso no rodapé, ou reserve um horário na agenda.'
