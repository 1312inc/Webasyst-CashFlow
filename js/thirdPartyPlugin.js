{
  const observer = new MutationObserver((mutations, me) => {
    const $chart = document.querySelector('.c-chart-main')
    if ($chart) {
      let isHidden = localStorage.getItem('chartHidden')
      $chart.style.display = isHidden ? 'none' : 'block'

      const $container = document.createElement('div')
      $container.style.padding = '.75rem 2.375rem'
      const $button = document.createElement('button')
      $button.innerHTML = isHidden ? 'Open Chart' : 'Close Chart'
      $container.appendChild($button)

      $chart.parentElement.insertBefore($container, $chart)

      $button.onclick = () => {
        isHidden = !isHidden
        $chart.style.display = isHidden ? 'none' : 'block'
        $button.innerHTML = isHidden ? 'Open Chart' : 'Close Chart'
        if (isHidden) {
          localStorage.setItem('chartHidden', true)
        } else {
          localStorage.removeItem('chartHidden')
        }
      }

      me.disconnect()
    }
  })

  observer.observe(document, {
    childList: true,
    subtree: true
  })
}
