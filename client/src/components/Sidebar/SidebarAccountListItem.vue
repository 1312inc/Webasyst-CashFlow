<template>
  <li :class="{'selected': isActive}">
    <router-link :to="`/account/${account.id}`" class="flexbox middle">
      <span class="icon">
        <img
          v-if="$helper.isValidHttpUrl(account.icon)"
          :src="account.icon"
          alt=""
          class="size-20"
        />
        <span v-else>
          <i :class="`fas ${accountIcon}`"></i>
        </span>
      </span>
      <span>{{ account.name }}</span>
      <span
        v-if="account.stat"
        class="count"
        v-html="
          `${account.stat.summaryShorten}&nbsp;${$helper.currencySignByCode(
            account.currency
          )}`
        "
      ></span>
    </router-link>
  </li>
</template>

<script>
import currencyIcons from '@/utils/currencyIcons'
export default {
  props: ['account'],

  computed: {
    isActive () {
      return this.$store.state.currentType === 'account' && this.$store.state.currentTypeId === this.account.id
    },

    accountIcon () {
      return currencyIcons[this.account.currency] || currencyIcons.default
    }
  }
}
</script>
