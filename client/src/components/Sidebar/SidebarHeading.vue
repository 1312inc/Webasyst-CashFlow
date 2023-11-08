<template>
  <h6 class="heading">
    <span><slot /></span>
    <a
      v-if="$permissions.isAdmin"
      class="count"
      @click="openModal = true"
    >
      <i class="fas fa-plus-circle" />
    </a>
    <portal>
      <Modal
        v-if="openModal"
        @close="openModal = false"
      >
        <component
          :is="updatingEntityName"
          :type="type"
        />
      </Modal>
    </portal>
  </h6>
</template>

<script>
import Modal from '@/components/Modal'
import Account from '@/components/Modals/AddAccount'
import Category from '@/components/Modals/AddCategory'
export default {

  components: {
    Modal,
    Account,
    Category
  },
  props: ['updatingEntityName', 'type'],

  data () {
    return {
      openModal: false
    }
  }
}
</script>
