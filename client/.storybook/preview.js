import { i18n } from '@/plugins/locale'

export const parameters = {
  actions: { argTypesRegex: "^on[A-Z].*" },
}

export const decorators = [(story) => ({
  components: { story },
  template: '<story />',
  i18n
})];