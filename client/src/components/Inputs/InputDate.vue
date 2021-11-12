<template>
  <input ref="date" />
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

<style>
@import "~flatpickr/dist/flatpickr.css";
</style>
