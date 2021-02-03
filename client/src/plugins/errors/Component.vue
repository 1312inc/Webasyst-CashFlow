<template>
  <div v-if="errors.length" class="alert-fixed-box">
    <span v-for="(error, i) in errors" :key="i" class="alert danger">
      <span class="icon"><i class="fas fa-skull"></i></span>
      <span v-html="error"></span>
      <a href="#" @click.prevent="remove(i)" class="alert-close"
        ><i class="fas fa-times"></i
      ></a>
    </span>
  </div>
</template>

<script>
import store from '../../store'
export default {
  store,
  computed: {
    errors () {
      return this.$store.state.errors.errors
    }
  },

  methods: {
    remove (index) {
      this.$store.commit('errors/delete', index)
    }
  }
}
</script>
