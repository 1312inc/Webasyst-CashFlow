<template>
  <input
    :value="value"
    @input="$emit('input', $event.target.value)"
    type="text"
    ref="date"
  />
</template>

<script>
import { locale } from '@/plugins/locale'
import flatpickr from 'flatpickr'
import { Russian } from 'flatpickr/dist/l10n/ru.js'
export default {
  props: ['value'],

  mounted () {
    this.flatpickr = flatpickr(this.$refs.date, {
      locale: locale === 'ru_RU' ? Russian : 'en'
    })
  },

  beforeDestroy () {
    if (this.flatpickr) this.flatpickr.destroy()
  }
}
</script>

<style>
  @import "~flatpickr/dist/flatpickr.css";
</style>
