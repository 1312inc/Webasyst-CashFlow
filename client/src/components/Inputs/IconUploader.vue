<template>
  <div class="UppyDragDrop"></div>
</template>

<script>
import Uppy from '@uppy/core'
import DragDrop from '@uppy/drag-drop'
import XHRUpload from '@uppy/xhr-upload'

export default {
  mounted () {
    this.uppy = new Uppy({
      debug: process.env.NODE_ENV === 'development',
      autoProceed: true,
      restrictions: {
        maxNumberOfFiles: 1
      },
      meta: {
        access_token:
          process.env.VUE_APP_API_TOKEN || window?.appState?.token || ''
      }
    })
      .use(DragDrop, {
        target: '.UppyDragDrop'
      })
      .use(XHRUpload, {
        endpoint: `${window?.appState?.baseApiUrl ||
          '/api.php'}/cash.account.uploadLogo`,
        limit: 1,
        fieldName: 'logo'
      })
      .on('upload-success', (file, response) => {
        this.$emit('uploaded', response.body)
      })
  }
}
</script>

<style lang="scss">
@import "~@uppy/core/dist/style.css";
@import "~@uppy/drag-drop/dist/style.css";

.uppy-DragDrop-container {
  position: absolute;
}
.uppy-DragDrop-inner {
  padding: 8px;
}
.uppy-DragDrop-arrow {
  display: none;
}
.uppy-DragDrop-label {
  font-size: 0.7rem;
  color: initial;
  margin-bottom: 0;
}
</style>
