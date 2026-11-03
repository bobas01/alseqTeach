export const INTAKE_LEVELS = [
  { id: 'iniciante', label: 'Nunca estudei / iniciante' },
  { id: 'basico', label: 'Básico' },
  { id: 'intermediario', label: 'Intermediário' },
  { id: 'avancado', label: 'Avançado / conversação' },
] as const

export const INTAKE_GOALS = [
  { id: 'dia_a_dia', label: 'Falar no dia a dia, com as pessoas' },
  { id: 'colegas', label: 'Conversar com colegas de trabalho' },
  { id: 'assistentes', label: 'Falar com assistentes ou a equipe' },
  { id: 'administracao', label: 'Falar com a administração' },
  { id: 'entrevista', label: 'Preparar uma entrevista em francês' },
  { id: 'tecnico', label: 'Linguagem técnica da minha área' },
  { id: 'outro', label: 'Outro objetivo' },
] as const

export type IntakeLevelId = (typeof INTAKE_LEVELS)[number]['id']
export type IntakeGoalId = (typeof INTAKE_GOALS)[number]['id']
