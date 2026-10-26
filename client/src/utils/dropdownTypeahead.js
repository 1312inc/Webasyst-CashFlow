function isTypingTarget (target) {
  if (!target || !target.tagName) return false
  const tag = target.tagName
  return tag === 'INPUT' || tag === 'TEXTAREA' || target.isContentEditable
}

export function matchesQuery (text, query) {
  return String(text || '').toLocaleLowerCase().includes(query.toLocaleLowerCase())
}

export function createTypeaheadBuffer ({ onChange, onEnter }) {
  let buffer = ''

  function set (value) {
    buffer = value
    onChange(buffer)
  }

  function onKeydown (event) {
    if (event.metaKey || event.ctrlKey || event.altKey || event.isComposing) return
    if (isTypingTarget(event.target)) return

    if (event.key === 'Enter') {
      if (!buffer) return
      event.preventDefault()
      onEnter()
      return
    }

    if (event.key === 'Backspace') {
      if (!buffer) return
      event.preventDefault()
      set(buffer.slice(0, -1))
      return
    }

    if (event.key.length !== 1) return
    event.preventDefault()
    set(buffer + event.key)
  }

  return { onKeydown, reset: () => set('') }
}
