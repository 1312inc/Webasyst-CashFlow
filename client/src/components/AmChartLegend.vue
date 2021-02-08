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
          {{
            $helper.toCurrency({
              value: item.amount,
            })
          }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ['legendItems']
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
