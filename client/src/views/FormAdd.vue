<script setup>
import AddAccount from '@/components/Modals/AddAccount'
import AddCategory from '@/components/Modals/AddCategory'
import Modal from '../components/Modal.vue'

const props = defineProps({
  entity: {
    type: String,
    required: true
  },
  type: {
    type: String,
    default: 'income'
  }
})

function close () {
  window.emitter.emit('closeDialog')
}
</script>

<template>
  <Modal
    :disable-transition="true"
    @close="close"
  >
    <component
      :is="props.entity === 'category' ? AddCategory : AddAccount"
      :type="props.entity === 'category' && props.type"
    />
  </Modal>
</template>
