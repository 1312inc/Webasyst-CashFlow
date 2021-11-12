import { i18n } from '@/plugins/locale'
import DropdownWa from './DropdownWa.vue'

export default {
  title: 'Cash/Inputs/DropdownWa',
  component: DropdownWa,
  args: {
    isRight: false,
    defaultValue: '',
    valuePropName: 'value',
    maxHeight: 300,
    rowModificator: obj => {
      return obj.name
    },
    items: [
      {
        name: 'item 1',
        value: 1
      },
      {
        name: 'item 2',
        value: 2
      },
      {
        name: 'item 3',
        value: 3
      }
    ]
  },
  argTypes: {
    input: { action: 'input' }
  }
}

const Template = (args, { argTypes }) => {
  return ({
    props: Object.keys(argTypes),
    i18n,
    components: { DropdownWa },
    template: `
    <DropdownWa v-bind="$props" v-on="$props" />
    `
  })
}

export const DropdownCats = Template.bind({})
DropdownCats.args = {
  value: 1,
  items: window.appState.categories,
  valuePropName: 'id',
  label: 'Статья',
  rowModificator: obj => {
    return `<span class="icon"><i
    class="rounded"
    style="background-color:#000;"
  ></i
></span><span>${obj.name}</span>`
  }
}

export const DropdownAccounts = Template.bind({})
DropdownAccounts.args = {
  value: 1,
  items: window.appState.accounts,
  valuePropName: 'id',
  label: 'На счет',
  rowModificator: obj => {
    return `<span class="icon"><i
    class="rounded"
    style="background-color:#000;"
  ></i
></span><span>${obj.name}</span>`
  }
}
