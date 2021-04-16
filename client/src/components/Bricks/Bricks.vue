<template>
  <div class="bricks custom-mt-0">
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
