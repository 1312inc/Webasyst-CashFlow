import dayjs from 'dayjs'
import { type Ref, onMounted, onBeforeUnmount, nextTick, ref, computed, watch } from 'vue'
import onceWheel from './oncewheel'
import { useElementSize } from '@vueuse/core'

export const useCalendarScroll = (wrapperRef: Ref<HTMLElement | null>, containerRef: Ref<HTMLElement | null>, firstDayOfWeek: 0 | 1) => {
  const currentSlide = ref(1)
  const wheelOn = ref(true)
  const transitionOn = ref(true)

  const { height: wrapperHeight } = useElementSize(wrapperRef)
  const cellHeight = computed(() => wrapperHeight.value / 6)

  const activeMonth = ref(dayjs())

  const startDate = computed(() => {
    const startOfMonth = activeMonth.value.startOf('M')
    const offset = startOfMonth.get('d') + -firstDayOfWeek
    const activeMonthOffset = offset < 0 ? 7 + offset : offset
    return startOfMonth.add(-(6 * 7 + activeMonthOffset), 'd')
  })

  const daysInCalendar = computed(() => new Array(6 * 7 * 3).fill(null).map((e, i) => {
    const date = startDate.value.add(i, 'd').toDate()
    const localeDate = date.toLocaleDateString()
    return {
      localeDate,
      timestamp: date.getTime(),
      isWeekend: [0, 6].includes(date.getDay()),
      isCurrent: new Date().toLocaleDateString() === localeDate,
      isFirstDay: date.getDate() === 1,
      isCurrentMonth: date.getMonth() === activeMonth.value.get('M')
    }
  }))

  const scrollDownHandler = () => {
    if (wheelOn.value) { if (currentSlide.value < 2) { currentSlide.value++ } }
  }

  const scrollUpHandler = () => {
    if (wheelOn.value) { if (currentSlide.value > 0) { currentSlide.value-- } }
  }

  const goToSlide = (num: number) => {
    if (containerRef.value) {
      const targetEl = containerRef.value.querySelectorAll('.icg-months-grid-day--first-day')[num]
      if (targetEl instanceof HTMLElement) {
        containerRef.value.style.transform = `translate3d(0px, ${-targetEl.offsetTop}px, 0px)`
      }
    }
  }

  watch(currentSlide, (slideNum) => {
    if (slideNum === 1) {
      goToSlide(slideNum)
    } else {
      if (containerRef.value) {
        transitionOn.value = true
        wheelOn.value = false
        containerRef.value.addEventListener('transitionend', () => {
          transitionOn.value = false
          wheelOn.value = true

          activeMonth.value = activeMonth.value.add(slideNum === 0 ? -1 : 1, 'month')
          nextTick(() => {
            currentSlide.value = 1
          })
        }, {
          once: true
        })

        goToSlide(slideNum)
      }
    }
  })

  watch(transitionOn, (on) => {
    if (containerRef.value) {
      containerRef.value.style.transitionProperty = on ? 'transform' : 'none'
    }
  })

  watch(cellHeight, () => {
    nextTick(() => {
      goToSlide(1)
    })
  })

  let unwheel: undefined | (() => void)

  onMounted(() => {
    if (wrapperRef.value) { unwheel = onceWheel(wrapperRef.value, scrollDownHandler, scrollUpHandler) }
    if (containerRef.value) {
      transitionOn.value = false
      containerRef.value.style.transform = `translate3d(0px, ${-containerRef.value.clientHeight}px, 0px)`
    }
  })

  onBeforeUnmount(() => {
    unwheel && unwheel()
  })

  return {
    cellHeight,
    activeMonth,
    daysInCalendar
  }
}
