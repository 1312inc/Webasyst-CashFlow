<template>
  <div class="c-legend">
    <div v-for="item in legendItems" :key="item.id" class="c-legend__item">
      <div
        class="c-legend__item__square"
        :style="`background-color:${item.category_color}`"
      ></div>
      <div class="c-legend__item__label smaller">
        <div class="c-legend__item__label__title">{{ item.category }}</div>
        <div class="c-legend__item__label__value">
          {{ $helper.toCurrency(item.amount, null) }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  computed: {
    rawData () {
      return this.$store.state.transaction.activeGroupTransactions.length
        ? this.$store.state.transaction.activeGroupTransactions
        : this.$store.state.transaction.defaultGroupTransactions
    },
    legendItems () {
      return this.rawData.reduce((acc, el) => {
        // const el =
        //   this.$store.getters['transaction/getTransactionById'](id) || id
        const category = this.$store.getters['category/getById'](el.category_id)
        if (!acc[category.id]) {
          acc[category.id] = {
            amount: el.amount,
            category: category.name,
            category_color: category.color
          }
        } else {
          acc[category.id].amount += el.amount
        }
        return acc
      }, {})
    }
  }
}
</script>

<style lang="scss">
.c-legend {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;

  &__item {
    display: flex;
    align-items: center;
    width: 200px;
    margin: 0.2rem 0.4rem;

    &__square {
      width: 1.4rem;
      height: 1.4rem;
      flex-grow: 0;
      flex-shrink: 0;
      border-radius: 4px;
      margin-right: 0.4rem;
    }
    &__label {
      display: flex;
      flex-grow: 1;
      align-items: center;
      justify-content: space-between;
      min-width: 0;

      &__title {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }
    }
  }
}
</style>
