<template>
    <div class="box uploadbox custom-p-0">
      <div class="UppyDragDrop"></div>
    </div>
</template>

<script>
import Uppy from '@uppy/core'
import DragDrop from '@uppy/drag-drop'
import XHRUpload from '@uppy/xhr-upload'

export default {
  mounted () {
    this.uppy = new Uppy({
      autoProceed: true,
      restrictions: {
        maxNumberOfFiles: 1
      },
      meta: {
        access_token: window?.appState?.token
      }
    })
      .use(DragDrop, {
        target: '.UppyDragDrop',
        locale: {
          strings: {
            dropHereOr: this.$t('fileUploaderLabel')
          }
        }
      })
      .use(XHRUpload, {
        endpoint: `${window?.appState?.baseApiUrl ||
          '/api.php'}/cash.account.uploadLogo`,
        limit: 1,
        fieldName: 'logo'
      })
      .on('upload-success', (file, response) => {
        if (response.body.error) {
          this.$store.commit('errors/error', {
            title: 'error.api',
            method: 'cash.account.uploadLogo',
            message: response.body.error_description
          })
        } else {
          this.$emit('uploaded', response.body)
        }
      })
  }
}
</script>

<style lang="scss">
@import "@uppy/core/dist/style.css";
.UppyDragDrop {
  position: relative;
  height: 40px;
}
.uppy-DragDrop-container {
  top: 0;
  left: 0;
  position: absolute;
  text-align: center;
  cursor: pointer;
  height: 40px;

  &:hover {
    color: inherit;
    box-shadow: none;
    background: var(--background-color-table-row-hover);
  }

}
.uppy-DragDrop-arrow {
  display: none;
}
.uppy-DragDrop-label {
  font-size: 0.7rem;
  margin-bottom: 0;
}
</style>
