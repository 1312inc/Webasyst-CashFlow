<script setup>
import { useRoute } from 'vue-router/composables'
import AddAccount from '@/components/Modals/AddAccount'
import AddCategory from '@/components/Modals/AddCategory'

const route = useRoute()
</script>

<template>
  <div
    class="dialog"
    style="display: block; position: absolute;"
  >
    <component :is="route.params.type === 'account' ? AddAccount : AddCategory" />
  </div>
</template>
