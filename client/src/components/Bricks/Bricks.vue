<template>
  <div class="bricks custom-mt-0">
    <BrickTransactions />
    <BrickUpnext />
  </div>
</template>

<script>
import BrickUpnext from './BrickUpnext'
import BrickTransactions from './BrickTransactions'
export default {
  components: {
    BrickUpnext,
    BrickTransactions
  }
}
</script>
