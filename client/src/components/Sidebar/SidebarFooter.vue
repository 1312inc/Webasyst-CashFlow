<script setup>
</script>

<template>
  <ul class="menu">
    <li v-if="$permissions.isAdmin">
      <router-link to="/companies">
        <i class="fas fa-university" />
        <span :class="{ 'opacity-50': !$appState.isPremium }">{{ $t("companies") }}</span>
        <span
          v-if="!$appState.isPremium"
          class="count small"
        >
          <i class="fas fa-star text-yellow" />
        </span>
      </router-link>
    </li>
    <li v-if="$permissions.isAdmin">
      <router-link to="/scenarios">
        <i class="fas fa-code-branch" />
        <span :class="{ 'opacity-50': !$appState.isPremium }">{{ $t("scenarios") }}</span>
        <span
          v-if="!$appState.isPremium"
          class="count small"
        >
          <i class="fas fa-star text-yellow" />
        </span>
      </router-link>
    </li>
    <li v-if="$permissions.isAdmin">
      <router-link to="/templates">
        <i class="fas fa-copy" />
        <span :class="{ 'opacity-50': !$appState.isPremium }">{{ $t("operationTemplates") }}</span>
        <span
            v-if="!$appState.isPremium"
            class="count small"
        >
          <i class="fas fa-star text-yellow" />
        </span>
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
        <span class="count"><span class="badge purple small bold">%</span></span>
      </a>
    </li>
  </ul>
</template>
