import { i18n } from '@/plugins/locale'

let pendingConfirmation = false

export async function confirmDeletion (warningKey, actionKey = 'delete') {
  const warning = i18n.t(warningKey)
  const handler = window.webkit?.messageHandlers?.cashFlowConfirmDeletion
  const documentID = window.cashFlowDocumentID
  if (!handler || !documentID) return window.confirm(warning)
  if (pendingConfirmation) return false

  const route = window.location.hash
  pendingConfirmation = true
  try {
    const confirmed = await handler.postMessage({
      documentID,
      body: { warning, actionTitle: i18n.t(actionKey) }
    })
    return confirmed === true && window.cashFlowDocumentID === documentID && window.location.hash === route
  } catch {
    return false
  } finally {
    pendingConfirmation = false
  }
}
