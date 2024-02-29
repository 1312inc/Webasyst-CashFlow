<template>
  <div class="flexbox middle space-8">
    <div :class="{ 'state-with-inner-icon': showSign, left: showSign }">
      <imask-input
        ref="input"
        :value="value"
        :mask="Number"
        :scale="2"
        :signed="signed"
        :unmask="true"
        :radix="delimiters.decimal"
        :map-to-radix="['.', ',']"
        :max="999999999999"
        :thousands-separator="delimiters.thousands"
        :placeholder="placeholder"
        :class="classes"
        class="number bold width-100"
        :style="category && `border-color: ${category.color}`"
        type="text"
        inputmode="decimal"
        @accept="$emit('input', $event)"
        @keyup.enter="$emit('keyEnter', $event)"
      />
      <span
        v-if="showSign && categoryType"
        :class="{
          'text-orange': categoryType === 'expense',
          'text-green': categoryType === 'income'
        }"
        class="icon"
      >
        <span v-show="categoryType === 'expense'">
          <i class="fas fa-minus" />
        </span>
        <span v-show="categoryType === 'income'">
          <i class="fas fa-plus" />
        </span>
      </span>
    </div>
    <span
      v-if="account || currencyCode"
      class="bold"
    >{{
      $helper.currencySignByCode(account ? account.currency : currencyCode)
    }}</span>
  </div>
</template>

<script>
import { numeral } from '@/plugins/numeralMoment'
import { IMaskComponent } from 'vue-imask'
export default {

  components: {
    'imask-input': IMaskComponent
  },
  props: {
    value: {
      type: String
    },
    signed: {
      type: Boolean,
      default: true
    },
    showSign: {
      type: Boolean,
      default: true
    },
    categoryId: {
      type: Number
    },
    accountId: {
      type: Number
    },
    placeholder: {
      type: String,
      default: ''
    },
    transactionType: {
      type: String,
      default: ''
    },
    currencyCode: {
      type: String,
      default: ''
    },
    error: {
      type: Boolean,
      default: false
    },
    focused: {
      type: Boolean,
      default: false
    },
    short: {
      type: Boolean,
      default: false
    }
  },

  computed: {
    delimiters () {
      return numeral.locales[numeral.locale()].delimiters
    },
    category () {
      return this.$store.getters['category/getById'](this.categoryId)
    },
    account () {
      return this.$store.getters['account/getById'](this.accountId)
    },
    categoryType () {
      return this.transactionType || this.category?.type
    },
    classes () {
      return {
        'state-error': this.error,
        short: this.short
      }
    }
  },

  mounted () {
    if (this.focused) {
      this.$refs.input.$el.focus()
      this.$refs.input.$el.select()
    }
  }
}
</script>
