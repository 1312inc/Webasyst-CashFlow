import Vue from 'vue'
import Helpers from '@/plugins/helpers'
import numeralMoment from '@/plugins/numeralMoment'
import VuePortal from '@linusborg/vue-simple-portal'
import { MINIMAL_VIEWPORTS } from '@storybook/addon-viewport';

Vue.use(Helpers)
Vue.use(numeralMoment)
Vue.use(VuePortal)

export const parameters = {
  actions: { argTypesRegex: "^on[A-Z].*" },
  viewport: {
    viewports: MINIMAL_VIEWPORTS
  },
}