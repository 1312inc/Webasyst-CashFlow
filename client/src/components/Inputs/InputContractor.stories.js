import { i18n } from '@/plugins/locale'
import InputContractor from './InputContractor.vue'

export default {
  title: 'Cash/Inputs/Contractor',
  component: InputContractor,
  args: {
    defaultContractor: {
      name: 'тест тестович',
      userpic: 'http://localhost:8888/wa-content/img/userpic96.jpg'
    },
    createNewContractor: true
  },
  argTypes: {
    newContractor: { action: 'Update name of New Contractor' },
    changeContractor: { action: 'Change contracor ID' }
  }
}

const Template = (args, { argTypes }) => {
  return ({
    props: Object.keys(argTypes),
    i18n,
    components: { InputContractor },
    template: '<InputContractor v-bind="$props" v-on="$props" />'
  })
}

export const Contractor = Template.bind({})
Contractor.args = {}

export const ContractorNoCreate = Template.bind({})
ContractorNoCreate.args = {
  createNewContractor: false
}
