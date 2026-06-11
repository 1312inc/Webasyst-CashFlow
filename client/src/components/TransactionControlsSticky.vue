<template>
  <Fragment>
    <div
      ref="stickyEl"
      class="c-transaction-controls-sticky"
    >
      <BlankBox>
        <div class="custom-px-16">
          <TransactionControls />
        </div>
      </BlankBox>
    </div>
    <div ref="dummyEl" />
  </Fragment>
</template>

<script setup>
import TransactionControls from './TransactionControls.vue'
import BlankBox from './BlankBox.vue'
import { onBeforeUnmount, onMounted, ref } from 'vue'

const stickyEl = ref()
const dummyEl = ref()
let observer

onMounted(() => {
  if (!stickyEl.value) return
  observer = new IntersectionObserver(
    ([entry]) => {
      stickyEl.value.classList.toggle('is-sticky', !entry.isIntersecting)
    },
    { threshold: 1, rootMargin: '70px 0px 0px 0px' }
  )

  if (dummyEl.value) { observer.observe(dummyEl.value) }
})

onBeforeUnmount(() => {
  if (dummyEl.value) { observer.unobserve(dummyEl.value) }
})

</script>

<style>
.c-transaction-controls-sticky {
  position: sticky;
  top: 4rem;
  z-index: 999;
  opacity: 0;
  pointer-events: none;
}
.c-transaction-controls-sticky .box.rounded {
  border-radius: 0;
}

.c-transaction-controls-sticky.is-sticky {
  opacity: 1;
  pointer-events: auto;
}

.c-mobile-build .c-transaction-controls-sticky {
  top: 0;
}

</style>
