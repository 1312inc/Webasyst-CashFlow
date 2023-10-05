export default (payload) => {
  const counterContainer = document.querySelector('#wa-header #wa-applist #wa-app-cash > a')
  if (counterContainer) {
    let counter = counterContainer.querySelector('.badge')
    if (payload.onbadge > 0) {
      if (!counter) {
        counter = document.createElement('span')
        counter.classList.add('badge', 'indicator')
        counterContainer.appendChild(counter)
      }
      counter.innerText = payload.onbadge
    } else {
      if (counter) {
        counter.remove()
      }
    }
  }
}
