<template>
  <h6 class="heading">
    <span><slot></slot></span>
    <a @click="openModal = true" class="count">
      <i class="fas fa-plus-circle"></i>
    </a>
    <portal>
      <Modal v-if="openModal" @close="openModal = false">
        <component :is="updatingEntityName" :type="type"></component>
      </Modal>
    </portal>
  </h6>
</template>

<script>
import Modal from '@/components/Modal'
import Account from '@/components/Modals/AddAccount'
import Category from '@/components/Modals/AddCategory'
export default {
  props: ['updatingEntityName', 'type'],

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
