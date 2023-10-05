<template>
  <router-link
    to="/upnext"
    :class="{ selected: $route.name === 'Upnext' }"
    class="brick custom-mb-0"
  >
    <span class="icon"><i class="far fa-clock"></i></span>
    {{ $t("upnext") }}
  </router-link>
</template>
