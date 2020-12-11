<template>
  <div class="c-colors flexbox">
    <div v-for="(color, i) in colors[type]" :key="i">
      <div
        class="c-colors__item"
        :class="{ active: color === active }"
        :style="`background-color: ${color};`"
        @click="click(color)"
      ></div>
    </div>
  </div>
</template>

<script>
export default {
  props: ['type', 'active'],

  data () {
    return {
      colors: {
        expense: [
          '#880E4F',
          '#AD1457',
          '#C2185B',
          '#D81B60',
          '#E91E63',
          '#EC407A',
          '#F06292',
          '#F48FB1',
          '#F8BBD0',
          '#B71C1C',
          '#C62828',
          '#D32F2F',
          '#E53935',
          '#F44336',
          '#EF5350',
          '#E57373',
          '#EF9A9A',
          '#FFCDD2',
          '#CC1010',
          '#EE2222',
          '#FF1312',
          '#FF0000',
          '#FF5722',
          '#FF7043',
          '#FF8A65',
          '#FFAB91',
          '#FFCCBC',
          '#C65100',
          '#EF6C00',
          '#F57C00',
          '#FB8C20',
          '#FFA853',
          '#FFBB77',
          '#FFCC88',
          '#FFDD99',
          '#FFEEAA'
        ],
        income: [
          '#CCFF99',
          '#99FF66',
          '#66FF66',
          '#66FF99',
          '#66FFCC',
          '#66FFFF',
          '#66CCFF',
          '#6699FF',
          '#6666FF',
          '#CCFF00',
          '#66FF00',
          '#00FF00',
          '#00FF66',
          '#00FF99',
          '#00FFFF',
          '#00CCFF',
          '#0099FF',
          '#0033FF',
          '#99CC00',
          '#33CC00',
          '#00CC00',
          '#00CC33',
          '#00CC66',
          '#00CCCC',
          '#0099CC',
          '#0066CC',
          '#0033CC',
          '#669900',
          '#339900',
          '#009900',
          '#009933',
          '#009966',
          '#009999',
          '#006699',
          '#003399',
          '#000099'
        ]
      }
    }
  },

  methods: {
    click (color) {
      this.$emit('select', color)
    }
  }
}
</script>

<style lang="scss">
.c-colors {
  flex-wrap: wrap;

  & > div {
    flex: 1 0 11%;
  }

  &__item {
    cursor: pointer;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: 3px solid #fff;

    &.active {
      border-color: #000;
    }
  }
}
</style>
