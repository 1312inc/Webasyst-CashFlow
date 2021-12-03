<template>
  <div
    :class="{ 'is-opened': open, 'state-error': error }"
    v-click-outside="() => (open = false)"
    class="dropdown"
  >
    <button
      @click.prevent="open = !open"
      class="dropdown-toggle button light-gray width-100"
    >
      <div
        class="flexbox space-8 middle wrap-mobile align-left"
        :class="{ gray: activeItem.name === defaultValue }"
        v-html="rowModificator(activeItem)"
      />
    </button>
    <div
      :class="{ right: isRight }"
      :style="`max-height:${maxHeight}px`"
      class="dropdown-body"
    >
      <ul class="menu">
        <li v-for="(item, i) in itemsList" :key="i">
          <a
            v-if="item.name"
            @click.prevent="
              $emit('input', item[valuePropName]);
              open = false;
            "
            v-html="rowModificator(item)"
            href="#"
          ></a>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
import ClickOutside from 'vue-click-outside'

export default {
  props: {
    value: {
      default: null,
      required: true
    },
    items: {
      type: Array,
      required: true
    },
    valuePropName: {
      type: String,
      default: 'value'
    },
    useDefaultValue: {
      type: Boolean,
      default: true
    },
    defaultValue: {
      type: String,
      default: 'no select'
    },
    rowModificator: {
      type: Function,
      default: obj => {
        return obj.name
      }
    },
    isRight: {
      type: Boolean,
      default: false
    },
    maxHeight: {
      type: Number,
      default: 500
    },
    error: {
      type: Boolean,
      default: false
    }
  },

  directives: {
    ClickOutside
  },

  data () {
    return {
      open: false
    }
  },

  computed: {
    itemsList () {
      return this.useDefaultValue
        ? [
          {
            [this.valuePropName]: this.$options.props.value.default,
            name: this.defaultValue
          },
          ...this.items
        ]
        : [...this.items]
    },

    activeItem () {
      return this.itemsList.find(i => i[this.valuePropName] === this.value)
    }
  }
}
</script>

<style lang="scss" scoped>
.dropdown-toggle {
  div {
    white-space: nowrap;
    overflow-x: hidden;
    text-overflow: ellipsis;
    display: block;
  }
}
</style>
