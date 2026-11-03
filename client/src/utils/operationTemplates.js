import { appStateService } from '@/services/appState'

const NUMERIC_FIELDS = new Set(['account_id', 'category_id', 'transfer_account_id'])
const CONTRACTOR_FIELDS = new Set(['contractor_contact_id', 'contactor_contact_id'])

function getTemplates () {
  const templates = appStateService.templates
  return Array.isArray(templates) ? templates : []
}

function sortTemplates (a, b) {
  return (a.sort ?? 0) - (b.sort ?? 0) || (a.id ?? 0) - (b.id ?? 0)
}

function toNumber (value) {
  const number = Number(value)
  return Number.isFinite(number) ? number : null
}

export function getTemplatesByType (type) {
  return getTemplates()
    .filter(template => template.type === type)
    .sort(sortTemplates)
}

export function templateContentToDefaults (content) {
  const defaults = {}

  for (const item of Array.isArray(content) ? content : []) {
    if (!item || item.value === '' || item.value == null) continue

    const { name, value } = item

    if (CONTRACTOR_FIELDS.has(name)) {
      const id = toNumber(value)
      if (id) defaults.contractor_contact_id = id
      continue
    }

    if (NUMERIC_FIELDS.has(name)) {
      const id = toNumber(value)
      if (id != null) defaults[name] = id
      continue
    }

    if (name === 'amount') {
      const amount = toNumber(value)
      if (amount != null) defaults.amount = `${Math.abs(amount)}`
      continue
    }

    defaults[name] = value
  }

  return defaults
}

export function getTemplateLabel (template) {
  if (template?.name) return template.name

  const items = Array.isArray(template?.content) ? template.content : []
  const description = items.find(item => item.name === 'description')?.value
  if (description) return description

  return template?.id != null ? `#${template.id}` : ''
}
