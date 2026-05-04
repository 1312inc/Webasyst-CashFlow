import { waitForjQuery } from '@/utils/waiters'

export async function triggerTooltip () {
  const $ = await waitForjQuery()
  if (!$) return
  const fake = document.createElement('div')
  fake.id = 'tooltip-fake'
  document.body.appendChild(fake)
  $('#tooltip-fake').waTooltip()
  setTimeout(() => {
    document.body.removeChild(fake)
  }, 100)
}
