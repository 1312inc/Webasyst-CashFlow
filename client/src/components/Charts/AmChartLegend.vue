<template>
  <div class="c-legend" :class="{ 'c-legend--column': isColumnStyle }">
    <div
      v-for="item in legendItems"
      :key="item.id"
      class="c-legend__item"
      :class="{ 'c-legend__item--column': isColumnStyle }"
    >
      <div
        class="c-legend__item__square"
        :style="`background-color:${item.category_color}`"
      ></div>
      <div class="c-legend__item__label smaller">
        <div class="c-legend__item__label__title">{{ item.category_name }}</div>
        <div class="c-legend__item__label__value">
          {{
            $helper.toCurrency({
              value: item.amount,
              isDynamics: true,
              isReverse: isReverse,
              currencyCode,
            })
          }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    legendItems: {
      type: Array
    },
    currencyCode: {
      type: String
    },
    isReverse: {
      type: Boolean,
      default: false
    },
    isColumnStyle: {
      type: Boolean,
      default: true
    }
  }
}
</script>

<style lang="scss">
.c-legend {
  display: flex;
  flex-wrap: wrap;

  &--column {
    justify-content: center;
  }

  &__item {
    display: flex;
    margin: 0.375rem 0.5rem;

    &--column {
      align-items: center;
      width: 80%;
    }

    &__square {
      width: 1rem;
      height: 1rem;
      flex-grow: 0;
      flex-shrink: 0;
      border-radius: 50%;
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
        margin-right: 1rem;
      }

      &__value {
        white-space: nowrap;
        font-weight: 500;
        color: var(--dark-gray);
      }
    }
  }
}
</style>
