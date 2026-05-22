export default function onceWheel (element, scrollDownHandler, scrolUpHandler) {
  let wheelPower = 0
  let wheelLock = false
  let wheelTimeStamp = 0
  let wheelLockTimer = null
  const deltaThreshold = 10
  const noiseThreshold = 10

  function handler (event) {
    const delta = -event.deltaY
    const absDelta = Math.abs(delta)

    if (absDelta < noiseThreshold) return
    if (event.timeStamp - wheelTimeStamp < 300 && wheelLock) return

    wheelTimeStamp = event.timeStamp

    if (wheelPower < absDelta && !wheelLock) {
      if (delta < -deltaThreshold) scrollDownHandler()
      else if (delta > deltaThreshold) scrolUpHandler()

      lock(absDelta)

      wheelLockTimer && clearTimeout(wheelLockTimer)
      wheelLockTimer = setTimeout(() => {
        if (wheelPower !== absDelta) return
        unlock()
      }, 1000)
    } else if (absDelta < deltaThreshold) {
      unlock()
    }
  }

  function unsubscribe () {
    element.removeEventListener('wheel', handler)
  }

  function lock (absDelta) {
    wheelPower = absDelta
    wheelLock = true
  }

  function unlock () {
    wheelPower = deltaThreshold
    wheelLock = false
  }

  element.addEventListener('wheel', handler)

  return unsubscribe
}
