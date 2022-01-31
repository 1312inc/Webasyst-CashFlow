{
  const observer = new MutationObserver((mutations, me) => {
    const $sidebar = document.querySelector('.sidebar-body')
    if ($sidebar) {
      const $container = document.createElement('div')
      $container.innerHTML = `
        <div class="coinpaprika-currency-widget" 
     data-primary-currency="USD" 
     data-currency="btc-bitcoin" 
     data-modules='["chart", "market_details"]'  
     data-update-active="false" 
     data-update-timeout="30s"></div>
     `

      $sidebar.appendChild($container)

      me.disconnect()
    }
  })

  observer.observe(document, {
    childList: true,
    subtree: true
  })
}
