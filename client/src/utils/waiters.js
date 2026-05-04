const DEFAULT_INTERVAL_MS = 100
const DEFAULT_TIMEOUT_MS = 30000

/**
 * @param {() => boolean} predicate
 * @param {() => *} getValue
 * @param {{ timeoutMs?: number, intervalMs?: number }} [options]
 * @returns {Promise<*|null>} value or null after timeout
 */
function pollUntil (predicate, getValue, options = {}) {
  const intervalMs = options.intervalMs ?? DEFAULT_INTERVAL_MS
  const timeoutMs = options.timeoutMs ?? DEFAULT_TIMEOUT_MS
  const deadline = Date.now() + timeoutMs

  return new Promise((resolve) => {
    const check = () => {
      if (predicate()) {
        resolve(getValue())
        return
      }
      if (Date.now() >= deadline) {
        resolve(null)
        return
      }
      setTimeout(check, intervalMs)
    }
    check()
  })
}

/**
 * @param {{ timeoutMs?: number, intervalMs?: number }} [options]
 * @returns {Promise<typeof window.tippy | null>}
 */
export function waitForTippy (options) {
  return pollUntil(() => !!window.tippy, () => window.tippy, options)
}

/**
 * @param {{ timeoutMs?: number, intervalMs?: number }} [options]
 * @returns {Promise<typeof window.jQuery | null>}
 */
export function waitForjQuery (options) {
  return pollUntil(() => typeof window.jQuery !== 'undefined', () => window.jQuery, options)
}
