<script setup>
</script>

<template>
  <ul class="menu">
    <li v-if="$appState.isPremium && $permissions.isAdmin">
      <router-link to="/companies">
        <i class="fas fa-university" />
        <span>{{ $t("companies") }}</span>
      </router-link>
    </li>
    <li v-if="$appState.isPremium && $permissions.isAdmin">
      <router-link to="/scenarios">
        <i class="fas fa-code-branch" />
        <span>{{ $t("scenarios") }}</span>
      </router-link>
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
    <li v-if="$permissions.isAdmin && !$appState.isPremium">
      <a :href="`${$helper.baseUrl}upgrade/`">
        <i class="fas fa-star text-yellow" />
        <span>{{ $t("premium") }}</span>
      </a>
    </li>
  </ul>
</template>
