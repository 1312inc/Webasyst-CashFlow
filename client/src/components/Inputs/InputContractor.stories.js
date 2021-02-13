import InputContractor from './InputContractor.vue'

export default {
  title: 'Cash/Inputs/Contractor',
  component: InputContractor,
  args: {
    defaultContractor: {
      name: 'тест тестович',
      userpic: 'http://localhost:8888/wa-content/img/userpic96.jpg'
    }
  },
  argTypes: {
    newContractor: { action: 'Update name of New Contractor' },
    changeContractor: { action: 'Change contracor ID' }
  }
}

export const Contractor = (args, { argTypes }) => {
  return ({
    props: Object.keys(argTypes),
    components: { InputContractor },
    template: `
    <InputContractor
      :defaultContractor="defaultContractor"
      @newContractor="newContractor"
      @changeContractor="changeContractor"
    />
    `
  })
}
