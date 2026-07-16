<script setup>
</script>

<template>
  <ul class="menu">
    <li v-if="$permissions.isAdmin">
      <router-link to="/companies">
        <i class="fas fa-building" />
        <span>{{ $t("companies") }}</span>
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
    <li v-if="$permissions.isAdmin">
      <a :href="`${$helper.baseUrl}upgrade/`">
        <i
          class="fas fa-star"
          :class="{'text-yellow': !$appState.isPremium}"
        />
        <span>{{ $t("premium") }}</span>
      </a>
    </li>
  </ul>
</template>
