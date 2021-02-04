<template>
  <draggable
    tag="ul"
    v-model="accounts"
    class="menu"
  >
    <slot></slot>
  </draggable>
</template>

<script>
import draggable from 'vuedraggable'
export default {
  components: {
    draggable
  },

  computed: {
    accounts: {
      get () {
        return this.$store.state.account.accounts
      },
      set (value) {
        const ids = value.map(e => e.id)
        this.$store.dispatch('account/sort', {
          order: ids
        })
      }
    }
  }
}
</script>
