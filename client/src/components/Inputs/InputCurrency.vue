<template>
  <imask-input
    :value="$attrs.value"
    @accept="update"
    v-on="$listeners"
    :mask="Number"
    :scale="2"
    :signed="signed"
    :unmask="true"
    :radix="delimiters.decimal"
    :mapToRadix="['.', ',']"
    :thousandsSeparator="delimiters.thousands"
  />
</template>

<script>
import { numeral } from '@/plugins/numeralMoment'
import { IMaskComponent } from 'vue-imask'
export default {
  props: {
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
  },

  methods: {
    update (e) {
      this.$emit('input', e)
    }
  }
}
</script>
