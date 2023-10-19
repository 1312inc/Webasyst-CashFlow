<template>
  <ul class="menu">
    <li v-if="$permissions.canSeeReport">
      <a :href="`${$helper.baseUrl}report/dds/`">
        <i class="fas fa-chart-pie" />
        <span>{{ $t("reports") }}</span>
      </a>
    </li>
    <li v-if="$permissions.canImport">
      <a :href="`${$helper.baseUrl}import/`">
        <i class="fas fa-file-import" />
        <span>{{ $t("import") }}</span>
      </a>
    </li>
    <li v-if="$permissions.isAdmin">
      <router-link to="/trash">
        <i class="fas fa-trash-alt" />
        <span>{{ $t("trash") }}</span>
      </router-link>
    </li>
    <li v-if="$permissions.isAdmin">
      <a :href="`${$helper.baseUrl}plugins/`">
        <i class="fas fa-plug" />
        <span>{{ $t("plugins") }}</span>
      </a>
    </li>
  </ul>
</template>
