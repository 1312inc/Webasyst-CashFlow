<template>
  <div>
    <span v-if="transaction.contractor_contact" class="icon userpic size-48">
      <img :src="transaction.contractor_contact.userpic" alt="" />
    </span>
    <span
      v-else
      :style="`background-color:${category.color};`"
      class="userpic userpic48 align-center"
    >
      <i class="c-category-glyph fas" :class="mainGlyph"></i>
      <span
        v-if="transaction.external_source_info"
        :style="`background:${transaction.external_source_info.color}`"
        :title="transaction.external_source_info.name"
        class="userstatus"
      >
        <i :class="externalGlyphClass">{{ externalGlyphSymbol }}</i>
      </span>
    </span>
    <span v-show="isCollapseHeader || isRepeatingGroup">
      <span
        :style="`background-color:${category.color};`"
        class="userpic-stack-imitation"
      ></span>
      <span
        :style="`background-color:${category.color};`"
        class="userpic-stack-imitation"
      ></span>
    </span>
  </div>
</template>

<script>
import currencyIcons from '@/utils/currencyIcons'
export default {
  props: [
    'transaction',
    'category',
    'account',
    'isCollapseHeader',
    'isRepeatingGroup'
  ],

  computed: {
    mainGlyph () {
      // if account currency has icon
      if (currencyIcons[this.account.currency]) {
        return currencyIcons[this.account.currency]
      }
      // if transfer
      if (this.transaction.category_id === -1312) {
        return 'fa-exchange-alt'
      }
      // if positive amount
      if (this.transaction.amount >= 0) {
        return 'fa-arrow-up'
      }
      // if negative amount
      if (this.transaction.amount < 0) {
        return 'fa-arrow-down'
      }
      return ''
    },

    externalGlyph () {
      return this.transaction.external_source_info?.glyph
    },

    // check if glyph is symbol or string
    isExternalGlyphIcon () {
      return this.externalGlyph?.length > 1
    },

    externalGlyphClass () {
      return this.isExternalGlyphIcon ? this.externalGlyph : ''
    },

    externalGlyphSymbol () {
      return !this.isExternalGlyphIcon ? this.externalGlyph : ''
    }
  }
}
</script>
