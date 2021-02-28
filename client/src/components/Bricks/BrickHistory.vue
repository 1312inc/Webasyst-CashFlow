<template>
  <router-link
    to="/history"
    class="brick"
    :class="{ selected: $route.name === 'History' }"
  >
    <span class="icon"><i class="fas fa-history"></i></span>
    {{ $t("history") }}
  </router-link>
</template>

<script>
export default {}
</script>
