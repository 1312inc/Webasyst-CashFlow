<template>
  <div>
    <span
      :style="`background-color:${category.color};`"
      class="userpic userpic48 align-center"
    >
      <img
        v-if="transaction.contractor_contact"
        :src="transaction.contractor_contact.userpic"
        alt=""
      />
      <div v-else>
        <i class="c-category-glyph fas" :class="mainGlyph"></i>
      </div>
      <span
        v-if="transaction.external_source_info"
        :style="`background:${transaction.external_source_info.color}`"
        :title="transaction.external_source_info.name"
        class="userstatus"
      >
        <i :class="externalGlyphClass">{{ externalGlyphSymbol }}</i>
      </span>
    </span>
    <span v-if="isCollapseHeader || isRepeatingGroup">
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
        return 'fa-arrow-down'
      }
      // if negative amount
      if (this.transaction.amount < 0) {
        return 'fa-arrow-up'
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

<style lang="scss">
.userpic48 > .userstatus {
  width: 0.5625rem;
  height: 0.5625rem;
  bottom: 0;
  right: 0;
  transition: 0.1s;
  font-size: 0;

  i {
    display: none;
    font-style: normal;
    font-size: 0.75rem;
    font-weight: bold;
  }
}
.userpic48:hover > .userstatus > i {
  display: block;
}
.userpic48 > .userstatus > svg {
  display: none;
}
.userpic48:hover > .userstatus {
  width: 1.25rem;
  height: 1.25rem;
  font-size: 1rem;
  bottom: -0.375rem;
  right: -0.375rem;
}
.userpic48:hover > .userstatus > svg {
  display: block;
}
</style>
