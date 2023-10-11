<template>
  <router-link
    to="/calendar"
    :class="{ selected: $route.name === 'Calendar' }"
    class="brick custom-mb-0"
  >
    <span class="icon"><i class="far fa-calendar"></i></span>
    {{ $t("calendar") }}
  </router-link>
</template>
