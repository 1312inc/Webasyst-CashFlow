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
export default {
  data () {
    return {
      errors: []
    }
  },

  methods: {
    error (message) {
      this.errors.push(message)
    },

    remove (index) {
      this.errors.splice(index, 1)
    }
  }
}
</script>

<style>
.alert-fixed-box {
    z-index: 2001;
}
</style>
