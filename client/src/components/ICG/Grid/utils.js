export const getWeekDaysNames = (locale, firstDayOfWeek) => {
  const baseDate = new Date(Date.UTC(2017, 0, 2))
  const weekDays = []
  for (let i = 0; i < 7; i++) {
    weekDays.push(baseDate.toLocaleDateString(locale, { weekday: 'short' }))
    baseDate.setDate(baseDate.getDate() + 1)
  }

  if (!firstDayOfWeek) {
    weekDays.unshift(weekDays.pop())
  }

  return weekDays
}
