<template>
  <div
    v-click-outside="() => (open = false)"
    :class="{ 'is-opened': open, 'state-error': error }"
    class="dropdown"
  >
    <button
      class="dropdown-toggle button light-gray width-100"
      @click.prevent="open = !open"
    >
      <div
        v-if="label"
        class="smaller gray custom-mb-8 align-left"
      >
        {{ label }}
      </div>
      <div
        class="flexbox space-8 middle wrap-mobile align-left"
        v-html="
          activeItem[valuePropName] !== $options.props.value.default
            ? rowModificator(activeItem)
            : '&nbsp;'
        "
      />
    </button>
    <div
      :class="{ right: isRight }"
      :style="`max-height:${maxHeight}px`"
      class="dropdown-body"
    >
      <div
        v-for="(group, i) in itemsGroups"
        :key="i"
      >
        <div
          v-if="groupsLabels[useDefaultValue ? i - 1 : i]"
          class="smaller gray uppercase custom-px-16 custom-py-8"
        >
          {{ groupsLabels[useDefaultValue ? i - 1 : i] }}
        </div>
        <ul class="menu">
          <li
            v-for="(item, j) in group"
            :key="j"
          >
            <a
              v-if="item.name"
              href="#"
              @click.prevent="
                $emit('input', item[valuePropName]);
                open = false;
              "
              v-html="rowModificator(item)"
            />
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
import ClickOutside from 'vue-click-outside'

export default {

  directives: {
    ClickOutside
  },
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
      default: function () {
        return this.$t('notSelected')
      }
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
    label: {
      type: String
    },
    groupsLabels: {
      type: Array,
      default: () => []
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

  data () {
    return {
      open: false
    }
  },

  computed: {
    itemsGroups () {
      const groups = this.items.every(e => Array.isArray(e))
        ? [...this.items]
        : [[...this.items]]

      return this.useDefaultValue
        ? [
            [
              {
                [this.valuePropName]: this.$options.props.value.default,
                name: this.defaultValue
              }
            ],
     ***REMOVED***groups
          ]
        : groups
    },

    activeItem () {
      const flated = this.itemsGroups.flat()
      return (
        flated.find(i => i[this.valuePropName] === this.value) || flated[0]
      )
    }
  }
}
</script>

<style lang="scss" scoped>
.dropdown {
  .menu {
    margin: 0;
  }
}
.dropdown-toggle {
  div {
    white-space: nowrap;
    overflow-x: hidden;
    text-overflow: ellipsis;
    display: block;
  }
}
</style>
