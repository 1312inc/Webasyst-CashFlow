<template>
  <div>
    <label v-if="title">
      {{ title }}
    </label>
    <div class="wa-select">
        <select v-model="value" @change="onChange($event)">
            <option v-for="(item, i) in items" :key="i" :value="item.value">{{ item.title }}</option>
        </select>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    items: {
      type: Array
    },

    defaultSelectedIndex: {
      type: Number,
      default () {
        return 0
      }
    },

    title: {
      type: String
    }
  },

  data () {
    return {
      value: ''
    }
  },

  created () {
    this.value = this.items[this.defaultSelectedIndex].value
  },

  methods: {
    onChange (event) {
      this.$emit('selected', event.target.value)
    }
  }
}
</script>
