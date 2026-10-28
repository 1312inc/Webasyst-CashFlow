import { appStateService } from '@/services/appState'

const MAX_AGE = 31536000

/**
 * Path shared by the app page and `/api.php`, so the browser sends the cookie
 * both on the initial document request and on API calls.
 */
export function cookiePath () {
  const baseApiUrl = appStateService.baseApiUrl
  if (!baseApiUrl) return '/'
  try {
    const { pathname } = new URL(baseApiUrl, window.location.origin)
    const path = pathname.replace(/api\.php\/?$/, '')
    return path.startsWith('/') ? path : '/'
  } catch (_) {
    return '/'
  }
}

export function getCookie (name) {
  const prefix = `${encodeURIComponent(name)}=`
  const found = document.cookie.split('; ').find(item => item.startsWith(prefix))
  if (!found) return null
  try {
    return decodeURIComponent(found.slice(prefix.length))
  } catch (_) {
    return found.slice(prefix.length)
  }
}

export function setCookie (name, value) {
  const parts = [
    `${encodeURIComponent(name)}=${encodeURIComponent(value)}`,
    `path=${cookiePath()}`,
    `max-age=${MAX_AGE}`,
    'samesite=Lax'
  ]
  if (window.location.protocol === 'https:') {
    parts.push('secure')
  }
  document.cookie = parts.join('; ')
}

export function deleteCookie (name) {
  document.cookie = `${encodeURIComponent(name)}=; path=${cookiePath()}; max-age=0; samesite=Lax`
}
