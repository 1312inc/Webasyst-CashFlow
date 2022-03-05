<template>
  <div class="custom-mt-32">
    <div v-if="$permissions.isAdmin" class="custom-px-16">
      <div class="toggle width-100">
        <span
          v-for="(tab, i) in tabs"
          :key="i"
          @click="activeTab = i"
          :class="{ selected: activeTab === i }"
          >{{ $t(tab) }}</span
        >
      </div>
    </div>

    <slot :name="tabs[activeTab]"></slot>
  </div>
</template>

<script>
export default {
  data () {
    return {
      tabs: ['categories', 'contacts'],
      activeTab: +localStorage.getItem('cashTogglerActiveTab') || 0
    }
  },

  watch: {
    activeTab (val) {
      localStorage.setItem('cashTogglerActiveTab', val)
    }
  }
}
</script>
