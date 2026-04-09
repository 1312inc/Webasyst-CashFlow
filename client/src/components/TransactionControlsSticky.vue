<template>
  <Fragment>
    <div
      ref="stickyEl"
      class="c-transaction-controls-sticky"
      :class="{'desktop-and-tablet-only': $helper.isDesktopEnv}"
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
    { threshold: 1, rootMargin: '60px 0px 0px 0px' }
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
  display: none;
}

.c-transaction-controls-sticky.is-sticky {
  display: block;
}

.c-mobile-build .c-transaction-controls-sticky {
  top: 0;
}

</style>
