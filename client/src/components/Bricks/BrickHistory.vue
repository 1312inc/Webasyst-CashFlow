<template>
  <router-link
    to="/history"
    class="brick custom-mb-0"
    :class="{ selected: $route.name === 'History' }"
  >
    <span class="icon"><i class="fas fa-history"></i></span>
    {{ $t("history") }}
  </router-link>
</template>

<script>
export default {}
</script>
