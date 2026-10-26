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
        class="align-left"
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
        v-if="typeaheadQuery"
        class="smaller gray custom-px-16 custom-py-8"
      >
        {{ typeaheadQuery }}
      </div>
      <div
        v-for="(group, i) in itemsGroups"
        v-show="!typeaheadMatches || group.some(isTypeaheadMatch)"
        :key="i"
      >
        <div
          v-if="groupsLabels[useDefaultValue ? i - 1 : i]"
          class="smaller gray uppercase custom-px-16 custom-py-8"
        >
          {{ groupsLabels[useDefaultValue ? i - 1 : i] }}
        </div>
        <ul class="menu custom-m-0">
          <li
            v-for="(item, j) in group"
            v-show="!typeaheadMatches || isTypeaheadMatch(item)"
            :key="j"
            :class="{ selected: item === typeaheadActiveItem }"
          >
            <a
              v-if="item.name"
              href="#"
              @click.prevent="select(item)"
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
import { createTypeaheadBuffer, matchesQuery } from '@/utils/dropdownTypeahead'

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
    },
    typeahead: {
      type: Boolean,
      default: false
    }
  },

  data () {
    return {
      open: false,
      typeaheadQuery: ''
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
            ...groups
          ]
        : groups
    },

    activeItem () {
      const flated = this.itemsGroups.flat()
      return (
        flated.find(i => i[this.valuePropName] === this.value) || flated[0]
      )
    },

    typeaheadMatches () {
      if (!this.typeaheadQuery) return null
      return this.itemsGroups.flat().filter(item => item.name && matchesQuery(item.name, this.typeaheadQuery))
    },

    typeaheadActiveItem () {
      return this.typeaheadMatches ? this.typeaheadMatches[0] || null : null
    }
  },

  watch: {
    open (isOpen) {
      if (!this.typeahead) return
      if (isOpen) window.addEventListener('keydown', this.typeaheadBuffer.onKeydown)
      else this.detachTypeahead()
    },

    typeaheadActiveItem () {
      this.$nextTick(() => {
        this.$el.querySelector('.menu > li.selected')?.scrollIntoView({ block: 'nearest' })
      })
    }
  },

  created () {
    this.typeaheadBuffer = createTypeaheadBuffer({
      onChange: query => { this.typeaheadQuery = query },
      onEnter: () => {
        if (this.typeaheadActiveItem) this.select(this.typeaheadActiveItem)
      }
    })
  },

  beforeDestroy () {
    this.detachTypeahead()
  },

  methods: {
    select (item) {
      this.$emit('input', item[this.valuePropName])
      this.open = false
    },

    isTypeaheadMatch (item) {
      return this.typeaheadMatches.includes(item)
    },

    detachTypeahead () {
      window.removeEventListener('keydown', this.typeaheadBuffer.onKeydown)
      this.typeaheadBuffer.reset()
    }
  }
}
</script>
