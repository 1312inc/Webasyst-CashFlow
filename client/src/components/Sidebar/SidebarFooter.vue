<template>
  <div class="sidebar-footer shadowed">
    <ul class="menu">
      <li>
        <a :href="`${$helper.baseUrl}reports/`">
          <i class="fas fa-chart-pie"></i>
          <span>{{ $t("reports") }}</span>
        </a>
      </li>
      <li>
        <a :href="`${$helper.baseUrl}import/`">
          <i class="fas fa-file-import"></i>
          <span>{{ $t("import") }}</span>
        </a>
      </li>
      <li>
        <a :href="`${$helper.baseUrl}shop/settings/`">
          <i class="fas fa-sliders-h"></i>
          <span>Shop-Script</span>
        </a>
      </li>
    </ul>
  </div>
</template>
