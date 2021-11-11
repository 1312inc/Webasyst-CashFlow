<template>
  <imask-input
    :value="value"
    @accept="$emit('input', $event)"
    :mask="Number"
    :scale="2"
    :signed="signed"
    :unmask="true"
    :radix="delimiters.decimal"
    :mapToRadix="['.', ',']"
    :thousandsSeparator="delimiters.thousands"
    type="text"
  />
</template>

<script>
import { numeral } from '@/plugins/numeralMoment'
import { IMaskComponent } from 'vue-imask'
export default {
  props: {
    value: {
      type: String
    },
    signed: {
      type: Boolean,
      default: true
    }
  },

  components: {
    'imask-input': IMaskComponent
  },

  computed: {
    delimiters () {
      return numeral.locales[numeral.locale()].delimiters
    }
  }
}
</script>
