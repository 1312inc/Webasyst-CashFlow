<template>
  <div
    v-click-outside="clickOutside"
    class="c-picker"
  >
    <div
      :class="{ 'c-picker__square--active': showPicker }"
      :style="`background-color:${value};`"
      class="c-picker__square"
      @click="showPicker = true"
    />
    <transition name="fade">
      <div
        v-show="showPicker"
        id="c-picker"
        class="c-picker__wheel"
      />
    </transition>
  </div>
</template>

<script>
import ClickOutside from 'vue-click-outside'
import iro from '@jaames/iro'
export default {

  directives: {
    ClickOutside
  },
  props: ['value'],

  data () {
    return {
      showPicker: false
    }
  },

  mounted () {
    this.colorPicker = new iro.ColorPicker('#c-picker', {
      width: 200,
      color: this.value,
      borderWidth: 2,
      borderColor: '#333',
      wheelLightness: false,
      layout: [
        {
          component: iro.ui.Slider
        },
        {
          component: iro.ui.Wheel
        }
      ]
    }).on('color:change', color => {
      this.$emit('input', color.hexString)
    })
  },

  methods: {
    clickOutside () {
      this.showPicker = false
    }
  }
}
</script>

<style lang="scss">
.c-picker {
  position: relative;

  &__square {
    width: 30px;
    height: 30px;
    border-radius: 0.25rem;
    border: 2px solid rgba($color: #000000, $alpha: 0.2);
    cursor: pointer;

    &--active {
      border: 2px solid rgba($color: #000000, $alpha: 0.5);
    }
  }

  &__wheel {
    position: absolute;
    transform: translateY(-160px) translateX(40px);
  }
}
</style>
