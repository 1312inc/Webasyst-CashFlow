const STORAGE_KEY = 'cash_breakdown_local_to_days'

export function getLocalBreakdownToDays () {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    if (raw == null || raw === '') return null
    const days = Number(raw)
    return Number.isFinite(days) && days > 0 ? days : null
  } catch (_) {
    return null
  }
}

export function setLocalBreakdownToDays (days) {
  const value = Number(days)
  if (!Number.isFinite(value) || value <= 0) return
  try {
    localStorage.setItem(STORAGE_KEY, String(value))
  } catch (_) {}
}

export function clearLocalBreakdownToDays () {
  try {
    localStorage.removeItem(STORAGE_KEY)
  } catch (_) {}
}
