import { useStorage } from '@vueuse/core'

export const transactionsViewMode = useStorage('cashTransactionsView', 'list')

function escapeHtml (value) {
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
}

function escapeTsv (value) {
  const text = value == null ? '' : String(value)
  if (/[\t\n\r"]/.test(text)) {
    return `"${text.replace(/"/g, '""')}"`
  }
  return text
}

function selectionElement (node) {
  if (!node) return null
  return node.nodeType === 3 ? node.parentElement : node
}

function isInsideTransactionsTable (node) {
  return !!selectionElement(node)?.closest('.c-transactions-table')
}

function headerLabels (columnStart, columnEnd) {
  const head = document.querySelector('table.c-transactions-table thead')
  if (!head) return []
  return [...head.querySelectorAll('th')]
    .filter(th => {
      return !th.classList.contains('c-transactions-table__check') &&
        !th.classList.contains('c-transactions-table__action')
    })
    .slice(columnStart, columnEnd + 1)
    .map(th => th.textContent.trim())
}

function writeTableClipboard (event, matrix) {
  const tsv = matrix.map(row => row.map(escapeTsv).join('\t')).join('\n')
  const html = `<table>${matrix.map(row => {
    return `<tr>${row.map(cell => `<td>${escapeHtml(cell)}</td>`).join('')}</tr>`
  }).join('')}</table>`
  event.preventDefault()
  event.clipboardData.setData('text/plain', tsv)
  event.clipboardData.setData('text/html', html)
}

function visibleTableRows () {
  return [...document.querySelectorAll('tr.c-transaction-table-row')].filter(tr => tr.offsetParent !== null)
}

function matrixFromSelection (selection) {
  let minCol = Infinity
  let maxCol = -1
  const picked = []

  visibleTableRows().forEach(tr => {
    const cells = [...tr.querySelectorAll('td[data-copy]')]
    const selectedIndexes = []
    cells.forEach((td, index) => {
      if (selection.containsNode(td, true)) selectedIndexes.push(index)
    })
    if (!selectedIndexes.length) return
    minCol = Math.min(minCol, ...selectedIndexes)
    maxCol = Math.max(maxCol, ...selectedIndexes)
    picked.push(cells)
  })

  if (!picked.length) return null

  const body = picked.map(cells => {
    return cells.slice(minCol, maxCol + 1).map(td => td.getAttribute('data-copy') || '')
  })
  return [headerLabels(minCol, maxCol), ...body]
}

function matrixFromCheckedRows () {
  const rows = visibleTableRows().filter(tr => tr.classList.contains('c-item-selected'))
  if (!rows.length) return null
  const width = rows[0].querySelectorAll('td[data-copy]').length - 1
  const body = rows.map(tr => {
    return [...tr.querySelectorAll('td[data-copy]')].map(td => td.getAttribute('data-copy') || '')
  })
  return [headerLabels(0, width), ...body]
}

export function copyTransactionsTable (event) {
  const selection = window.getSelection()
  const selectionInTable = selection && (
    isInsideTransactionsTable(selection.anchorNode) ||
    isInsideTransactionsTable(selection.focusNode)
  )
  const targetInTable = isInsideTransactionsTable(event.target)

  if (!selectionInTable && !targetInTable) return

  if (selection && !selection.isCollapsed && selection.toString() && selectionInTable) {
    const matrix = matrixFromSelection(selection)
    if (matrix) writeTableClipboard(event, matrix)
    return
  }

  if (!targetInTable) return

  const matrix = matrixFromCheckedRows()
  if (matrix) writeTableClipboard(event, matrix)
}
