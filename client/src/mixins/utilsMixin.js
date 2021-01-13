export default {
  methods: {
    handleApiError (e) {
      const message = e.response.data?.error === 'error' ? e.response.data.error_message : e.message
      this.$notify.error(message)
    }
  }
}
