<template>
  <router-link
    :to="{ name: 'Upnext' }"
    :class="{ selected: $route.name === 'Upnext' }"
    class="brick custom-mb-0"
  >
    <span class="icon"><i class="fas fa-star"></i></span>
    {{ $t("upnext") }}
  </router-link>
</template>
