<template>
  <router-link
    to="/calendar"
    :class="{ selected: $route.name === 'Calendar' }"
    class="brick"
  >
    <div class="flexbox middle space-8">
      <span class="icon"><i class="far fa-calendar-alt" /></span>
      {{ $t("calendar") }}
    </div>
  </router-link>
</template>
