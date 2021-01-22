<template>
  <div v-click-outside="clickOutside" class="c-picker">
    <div
      @click="showPicker = true"
      :class="{ 'c-picker__square--active': showPicker }"
      :style="`background-color:${color};`"
      class="c-picker__square"
    ></div>
    <transition name="fade">
      <div v-show="showPicker" class="c-picker__wheel" id="c-picker"></div>
    </transition>
  </div>
</template>

<script>
import ClickOutside from 'vue-click-outside'
import iro from '@jaames/iro'
export default {
  props: {
    startColor: {
      type: String
    },
    type: {
      type: String
    }
  },

  data () {
    return {
      showPicker: false
    }
  },

  computed: {
    color () {
      return this.startColor || (this.type === 'income' ? '#00FF00' : '#E57373')
    }
  },

  mounted () {
    this.colorPicker = new iro.ColorPicker('#c-picker', {
      width: 200,
      color: this.color,
      borderWidth: 10,
      borderColor: '#333',
      wheelLightness: false,
      layout: [
        {
          component: iro.ui.Wheel
        }
      ]
    }).on('color:change', color => {
      this.$emit('colorChange', color.hexString)
    })
  },

  methods: {
    clickOutside () {
      this.showPicker = false
    }
  },

  directives: {
    ClickOutside
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
    transform: translateY(-120px) translateX(40px);
  }
}
</style>
