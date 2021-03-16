import ButtonRound from './ButtonRound'

export default {
  title: 'Cash/Buttons',
  component: ButtonRound
}

const Template = (args, { argTypes }) => ({
  components: { ButtonRound },
  template: `
    <ButtonRound :icon="icon" />
    `
})

export const Move = Template.bind({})
Move.args = { icon: 'fa-arrow-right' }

export const Delete = Template.bind({})
Delete.args = { icon: 'fa-trash-alt' }
