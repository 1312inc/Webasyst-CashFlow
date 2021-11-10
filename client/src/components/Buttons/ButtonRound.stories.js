import ButtonRound from './ButtonRound'

export default {
  title: 'Cash/Buttons',
  component: ButtonRound
}

const Template = (args, { argTypes }) => ({
  components: { ButtonRound },
  props: Object.keys(argTypes),
  template: `
    <ButtonRound :icon="icon" />
    `
})

export const Move = Template.bind({})
Move.args = { icon: 'fa-coins' }

export const Delete = Template.bind({})
Delete.args = { icon: 'fa-trash-alt' }
