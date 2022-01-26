<template>
  <ul class="menu">
    <li v-if="$permissions.isAdmin">
      <router-link to="/trash">
        <i class="fas fa-trash-alt"></i>
        <span>{{ $t("trash") }}</span>
      </router-link>
    </li>
    <li v-if="$permissions.canSeeReport">
      <a :href="`${$helper.baseUrl}report/dds/`">
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
  </ul>
</template>
