<template>
  <router-link
    to="/calendar"
    :class="{ selected: $route.name === 'Calendar' }"
    class="brick"
  >
    <span class="icon"><i class="far fa-calendar-alt text-dark-gray" /></span>
    {{ $t("calendar") }}
  </router-link>
</template>
