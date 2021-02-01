<template>
  <input ref="date" type="text" />
</template>

<script>
import { locale } from '@/plugins/locale'
import flatpickr from 'flatpickr'
import { Russian } from 'flatpickr/dist/l10n/ru.js'
export default {
  props: ['value', 'minDate', 'maxDate', 'defaultDate'],

  async mounted () {
    this.flatpickr = flatpickr(this.$refs.date, {
      defaultDate: this.defaultDate || this.value || 'today',
      dateFormat: locale === 'ru_RU' ? 'd.m.Y' : 'm/d/Y',
      ...(locale === 'ru_RU' && { locale: Russian }),
      ...(this.minDate && {
        minDate: this.minDate
      }),
      ...(this.maxDate && {
        maxDate: this.maxDate
      }),
      parseDate: datestr => {
        return this.$moment(datestr).toDate()
      },
      onReady: selectedDates => {
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
