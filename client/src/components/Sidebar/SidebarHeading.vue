<template>
  <h6 class="heading">
    <span><slot></slot></span>
    <a @click="openModal = true" class="count">
      <i class="fas fa-plus-circle"></i>
    </a>
    <portal>
      <Modal v-if="openModal" @close="openModal = false">
        <component :is="updatingEntityName"></component>
      </Modal>
    </portal>
  </h6>
</template>

<script>
import Modal from '@/components/Modal'
import Account from '@/components/AddAccount'
import Category from '@/components/AddCategory'
export default {
  props: ['updatingEntityName'],

  components: {
    Modal,
    Account,
    Category
  },

  data () {
    return {
      openModal: false
    }
  }
}
</script>
