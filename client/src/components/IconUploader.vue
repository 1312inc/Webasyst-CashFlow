<template>
  <div class="UppyDragDrop"></div>
</template>

<script>
import Uppy from '@uppy/core'
import DragDrop from '@uppy/drag-drop'
import XHRUpload from '@uppy/xhr-upload'
import { baseApiUrl, accessToken } from '../plugins/api'

export default {
  mounted () {
    const uppy = new Uppy({
      debug: process.env.NODE_ENV === 'development',
      autoProceed: true,
      restrictions: {
        maxNumberOfFiles: 1
      },
      meta: {
        access_token: accessToken
      }
    })
    uppy.use(DragDrop, {
      target: '.UppyDragDrop'
    })
    uppy.use(XHRUpload, {
      endpoint: `${baseApiUrl}/cash.account.uploadLogo`,
      fieldName: 'logo'
    })
    uppy.on('upload-success', (file, response) => {
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
