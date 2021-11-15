<template>
  <div style="position:relative; top: 2px;">
    <span
      :style="`background-color:${category.color};`"
      class="userpic userpic48 align-center"
    >
      <img
        v-if="transaction.contractor_contact"
        :src="transaction.contractor_contact.userpic"
        alt=""
        class="c-contractor"
      />
      <div v-else>
        <i class="c-category-glyph fas" :class="mainGlyph"></i>
      </div>
      <!-- if repeating imported transaction -->
      <span
        v-if="collapseHeaderData"
        class="badge smaller"
        :style="`background:${transaction.external_source_info.color}`"
        :title="transaction.external_source_info.name"
      >
        &times;
        {{ collapseHeaderData.ids.length }}
      </span>
      <!-- if repeated just created transaction -->
      <span
        v-if="
          transaction.$_flagCreated && transaction.affected_transactions > 1
        "
        class="badge gray smaller"
      >
        &times;
        {{
          transaction.affected_transactions > 100
            ? "99+"
            : transaction.affected_transactions
        }}
      </span>
    </span>
    <!-- Userpic stack imitation block -->
    <span
      v-if="isCollapseHeader || isRepeatingGroup"
      class="c-userpic-stack-imitation"
    >
      <span
        :style="`background-color:${category.color};`"
        class="c-userpic-stack-imitation__item"
      ></span>
      <span
        :style="`background-color:${category.color};`"
        class="c-userpic-stack-imitation__item"
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
    'isRepeatingGroup',
    'collapseHeaderData'
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
    }
  }
}
</script>

<style lang="scss">
.userpic48 > .c-contractor {
  width: 2.5rem;
  height: 2.5rem;
  border: 0.125rem solid var(--background-color-blank);
  margin-top: 0.125rem;
}

.userpic48 > .badge {
  position: absolute;
  bottom: -0.375rem;
  right: -0.375rem;
}

.c-userpic-stack-imitation {
  position: absolute;
  top: 0;
  left: 0;
  z-index: 0;

  &__item {
    position: absolute;
    top: 0;
    width: 3rem;
    height: 3rem;
    background: var(--black);
    opacity: 0.25;
    border-radius: 50%;
    margin-left: -0.25rem;

    &:nth-child(1) {
      margin-left: -0.5rem;
    }
  }
}
</style>
