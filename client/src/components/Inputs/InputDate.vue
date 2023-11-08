<template>
  <input
    ref="date"
    type="hidden"
  >
</template>

<script>
import { locale } from '@/plugins/locale'
import flatpickr from 'flatpickr'
import { Russian } from 'flatpickr/dist/l10n/ru.js'
export default {
  props: {
    value: {
      type: String
    },
    minDate: {
      type: String
    },
    maxDate: {
      type: String
    },
    inline: {
      type: Boolean,
      default: false
    }
  },

  mounted () {
    this.flatpickr = flatpickr(this.$refs.date, {
      defaultDate: this.value || 'today',
      dateFormat: locale === 'ru_RU' ? 'd.m.Y' : 'm/d/Y',
      ...(locale === 'ru_RU' && { locale: Russian }),
      ...(this.minDate && {
        minDate: this.minDate
      }),
      ...(this.maxDate && {
        maxDate: this.maxDate
      }),
      inline: this.inline,
      parseDate: datestr => {
        return this.$moment(datestr).toDate()
      },
      onReady: selectedDates => {
        if (this.inline) {
          this.$refs.date.setAttribute('type', 'hidden')
        }
        this.emitChanges(selectedDates[0])
      },
      onChange: selectedDates => {
        this.emitChanges(selectedDates[0])
      }
    })
  },

  beforeDestroy () {
    if (this.flatpickr) this.flatpickr.destroy()
  },

  methods: {
    emitChanges (selectedDates) {
      this.$emit('input', this.$moment(selectedDates).format('YYYY-MM-DD'))
    }
  }
}
</script>

<style lang="scss">
@import "flatpickr/dist/flatpickr.css";
[data-theme="dark"] {
  .flatpickr-calendar {
    background: var(--background-color-blank);
    box-shadow: none;
  }
  .flatpickr-calendar.arrowTop:before,
  .flatpickr-calendar.arrowTop:after {
    border-bottom-color: var(--background-color-blank);
  }
  .flatpickr-months .flatpickr-month,
  .flatpickr-months .flatpickr-prev-month,
  .flatpickr-months .flatpickr-next-month,
  span.flatpickr-weekday,
  .flatpickr-day:not(.selected) {
    color: var(--text-color);
  }
  .flatpickr-months .flatpickr-prev-month,
  .flatpickr-months .flatpickr-next-month {
    fill: var(--text-color);
  }
  .flatpickr-day.flatpickr-disabled,
  .flatpickr-day.flatpickr-disabled:hover,
  .flatpickr-day.prevMonthDay,
  .flatpickr-day.nextMonthDay,
  .flatpickr-day.notAllowed,
  .flatpickr-day.notAllowed.prevMonthDay,
  .flatpickr-day.notAllowed.nextMonthDay {
    color: var(--text-color-hint);
  }
  .numInputWrapper span {
    border-color: var(--text-color);
  }
  .flatpickr-current-month .numInputWrapper span.arrowDown:after {
    border-top-color: var(--text-color);
  }
  .flatpickr-current-month .numInputWrapper span.arrowUp:after {
    border-bottom-color: var(--text-color);
  }
}
</style>
