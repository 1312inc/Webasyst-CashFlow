import { i18n } from '@/plugins/locale'
import InputCurrency from './InputCurrency.vue'

export default {
  title: 'Cash/Inputs/Currency',
  component: InputCurrency,
  args: {
    signed: true
  },
  argTypes: {
    input: { action: 'input' }
  }
}

const Template = (args, { argTypes }) => {
  return ({
    props: Object.keys(argTypes),
    i18n,
    components: { InputCurrency },
    template: `
    <InputCurrency v-bind="$props" v-on="$props" class="number" />
    `
  })
}

export const CurrencyAddAccount = Template.bind({})
CurrencyAddAccount.args = {
  placeholder: '0',
  value: '2'
}

export const CurrencyAddTrans = Template.bind({})
CurrencyAddTrans.args = {
  placeholder: '0',
  signed: false,
  value: '3'
}
