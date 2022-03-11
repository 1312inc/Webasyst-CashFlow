<template>
  <div class="custom-mt-32">
    <div v-if="$permissions.isAdmin" class="custom-px-16">
      <div class="toggle width-100" style="box-sizing: border-box;">
        <span
          v-for="(tab, i) in tabs"
          :key="i"
          @click="activeTab = i"
          :class="{ selected: activeTab === i }"
          class="width-50"
          style="box-sizing: border-box;text-align: center;margin-right: 0;"
          >{{ $t(tab) }}</span
        >
      </div>
    </div>
    <keep-alive include="ContactsList">
      <slot :name="tabs[activeTab]"></slot>
    </keep-alive>
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
