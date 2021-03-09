<template>
  <div id="wa-app">
    <div class="flexbox wrap-mobile">
      <Sidebar />
      <div class="content blank">
        <div sticky-container class="box custom-p-0-mobile">
          <router-view />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Sidebar from '@/components/Sidebar/Sidebar'
export default {
  components: {
    Sidebar
  }
}
</script>
