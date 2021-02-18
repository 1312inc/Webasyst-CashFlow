<template>
  <div id="wa-app">
    <div class="flexbox">
      <Sidebar />
      <div class="content blank">
        <div sticky-container class="box">
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
