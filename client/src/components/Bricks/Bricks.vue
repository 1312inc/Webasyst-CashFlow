<template>
  <div class="bricks">
    <BrickUpnext />
    <BrickHistory />
  </div>
</template>

<script>
import BrickUpnext from './BrickUpnext'
import BrickHistory from './BrickHistory'
export default {
  components: {
    BrickUpnext,
    BrickHistory
  }
}
</script>
