<template>
  <div id="wa-app">
    <div class="flexbox wrap-mobile">
      <Sidebar />
      <div class="content hide-scrollbar blank">
        <div sticky-container class="box custom-px-8 custom-p-0-mobile">
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
