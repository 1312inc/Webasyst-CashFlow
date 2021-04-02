<template>
  <ul class="menu">
    <li v-if="$permissions.canSeeReport">
      <a :href="`${$helper.baseUrl}reports/`">
        <i class="fas fa-chart-pie"></i>
        <span>{{ $t("reports") }}</span>
      </a>
    </li>
    <li v-if="$permissions.canImport">
      <a :href="`${$helper.baseUrl}import/`">
        <i class="fas fa-file-import"></i>
        <span>{{ $t("import") }}</span>
      </a>
    </li>
    <li v-if="$permissions.isAdmin">
      <a :href="`${$helper.baseUrl}shop/settings/`">
        <i class="fas fa-shopping-cart"></i>
        <span>Shop-Script</span>
      </a>
    </li>
  </ul>
</template>
