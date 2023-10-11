<template>
  <div class="bricks custom-mt-0">
    <BrickTransactions />
    <BrickCalendar />
  </div>
</template>

<script>
import BrickCalendar from './BrickCalendar'
import BrickTransactions from './BrickTransactions'
export default {
  components: {
    BrickCalendar,
    BrickTransactions
  }
}
</script>
